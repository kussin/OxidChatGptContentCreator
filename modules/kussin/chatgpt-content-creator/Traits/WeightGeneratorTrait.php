<?php

namespace Kussin\ChatGpt\Traits;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Category;
use OxidEsales\Eshop\Application\Model\CategoryList;

trait WeightGeneratorTrait
{
    public function kussinchatgptweight()
    {
        $iMaxTokens = (int) Registry::getConfig()->getConfigParam('iKussinChatGptApiMaxTokens');
        $sPrompt = trim(Registry::getConfig()->getConfigParam('sKussinChatGptPromptWeight'));
        $oArticle = $this->_kussinLoadArticle();
        $sCategoryPath = $this->getArticleCategoryPath($oArticle);
        $aRange = $this->getCategoryWeightRange($sCategoryPath);

        // Load title
        $sTitle = $oArticle->oxarticles__oxtitle->value;

        // Check if variant
        if ($oArticle->oxarticles__oxparentid->value) {
            $oParent = oxNew(\OxidEsales\Eshop\Application\Model\Article::class);
            if ($oParent->load($oArticle->oxarticles__oxparentid->value)) {
                $sTitle = trim($oParent->oxarticles__oxtitle->value . ' size:' . $oArticle->oxarticles__oxvarselect->value);
            }
        }

        if ($sPrompt == '') {
            // FALLBACK
            $sPrompt = 'Return the weight of the item "%s" by "%s" as a floating-point number in kilograms only, without any additional text or units. Answer only with the number and use `%s` if no plausible weight can be determined. The weight is plausible if it is between `%s` and `%s`. - FYI: The item is assigned to the following category or has the category path "%s".';
        }

        $this->_info(array(
            'method' => __CLASS__ . '::' . __FUNCTION__,
            'prompt' => $sPrompt,
            'params' => array(
                'title' => $oArticle->oxarticles__oxtitle->value,
                'manufacturer' => $oArticle->getManufacturer()->oxmanufacturers__oxtitle->value,
                'max_tokens' => $iMaxTokens,
            ),
        ));

        $sPrompt = sprintf(
            $sPrompt,
            $sTitle,
            $oArticle->getManufacturer()->oxmanufacturers__oxtitle->value,
            $aRange['default'],
            $aRange['min'],
            $aRange['max'],
            $sCategoryPath
        );

        $aResponse = $this->_kussinGetChatGptContent($sPrompt, false, false, floor($iMaxTokens * 0.9));
        $fWeight = trim($aResponse['data']);

        if ($aResponse['error'] === null) {
            try {
                $oArticle = oxNew(Article::class);
                $oArticle->load($this->_kussinLoadArticle()->getId());

                $oArticle->oxarticles__oxweight = new Field($fWeight);
                $oArticle->oxarticles__kussinchatgptgenerated = new Field(1);
                $oArticle->save();
            } catch (\Exception $oException) {
                $this->_error(array(
                    'method' => __CLASS__ . '::' . __FUNCTION__,
                    'response' => $oException,
                ));
            }
        }
    }

    private function getArticleCategoryPath(Article $oArticle): string
    {
        $aCatIds = $oArticle->getCategoryIds();
        $aPaths = [];

        foreach ($aCatIds as $sCatId) {
            $sPath = $this->getCategoryPathById($sCatId);
            if ($sPath) {
                $aPaths[] = $sPath;
            }
        }

        return implode(' | ', $aPaths);
    }

    private function getCategoryPathById(string $sCategoryId): string
    {
        $oCategory = oxNew(Category::class);
        if (!$oCategory->load($sCategoryId)) {
            return '';
        }

        $aTitles = [];

        while ($oCategory && $oCategory->oxcategories__oxid->value !== 'oxrootid') {
            $sTitle = html_entity_decode(
                $oCategory->oxcategories__oxtitle->value,
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            );
            $aTitles[] = trim($sTitle);
            $oCategory = $oCategory->getParentCategory();
        }

        return implode('/', array_reverse($aTitles));
    }
    private function getCategoryWeightRange(string $sCategoryPath): array
    {
        $aRanges = json_decode(Registry::getConfig()->getConfigParam('sKussinCategoryWeightRanges'), true);
        $aDefaultRange = ['min' => 2, 'max' => 6, 'default' => 4];

//        dumpVar([
//            '$sCategoryPath' => $sCategoryPath,
//            '$aRanges' => $aRanges,
//        ], true);

        if (!is_array($aRanges)) {
            return $aDefaultRange;
        }

        $aPaths = array_map('trim', explode('|', $sCategoryPath));

        foreach ($aPaths as $sPathToCheck) {
            $sPathToCheck = html_entity_decode($sPathToCheck, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            foreach ($aRanges as $sPath => $aRange) {
                $sPath = html_entity_decode($sPath, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                if (stripos($sPathToCheck, $sPath) === 0 && array_sum($aRange) > 0) {
                    return $aRange;
                }
            }
        }

        return $aDefaultRange;
    }

}
