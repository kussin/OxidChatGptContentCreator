<?php

namespace Kussin\ChatGpt\Controller\Admin;

use Kussin\ChatGpt\Traits\ArticleDataEnhancerTrait;
use Kussin\ChatGpt\Traits\ChatGPTClientTrait;
use Kussin\ChatGpt\Traits\LanguageTrait;
use Kussin\ChatGpt\Traits\LoggerTrait;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use QuneMedia\ChatGpt\Prompts\LanguageMapper;
use QuneMedia\ChatGpt\Prompts\Prompt;
use \Kussin\ChatGpt\Traits\WeightGeneratorTrait;


class ArticleMain extends ArticleMain_parent
{
    use ArticleDataEnhancerTrait;
    use ChatGPTClientTrait;
    use LanguageTrait;
    use LoggerTrait;
    use WeightGeneratorTrait;

    private $_oArticle = null;

    public function save()
    {
        parent::save();

        $iWeight = (float) $this->_kussinLoadArticle()->oxarticles__oxweight->value;
        if ($iWeight <= 0) {
            $this->kussinchatgptweight();
        }
    }
    private function _kussinLoadArticle()
    {
        if ($this->_oArticle === null) {
            $this->_oArticle = oxNew(Article::class);
            $this->_oArticle->load($this->getEditObjectId());
        }

        return $this->_oArticle;
    }

    public function kussinchatgptlongdesc()
    {
        $iMaxTokens = (int) Registry::getConfig()->getConfigParam('iKussinChatGptApiMaxTokens');
        $sPrompt = trim(Registry::getConfig()->getConfigParam('sKussinChatGptPromptLongDescription' . $this->_getLanguageCode($iLang)));

        if ($sPrompt == '') {
            // FALLBACK
            $oLang = Registry::getLang();
            $sLocaleCode = LanguageMapper::getLocaleCode($oLang->getLanguageAbbr($oLang->getBaseLanguage()));
            $sPrompt = Prompt::load()->get('LONG_DESCRIPTION', $sLocaleCode);
        }

        $this->_info(array(
            'method' => __CLASS__ . '::' . __FUNCTION__,
            'prompt' => $oException,
            'params' => array(
                'title' => $this->_kussinLoadArticle()->oxarticles__oxtitle->value,
                'manufacturer' => $this->_kussinLoadArticle()->getManufacturer()->oxmanufacturers__oxtitle->value,
                'max_tokens' => $iMaxTokens,
            ),
        ));

        // GET PROMPT
        $sPrompt = sprintf(
            $sPrompt,
            $this->_kussinLoadArticle()->oxarticles__oxtitle->value,
            $this->_kussinLoadArticle()->getManufacturer()->oxmanufacturers__oxtitle->value,
            $iMaxTokens
        );

        // EXTENT PROMPT W/ ENHANCED ARTICLE DATA
        $sArticleIdKey = trim(Registry::getConfig()->getConfigParam('sKussinChatGptArticleDataEnhancerArticleIdKey'));
        $sPrompt .= $this->_getEnhancedArticlePrompt($this->_kussinLoadArticle()->{$sArticleIdKey}->value);

        // GET CHATGPT CONTENT
        $aResponse = $this->_kussinGetChatGptContentLongDescription($sPrompt, FALSE, FALSE, floor($iMaxTokens * 1.1), TRUE);

        if ($aResponse['error'] == NULL) {

            try {
                // SAVE ARTICLE
                parent::save();

                // SAVE DESCRIPTION
                $oArticle = oxNew(Article::class);
                $oArticle->load( $this->_kussinLoadArticle()->getId() );

                $oContent = new Field(trim($aResponse['data']));
                $oArticle->setArticleLongDesc($oContent->getRawValue());

                $oArticle->save();

            } catch (\Exception $oException) {
                // ERROR
                $this->_error(array(
                    'method' => __CLASS__ . '::' . __FUNCTION__,
                    'response' => $oException,
                ));
            }
        }
    }

