<?php
namespace Kussin\ChatGpt\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Application\Model\Category;
use OxidEsales\Eshop\Application\Model\CategoryList;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleSettingBridgeInterface;

class CategoryWeightSettings extends AdminDetailsController
{
    protected $_sThisTemplate = 'chatgpt_weightrange.tpl';

    public function render()
    {
        parent::render();

        $this->_aViewData['aCategoryPaths'] = $this->getAllActiveCategoryPaths();
        $this->_aViewData['aRanges'] = $this->getStoredRanges();

        return $this->_sThisTemplate;
    }
    public function save()
    {
        $aRanges = Registry::getRequest()->getRequestParameter('ranges');
        $aClean = [];

        foreach ($aRanges as $sPath => $aData) {
            $sMin = trim($aData['min'] ?? '');
            $sMax = trim($aData['max'] ?? '');
            $sDefault = trim($aData['default'] ?? '');

            // Skip empty rows
            if ($sMin === '' && $sMax === '' && $sDefault === '') {
                continue;
            }

            $aClean[trim($sPath)] = [
                'min'     => (float) $sMin,
                'max'     => (float) $sMax,
                'default' => (float) $sDefault,
            ];
        }

        $oModuleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);

        $oModuleSettingBridge->save('sKussinCategoryWeightRanges', json_encode($aClean), 'kussin/chatgpt-content-creator');

        //fetched and render new values
        Registry::getUtils()->redirect($this->getViewConfig()->getSelfLink() . '&cl=chatgpt_weight_range', false, 302);
    }


    protected function getStoredRanges(): array
    {
        $json = \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('sKussinCategoryWeightRanges');

        return json_decode($json, true) ?? [];
    }


    protected function getAllActiveCategoryPaths(): array
    {
        $catList = oxNew(CategoryList::class);
        $catList->loadList();
        $paths = [];

        foreach ($catList as $cat) {
            if ((int)$cat->oxcategories__oxactive->value !== 1) {
                continue;
            }

            $paths[$cat->getId()] = $this->getCategoryPath($cat);
        }

        return $paths;
    }

    protected function getCategoryPath(Category $cat): string
    {
        $parts = [];

        while ($cat && $cat->getId() !== 'oxrootid') {
            $sTitle = html_entity_decode(
                $cat->oxcategories__oxtitle->value,
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            );

            // Clean up title: remove leading -, whitespace, tabs
            $sTitle = preg_replace('/^[\-\s]+/', '', $sTitle);

            $parts[] = trim($sTitle);
            $cat = $cat->getParentCategory();
        }

        return implode('/', array_reverse($parts));
    }







}