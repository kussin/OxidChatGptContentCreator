<?php

namespace Kussin\ChatGpt\Cron;

use Kussin\ChatGpt\Traits\ChatGPTClientTrait;
use Kussin\ChatGpt\Traits\ChatGPTProcessPromptsTrait;
use Kussin\ChatGpt\Traits\CustomDbTrait;
use Kussin\ChatGpt\Traits\LoggerTrait;
use Kussin\ChatGpt\Traits\OxidObjectsTrait;
use Kussin\ChatGpt\Traits\ProcessFlagTrait;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;

class Process extends FrontendController
{
    use ChatGPTClientTrait;
    use ChatGPTProcessPromptsTrait;
    use CustomDbTrait;
    use LoggerTrait;
    use OxidObjectsTrait;
    use ProcessFlagTrait;

    protected const PROCESS_NEW_STATUS = 'pending';
    protected const PROCESS_PROCESSING_STATUS = 'processing';
    protected const PROCESS_GENERATED_STATUS = 'generated';
    protected const PROCESS_COMPLETE_STATUS = 'complete';
    protected const PROCESS_CANCELED_STATUS = 'cancaled';
    protected const PROCESS_ERROR_STATUS = 'error';

    private $_sProcessSelectionQueryVarname = 'sKussinChatGptProcessSelectionQuery';
    
    /**
     * @return string
     */
    public function render() {
        $bProcessQueue = (bool) Registry::getConfig()->getConfigParam('blKussinChatGptProcessQueueEnabled');

        if ($bProcessQueue) {
            $this->_cron();

        } else {
            // CLEANUP
            $this->_removeFlag();

            $this->_info('ChatGPT Process Queue disabled.');

            echo 'ChatGPT Process Queue disabled.';
        }

        exit;
    }
    
    private function _cron() {
        if (!$this->_hasFlag()) {
            $this->_setFlag();

            // PROCESS STEPS
            $this->_fillQueue();
            $this->_adjustApiSettings();
            $this->_preparePrompt();
            $this->_generateContent();
            $this->_replaceContent();
            $this->_dXBkYXRlTGljZW5zZQ();

            $this->_removeFlag();

        } else {
            $this->_info('Process already running.');
        }
    }

    protected function _fillQueue() {
        if ($this->_isNewProcessDefined()) {
            $sSelectQuery = trim(Registry::getConfig()->getConfigParam('sKussinChatGptProcessSelectionQuery'));

            if (strlen($sSelectQuery) > '') {
                $this->_info('Queue filled with new processes.');

                $sQuery = implode(' ', array(
                    'INSERT IGNORE INTO `kussin_chatgpt_content_creator_queue` (`object`, `object_id`, `field`, `shop_id`, `lang_id`, `status`)',
                    $sSelectQuery,
                ));

                DatabaseProvider::getDb()->execute($sQuery);

                // FIX QUEUE TIMESTAMP
                $this->_fixConfigTimestamp('sKussinChatGptProcessSelectionQuery');
            }
        }
    }

    protected function _adjustApiSettings() {
        $sModel = trim(Registry::getConfig()->getConfigParam('sKussinChatGptProcessModel'));
        $iMaxTokens = (int) Registry::getConfig()->getConfigParam('iKussinChatGptProcessMaxTokens');
        $dTemperature = (double) Registry::getConfig()->getConfigParam('dKussinChatGptProcessTemperature');

        if (
            ($sModel !== '')
            && ($iMaxTokens > 100)
            && ($dTemperature >= 0.25)
        ) {
            $sQuery = 'UPDATE IGNORE `kussin_chatgpt_content_creator_queue` SET `model` = "' . $sModel . '", `max_tokens` = "' . $iMaxTokens . '", `temperature` = "' . $dTemperature . '", `process_ip` = "' . $this->_getClientIp() . '" WHERE (`status` = "' . self::PROCESS_NEW_STATUS . '");';

            DatabaseProvider::getDb()->execute($sQuery);

            $this->_debug('Updated ChatGPT api settings.');
        } else {
            // ERROR
            $this->_warning('API settings incomplete or wrong.');
        }
    }

    protected function _preparePrompt() {
        $iLimit = (int) Registry::getConfig()->getConfigParam('iKussinChatGptProcessLimitMaxPrompts');

        $sQuery = 'SELECT `id`, `object`, `object_id`, `field`, `shop_id`, `lang_id`, `mode`, `max_tokens` FROM kussin_chatgpt_content_creator_queue WHERE (`status` = "' . self::PROCESS_NEW_STATUS . '") ORDER BY `updated_at` ASC LIMIT ' . $iLimit . ';';

        foreach ($this->_getCustomDbResult($sQuery) as $aItem) {
            $sMode = trim($aItem[6]) != '' ? trim($aItem[6]) : 'create';
            $oObject = $this->_getOxidObject($aItem[1]);
            $sOxid = $aItem[2];
            $sFieldId = $this->_getOxidFieldId($aItem[1], $aItem[3], $aItem[5]);
            $iLang = (int) $aItem[5];

            // LOAD OBJECT
            $oObject->loadInLang($iLang, $sOxid);

            // GET PROMPT
            $sPrompt = $this->_getProcessPrompts($sMode, $oObject, $sFieldId, $iLang, $aItem[7]);

            $this->_debug([
                'method' => __CLASS__ . '::' . __METHOD__,
                'id' => $aItem[0],
                'object' => $aItem[1],
                'class' => get_class($oObject),
                'object_id' => $aItem[2],
                'prompt' => $sPrompt,
            ]);

            // SAVE PROMPT
            $sUpdateQuery = 'UPDATE kussin_chatgpt_content_creator_queue SET `prompt` = "' . $sPrompt . '", `process_ip` = "' . $this->_getClientIp() . '", `status` = "' . self::PROCESS_PROCESSING_STATUS . '" WHERE (`id` = "' . $aItem[0] . '");';
            DatabaseProvider::getDb()->execute($sUpdateQuery);

            // CLEAR
            $oObject = null;
            $sFieldId = null;
        }
    }