    public function kussinchatgptoptimizedesc()
    {
        $iMaxTokens = (int) Registry::getConfig()->getConfigParam('iKussinChatGptApiMaxTokens');
        $sPrompt = trim(Registry::getConfig()->getConfigParam('sKussinChatGptPromptOptimizeContent' . $this->_getLanguageCode($iLang)));

        if ($sPrompt == '') {
            // FALLBACK
            $oLang = Registry::getLang();
            $sLocaleCode = LanguageMapper::getLocaleCode($oLang->getLanguageAbbr($oLang->getBaseLanguage()));
            $sPrompt = Prompt::load()->get('OPTIMIZE_CONTENT', $sLocaleCode);
        }

        $this->_info(array(
            'method' => __CLASS__ . '::' . __FUNCTION__,
            'prompt' => $sPrompt,
            'params' => array(
                'title' => $this->_kussinLoadArticle()->getLongDescription(),
            ),
        ));

        // GET PROMPT
        $sPrompt = sprintf(
            $sPrompt,
            $this->_kussinLoadArticle()->getLongDescription(),
        );

        // EXTENT PROMPT W/ ENHANCED ARTICLE DATA
        $sArticleIdKey = trim(Registry::getConfig()->getConfigParam('sKussinChatGptArticleDataEnhancerArticleIdKey'));
        $sPrompt .= $this->_getEnhancedArticlePrompt($this->_kussinLoadArticle()->{$sArticleIdKey}->value);

        // GET CHATGPT CONTENT
        $aResponse = $this->_kussinGetChatGptContent($sPrompt, FALSE, FALSE, floor($iMaxTokens * 1.1), TRUE);

        if ($aResponse['error'] == NULL) {

            try {
                // SAVE ARTICLE
                parent::save();

                // SAVE DESCRIPTION
                $oArticle = oxNew(Article::class);
                $oArticle->load( $this->_kussinLoadArticle()->getId() );

                $oContent = new Field(trim($aResponse['data']));
                $oArticle->setArticleLongDesc($oContent->getRawValue());
                $oArticle->oxarticles__kussinchatgptgenerated = new Field(1);

                $oArticle->save();

            } catch (\Exception $oException) {
                // ERROR
                $this->_error(array(
                    'method' => __CLASS__ . '::' . __FUNCTION__,
                    'response' => $oException,
                ));
            }
        }
    }

    public function kussinchatgptshortdesc()
    {
        $iMaxTokens = 150;
        $sPrompt = trim(Registry::getConfig()->getConfigParam('sKussinChatGptPromptShortDescription' . $this->_getLanguageCode($iLang)));

        if ($sPrompt == '') {
            // FALLBACK
            $oLang = Registry::getLang();
            $sLocaleCode = LanguageMapper::getLocaleCode($oLang->getLanguageAbbr($oLang->getBaseLanguage()));
            $sPrompt = Prompt::load()->get('SHORT_DESCRIPTION', $sLocaleCode);
        }

        $this->_info(array(
            'method' => __CLASS__ . '::' . __FUNCTION__,
            'prompt' => $oException,
            'params' => array(
                'title' => $this->_kussinLoadArticle()->oxarticles__oxtitle->value,
                'manufacturer' => $this->_kussinLoadArticle()->getManufacturer()->oxmanufacturers__oxtitle->value,
                'max_tokens' => $iMaxTokens,
            ),
        ));

        // GET PROMPT
        $sPrompt = sprintf(
            $sPrompt,
            $this->_kussinLoadArticle()->oxarticles__oxtitle->value,
            $this->_kussinLoadArticle()->getManufacturer()->oxmanufacturers__oxtitle->value,
            $iMaxTokens
        );

        // GET CHATGPT CONTENT
        $aResponse = $this->_kussinGetChatGptContent($sPrompt, FALSE, FALSE, floor($iMaxTokens * 0.9));

        if ($aResponse['error'] == NULL) {

            try {
                // SAVE ARTICLE
                parent::save();

                // SAVE DESCRIPTION
                $oArticle = oxNew(Article::class);
                $oArticle->load( $this->_kussinLoadArticle()->getId() );

                $oArticle->oxarticles__oxshortdesc = new Field(trim($aResponse['data']));
                $oArticle->oxarticles__kussinchatgptgenerated = new Field(1);

                $oArticle->save();

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
