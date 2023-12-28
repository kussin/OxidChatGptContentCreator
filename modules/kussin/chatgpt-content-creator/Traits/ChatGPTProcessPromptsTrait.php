<?php

namespace Kussin\ChatGpt\Traits;

use OxidEsales\Eshop\Core\Registry;

trait ChatGPTProcessPromptsTrait
{
    protected function _getProcessMaxTokens($sFieldId, $iMaxTokens = 350) : int
    {
        switch ($sFieldId) {
            case 'oxshortdesc':
                $dFactor = 0.9;
                break;

            default:
            case 'oxlongdesc':
                $dFactor = 1.1;
                break;
        }

        return floor((int) $iMaxTokens * $dFactor);
    }

    protected function _useHtml($sFieldId) : bool
    {
        switch ($sFieldId) {
            case 'oxlongdesc':
                return true;

            default:
            case 'oxshortdesc':
                return false;
        }
    }

    private function _getProcessPrompts($oObject, $sFieldId, $iLang = 0, $iMaxTokens = 350)
    {
        switch ($sFieldId) {
            case 'oxshortdesc':
                $sPrompt = $this->_getChatGptProcessPrompt4ShortDescription($iLang);
                $sTitle = $this->_encodeProcessSpecialChars($oObject->oxarticles__oxtitle->value);
                $sManufacturer = $this->_encodeProcessSpecialChars($oObject->getManufacturer()->oxmanufacturers__oxtitle->value);
                break;

            default:
            case 'oxlongdesc':
                $sPrompt = $this->_getChatGptProcessPrompt4LongDescription($iLang);
                $sTitle = $this->_encodeProcessSpecialChars($oObject->oxarticles__oxtitle->value);
                $sManufacturer = $this->_encodeProcessSpecialChars($oObject->getManufacturer()->oxmanufacturers__oxtitle->value);
                break;
        }

        // FIX PROMPT
        $sPrompt = str_replace(['"', '"', '&quot;',], '`', $sPrompt);

        return sprintf(
            $sPrompt,
            $sTitle,
            $sManufacturer,
            $iMaxTokens
        );
    }

    protected function _encodeProcessSpecialChars($sString, $sCharset = 'UTF-8') : string
    {
        $sStringUtf8 = mb_convert_encoding($sString, $sCharset, mb_detect_encoding($sString));

        return htmlspecialchars($sStringUtf8, ENT_QUOTES, $sCharset);
    }

    protected function _encodeProcessContent($sString, $sCharset = 'UTF-8') : string
    {
        return base64_encode($sString);
    }

    protected function _decodeProcessContent($sString) : string
    {
        return base64_decode($sString);
    }

    protected function _getChatGptProcessPrompt4ShortDescription($iLang = 0)
    {
        $sPrompt = trim(Registry::getConfig()->getConfigParam('sKussinChatGptPromptShortDescriptionDE')); // TODO: MULTI-LANGUAL

        if ($sPrompt == '') {
            // FALLBACK
            $oLang = Registry::getLang();
            $sPrompt = $oLang->translateString('KUSSIN_CHATGPT_SHORT_DESCRIPTION_PROMPT', $iLang);
        }

        return $sPrompt;
    }

    protected function _getChatGptProcessPrompt4LongDescription($iLang = 0)
    {
        $sPrompt = trim(Registry::getConfig()->getConfigParam('sKussinChatGptPromptLongDescriptionDE')); // TODO: MULTI-LANGUAL

        if ($sPrompt == '') {
            // FALLBACK
            $oLang = Registry::getLang();
            $sPrompt = $oLang->translateString('KUSSIN_CHATGPT_LONG_DESCRIPTION_PROMPT', $iLang);
        }

        return $sPrompt;
    }
}