    protected function _generateContent() {
        $iLimit = (int) Registry::getConfig()->getConfigParam('iKussinChatGptProcessLimitMaxGenerations');

        $sQuery = 'SELECT `id`, `object`, `object_id`, `field`, `shop_id`, `lang_id`, `prompt`, `model`, `max_tokens`, `temperature` FROM kussin_chatgpt_content_creator_queue WHERE (`status` = "' . self::PROCESS_PROCESSING_STATUS . '") ORDER BY `updated_at` ASC LIMIT ' . $iLimit . ';';

        foreach ($this->_getCustomDbResult($sQuery) as $aItem) {
            $oObject = $this->_getOxidObject($aItem[1]);
            $sOxid = $aItem[2];
            $sFieldId = $this->_getOxidFieldId($aItem[1], $aItem[3], $aItem[5]);
            $iLang = (int) $aItem[5];
            $sPrompt = $aItem[6];
            $sModel = $aItem[7];
            $iMaxTokens = $this->_getProcessMaxTokens($aItem[3], (int) $aItem[8]);
            $dTemperature = (double) $aItem[9];

            // LOAD OBJECT
            $oObject->loadInLang($iLang, $sOxid);

            // ChatGPT API REQUEST
            $aResponse = $this->_kussinGetChatGptContent($sPrompt, $sModel, $dTemperature, $iMaxTokens, $this->_useHtml($aItem[3]));

            if ($aResponse['error'] == NULL) {

                try {
                    // GENERATE CONTENT
                    $sGenerated = $aResponse['data'];

                    // GET CURRENT CONTENT
                    $sContent = ($aItem[3] == 'oxlongdesc') ? $oObject->getLongDescription()->getRawValue() : $oObject->{$sFieldId}->value;

                    if ( ($sGenerated != '') && ($sGenerated != null) ) {
                        // SAVE PROMPT
                        $sUpdateQuery = 'UPDATE kussin_chatgpt_content_creator_queue SET `content` = ' . ( ($sContent == null) ? 'NULL' : '"' . $this->_encodeProcessContent($sContent) . '"' ) . ', `generated` = "' . $this->_encodeProcessContent($sGenerated) . '", `process_ip` = "' . $this->_getClientIp() . '", `status` = "' . self::PROCESS_GENERATED_STATUS . '" WHERE (`id` = "' . $aItem[0] . '");';
                        DatabaseProvider::getDb()->execute($sUpdateQuery);

                        $this->_debug('Generated ChatGPT ai content for: ' . $sOxid);
                    } else {
                        // ERROR
                        $this->_warning('Could not generate ChatGPT ai content for: ' . $sOxid);
                    }

                } catch (\Exception $oException) {
                    // ERROR
                    $this->_error(array(
                        'method' => __CLASS__ . '::' . __FUNCTION__,
                        'response' => $oException,
                    ));

                    // SAVE PROMPT
                    $sUpdateQuery = 'UPDATE kussin_chatgpt_content_creator_queue SET `process_ip` = "' . $this->_getClientIp() . '", `status` = "' . self::PROCESS_ERROR_STATUS . '" WHERE (`id` = "' . $aItem[0] . '");';
                    DatabaseProvider::getDb()->execute($sUpdateQuery);
                }
            }

            // CLEAR
            $oObject = null;
            $sFieldId = null;
        }
    }

