<?php

namespace Kussin\ChatGpt\Controller\Admin;

use Kussin\ChatGpt\Traits\ChatGPTClientTrait;
use Kussin\ChatGpt\Traits\LoggerTrait;
use OxidEsales\Eshop\Application\Model\Vendor;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;

class VendorMain extends VendorMain_parent
{
    use ChatGPTClientTrait;
    use LoggerTrait;

    private $_oVendor = null;

    private function _kussinLoadVendor()
    {
        if ($this->_oVendor === null) {
            $this->_oVendor = oxNew(Vendor::class);
            $this->_oVendor->load($this->getEditObjectId());
        }

        return $this->_oVendor;
    }

    public function kussinchatgptshortdesc()
    {
        $iMaxTokens = 150;

        // PROMPT
        $oLang = Registry::getLang();
        $sPrompt = $oLang->translateString('KUSSIN_CHATGPT_VENDOR_SHORT_DESCRIPTION_PROMPT', $oLang->getBaseLanguage());

        $this->_info(array(
            'method' => __CLASS__ . '::' . __FUNCTION__,
            'prompt' => $oException,
            'params' => array(
                'category' => $this->_kussinLoadVendor()->oxvendor__oxtitle->value,
                'url' => $this->_kussinLoadVendor()->getLink(),
                'max_tokens' => $iMaxTokens,
            ),
        ));

        // GET PROMPT
        $sPrompt = sprintf(
            $sPrompt,
            $this->_kussinLoadVendor()->oxvendor__oxtitle->value,
            $this->_kussinLoadVendor()->getLink()
        );

        // GET CHATGPT CONTENT
        $aResponse = $this->_kussinGetChatGptContent($sPrompt, FALSE, FALSE, floor($iMaxTokens * 0.9));

        if ($aResponse['error'] == NULL) {

            try {
                // SAVE VENDOR
                parent::save();

                // SAVE DESCRIPTION
                $oCategory = oxNew(Vendor::class);
                $oCategory->load( $this->_kussinLoadVendor()->getId() );

                $oContent = new Field($aResponse['data']);
                $oCategory->oxvendor__oxshortdesc = $oContent;

                $oCategory->save();

            } catch (\Exception $oException) {
                // ERROR
                $this->_error(array(
                    'method' => __CLASS__ . '::' . __FUNCTION__,
                    'response' => $oException,
                ));
            }
        }
    }
}