    protected function _replaceContent() {
        $iLimit = (int) Registry::getConfig()->getConfigParam('iKussinChatGptProcessLimitMaxReplacements');

        $sQuery = 'SELECT `id`, `object`, `object_id`, `field`, `shop_id`, `lang_id`, `generated` FROM kussin_chatgpt_content_creator_queue WHERE ( (`generated` IS NOT NULL) AND (`generated` NOT LIKE "") ) AND (`status` = "' . self::PROCESS_GENERATED_STATUS . '") ORDER BY `updated_at` ASC LIMIT ' . $iLimit . ';';

        foreach ($this->_getCustomDbResult($sQuery) as $aItem) {
            $oObject = $this->_getOxidObject($aItem[1]);
            $sOxid = $aItem[2];
            $sFieldId = $this->_getOxidFieldId($aItem[1], $aItem[3], $aItem[5]);
            $iLang = (int) $aItem[5];
            $sGeneratedContent = $this->_decodeProcessContent($aItem[6]);

            // LOAD OBJECT
            $oObject->load($sOxid);

            // SAVE CONTENT
            $oContent = new Field($sGeneratedContent);

            if ($aItem[3] == 'oxlongdesc') {
                $oObject->setArticleLongDesc($oContent->getRawValue());
            } else {
                $oObject->{$sFieldId} = new Field($oContent);
            }

            // TOUCH TIMESTAMP
            $this->_touchTimestamp($sOxid, ( ($aItem[1] == 'oxartextends') ? 'oxarticles' : $aItem[1] ));

            $oObject->save();

            // OBJECT LINK
            $sObjectLink = $oObject->getLink();

            // UPDATE STATUS
            $sUpdateQuery = 'UPDATE kussin_chatgpt_content_creator_queue SET `link` = "' . $sObjectLink . '", `process_ip` = "' . $this->_getClientIp() . '", `status` = "' . self::PROCESS_COMPLETE_STATUS . '" WHERE (`id` = "' . $aItem[0] . '");';
            DatabaseProvider::getDb()->execute($sUpdateQuery);

            $this->_debug('Saved ai content for: ' . $sOxid . ' (Link: ' . $sObjectLink . ')');

            // CLEAR
            $oObject = null;
            $sFieldId = null;
        }
    }

    protected function _isNewProcessDefined(): bool
    {
        $sQuery = 'SELECT OXTIMESTAMP FROM oxconfig WHERE (OXVARNAME LIKE "' . $this->_sProcessSelectionQueryVarname . '");';
        $iLastProcessTimestamp = strtotime($this->_getCustomDbValue($sQuery));

        return ((time() - (15*60)) < $iLastProcessTimestamp);
    }

    private function _fixConfigTimestamp($sOxVarname = 'sKussinChatGptProcessSelectionQuery', $sTimeChange = '-3 hours'): bool
    {
        $sTimestamp = date('Y-m-d H:i:s', strtotime($sTimeChange));

        $sQuery = 'UPDATE `oxconfig` SET `OXTIMESTAMP` = "' . $sTimestamp . '" WHERE (`OXVARNAME` LIKE "' . $sOxVarname . '");';

        return (bool) DatabaseProvider::getDb()->execute($sQuery);
    }

    private function _touchTimestamp($sOxid, $sTable = 'oxarticles'): bool
    {
        $sQuery = 'UPDATE IGNORE `' . $sTable . '` SET `KUSSINCHATGPTGENERATED` = 1, `OXTIMESTAMP` = NOW() WHERE (`OXID` LIKE "' . $sOxid . '");';

        return (bool) DatabaseProvider::getDb()->execute($sQuery);
    }

    private function _dXBkYXRlTGljZW5zZQ($sLicenseFile = 'modules/kussin/chatgpt-content-creator/license.txt'): bool
    {
        // LICENSE FILE
        $sFilename = str_replace('//', '/', Registry::getConfig()->getConfigParam('sShopDir') . '/' . $sLicenseFile);

        // GET SHOP
        $oShop = Registry::getConfig()->getActiveShop();

        // GET AI GENERATIONS
        $aGenerations = $this->_getCustomDbResult('SELECT DATE_FORMAT(`updated_at`, "%Y-%m") AS `Period`, COUNT(*) AS `Generations` FROM `kussin_chatgpt_content_creator_queue` WHERE (`status` LIKE "generated") OR (`status` LIKE "complete") GROUP BY `Period` ORDER BY `Period` DESC;');

        // GET AI OBJECTS
        $aObjects = $this->_getCustomDbResult('SELECT DISTINCT object, object_id AS `Objects` FROM `kussin_chatgpt_content_creator_queue` WHERE (`status` LIKE "generated") OR (`status` LIKE "complete");');

        // LICENSE DATA
        $sLicense = base64_encode(json_encode(array(
            'domain' => Registry::getConfig()->getConfigParam('sShopURL'),
            'production_mode' => Registry::getConfig()->isProductiveMode(),
            'company' => $oShop->oxshops__oxcompany->value,
            'email' => $oShop->oxshops__oxowneremail->value,
            'billing_address' => array(
                'company' => $oShop->oxshops__oxcompany->value,
                'first_name' => $oShop->oxshops__oxfname->value,
                'last_name' => $oShop->oxshops__oxlname->value,
                'address_1' => $oShop->oxshops__oxstreet->value,
                'postcode' => $oShop->oxshops__oxzip->value,
                'city' => $oShop->oxshops__oxcity->value,
                'country' => $oShop->oxshops__oxcountry->value,
                'email' => $oShop->oxshops__oxowneremail->value,
                'phone' => $oShop->oxshops__oxtelefon->value,
            ),
            'generations' => $aGenerations,
            'objects' => $aObjects,
            'timestamp' => date('Y-m-d H:i:s'),
        )));

        return (file_put_contents($sFilename, $sLicense) !== false);
    }
}