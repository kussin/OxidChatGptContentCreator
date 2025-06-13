<?php
/**
 * Metadata version
 */
$sMetadataVersion = '2.0';

use Kussin\ChatGpt\Core\ModuleEvents;
use OxidEsales\Eshop\Application\Controller\Admin\ArticleMain;
use OxidEsales\Eshop\Application\Controller\Admin\CategoryMain;
use OxidEsales\Eshop\Application\Controller\Admin\CategoryText;
use OxidEsales\Eshop\Application\Controller\Admin\ManufacturerMain;
use OxidEsales\Eshop\Application\Controller\Admin\VendorMain;
use QuneMedia\ChatGpt\Connector\OpenAiModels;
use QuneMedia\ChatGpt\Prompts\Prompt;

// FILE PATH
$sModulePath = dirname(__FILE__);

/**
 * Module information
 */
$aModule = array(
    'id'           => 'kussin/chatgpt-content-creator',
    'title'        => 'Kussin | ChatGPT Content Creator for OXID eShop',
    'description'  => 'ChatGPT Integration for OXID eShop',
    'thumbnail'    => 'module.png',
    'version'      => '0.0.5.1',
    'author'       => 'Daniel Kussin',
    'url'          => 'https://www.kussin.de',
    'email'        => 'daniel.kussin@kussin.de',

    'extend'       => array(
        ArticleMain::class => Kussin\ChatGpt\Controller\Admin\ArticleMain::class,
        CategoryMain::class => Kussin\ChatGpt\Controller\Admin\CategoryMain::class,
//        CategoryText::class => Kussin\ChatGpt\Controller\Admin\CategoryText::class,
        ManufacturerMain::class => Kussin\ChatGpt\Controller\Admin\ManufacturerMain::class,
        VendorMain::class => Kussin\ChatGpt\Controller\Admin\VendorMain::class,
    ),

    'events' => array(
        'onActivate' => ModuleEvents::class . '::onActivate',
    ),

    'controllers' => array(
        'article_chatgpt' => Kussin\ChatGpt\Controller\Admin\ArticleChatGPT::class,
        'chatgpt_bulk_approval' => Kussin\ChatGpt\Controller\Admin\ChatGPTBulkApproval::class,
        'chatgpt_exporter' => Kussin\ChatGpt\Controller\Admin\ChatGPTExporter::class,
        'chatgpt_popup' => Kussin\ChatGpt\Controller\ChatGPTPopup::class,
        'chatgpt_preview' => Kussin\ChatGpt\Controller\ChatGPTPreview::class,
        'process' => Kussin\ChatGpt\Cron\Process::class,
        'chatgpt_weight' => Kussin\ChatGpt\Cron\ChatGPTWeight::class,
        'chatgpt_weight_range' => Kussin\ChatGpt\Controller\Admin\CategoryWeightSettings::class,
    ),

    'templates' => array(
        'chatgpt_bulk_approval.tpl' => 'kussin/chatgpt-content-creator/views/tpl/admin/chatgpt_bulk_approval.tpl',
        'chatgpt_exporter.tpl' => 'kussin/chatgpt-content-creator/views/tpl/admin/chatgpt_exporter.tpl',
        'article_chatgpt.tpl' => 'kussin/chatgpt-content-creator/views/tpl/admin/article_chatgpt.tpl',

        // INCLUDES
        'materialize.tpl' => 'kussin/chatgpt-content-creator/views/tpl/admin/inc/materialize.tpl',
        'loader.tpl' => 'kussin/chatgpt-content-creator/views/tpl/admin/inc/loader.tpl',
        'chatgpt_bulk_actions.tpl' => 'kussin/chatgpt-content-creator/views/tpl/admin/inc/chatgpt_bulk_actions.tpl',

        // POPUP
        'chatgpt_popup.tpl' => 'kussin/chatgpt-content-creator/views/tpl/chatgpt_popup.tpl',

        // IFRAME
        'chatgpt_preview.tpl' => 'kussin/chatgpt-content-creator/views/tpl/chatgpt_preview.tpl',

        // WEIGHT RANGE
        'chatgpt_weightrange.tpl' => 'kussin/chatgpt-content-creator/views/tpl/admin/chatgpt_weightrange.tpl',
    ),

    'blocks' => array(
        array(
            'template' => 'article_main.tpl',
            'block' => 'admin_article_main_form',
            'file' => 'views/blocks/admin/admin_article_main_form.tpl',
        ),
        array(
            'template' => 'headitem.tpl',
            'block' => 'admin_headitem_inccss',
            'file' => 'views/blocks/admin/admin_headitem_inccss.tpl',
        ),
        array(
            'template' => 'include/category_main_form.tpl',
            'block' => 'admin_category_main_form',
            'file' => 'views/blocks/admin/admin_category_main_form.tpl',
        ),
        array(
            'template' => 'manufacturer_main.tpl',
            'block' => 'admin_manufacturer_main_form',
            'file' => 'views/blocks/admin/admin_manufacturer_main_form.tpl',
        ),
        array(
            'template' => 'vendor_main.tpl',
            'block' => 'admin_vendor_main_form',
            'file' => 'views/blocks/admin/admin_vendor_main_form.tpl',
        ),

        array(
            'template' => 'layout/base.tpl',
            'block' => 'base_style',
            'file' => 'views/blocks/base_style.tpl',
        ),
        array(
            'template' => 'page/details/inc/tabs.tpl',
            'block' => 'details_tabs_longdescription',
            'file' => 'views/blocks/details_tabs_longdescription.tpl',
        ),
        array(
            'template' => 'page/list/list.tpl',
            'block' => 'page_list_listbody',
            'file' => 'views/blocks/page_list_listbody.tpl',
        ),
    ),

    'settings' => array(
        array(
            'group' => 'sKussinChatGptSettings',
            'name' => 'sKussinChatGptApiKey',
            'type' => 'str',
            'value' => '',
        ),
        array(
            'group' => 'sKussinChatGptSettings',
            'name' => 'sKussinChatGptApiModel',
            'type' => 'select',
            'value' => OpenAiModels::getDefaultModel(),
            'constraints' => implode('|', OpenAiModels::getConstraints()),
        ),
        array(
            'group' => 'sKussinChatGptSettings',
            'name' => 'dKussinChatGptApiTemperature',
            'type' => 'str',
            'value' => 0.7,
        ),
        array(
            'group' => 'sKussinChatGptSettings',
            'name' => 'iKussinChatGptApiMaxTokens',
            'type' => 'str',
            'value' => 350,
        ),
        array(
            'group' => 'sKussinChatGptSettings',
            'name' => 'blKussinChatGptArticleDataEnhancerEnabled',
            'type' => 'bool',
            'value' => 0,
        ),
        array(
            'group' => 'sKussinChatGptSettings',
            'name' => 'sKussinChatGptArticleDataEnhancerArticleIdKey',
            'type' => 'str',
            'value' => 'oxarticles__oxartnum',
        ),
        array(
            'group' => 'sKussinChatGptFrontendSettings',
            'name' => 'blKussinChatGptFrontendDisclaimerEnabled',
            'type' => 'bool',
            'value' => 1,
        ),
        array(
            'group' => 'sKussinChatGptFrontendSettings',
            'name' => 'sKussinChatGptFrontendDisclaimerCmsId',
            'type' => 'str',
            'value' => 'kussin_chatgpt_disclaimer',
        ),
        array(
            'group' => 'sKussinChatGptPromptContextSettings',
            'name' => 'sKussinChatGptPromptContext',
            'type' => 'str',
            'value' => '',
        ),
        array(
            'group' => 'sKussinChatGptPromptSettings',
            'name' => 'sKussinChatGptPromptLongDescriptionDE',
            'type' => 'str',
            'value' => Prompt::load()->get('LONG_DESCRIPTION', 'de_DE'),
        ),
        array(
            'group' => 'sKussinChatGptPromptSettings',
            'name' => 'sKussinChatGptPromptLongDescriptionEN',
            'type' => 'str',
            'value' => Prompt::load()->get('LONG_DESCRIPTION', 'en_US'),
        ),
        array(
            'group' => 'sKussinChatGptPromptSettings',
            'name' => 'sKussinChatGptPromptShortDescriptionDE',
            'type' => 'str',
            'value' => Prompt::load()->get('SHORT_DESCRIPTION', 'de_DE'),
        ),
        array(
            'group' => 'sKussinChatGptPromptSettings',
            'name' => 'sKussinChatGptPromptShortDescriptionEN',
            'type' => 'str',
            'value' => Prompt::load()->get('SHORT_DESCRIPTION', 'en_US'),
        ),
        array(
            'group' => 'sKussinChatGptPromptSettings',
            'name' => 'sKussinChatGptPromptProductSearchKeysDE',
            'type' => 'str',
            'value' => Prompt::load()->get('PRODUCT_SEARCHKEYS', 'de_DE'),
        ),
        array(
            'group' => 'sKussinChatGptPromptSettings',
            'name' => 'sKussinChatGptPromptProductSearchKeysEN',
            'type' => 'str',
            'value' => Prompt::load()->get('PRODUCT_SEARCHKEYS', 'en_US'),
        ),
        array(
            'group' => 'sKussinChatGptPromptSettings',
            'name' => 'sKussinChatGptPromptProductAttributesDE',
            'type' => 'str',
            'value' => Prompt::load()->get('PRODUCT_ATTRIBUTES', 'de_DE'),
        ),
        array(
            'group' => 'sKussinChatGptPromptSettings',
            'name' => 'sKussinChatGptPromptProductAttributesEN',
            'type' => 'str',
            'value' => Prompt::load()->get('PRODUCT_ATTRIBUTES', 'en_US'),
        ),
        array(
            'group' => 'sKussinChatGptPromptSettings',
            'name' => 'sKussinChatGptPromptOptimizeContentDE',
            'type' => 'str',
            'value' => Prompt::load()->get('OPTIMIZE_CONTENT', 'de_DE'),
        ),
        array(
            'group' => 'sKussinChatGptPromptSettings',
            'name' => 'sKussinChatGptPromptOptimizeContentEN',
            'type' => 'str',
            'value' => Prompt::load()->get('OPTIMIZE_CONTENT', 'en_US'),
        ),
        array(
            'group' => 'sKussinChatGptPromptSettings',
            'name' => 'sKussinChatGptPromptWeight',
            'type' => 'str',
            'value' => 'Return the weight of the item "%s" by "%s" as a floating-point number in kilograms only, without any additional text or units. Answer only with the number and use `%s` if no plausible weight can be determined. The weight is plausible if it is between `%s` and `%s`. - FYI: The item is assigned to the following category or has the category path "%s".',
        ),
        array(
            'group' => 'sKussinChatGptProcessSettings',
            'name' => 'blKussinChatGptProcessQueueEnabled',
            'type' => 'bool',
            'value' => 0,
        ),
        array(
            'group' => 'sKussinChatGptProcessSettings',
            'name' => 'sKussinChatGptProcessSelectionQuery',
            'type' => 'str',
            'value' => file_get_contents($sModulePath . '/sql/oxartextends.example.sql'),
        ),
        array(
            'group' => 'sKussinChatGptProcessSettings',
            'name' => 'sKussinChatGptProcessModel',
            'type' => 'select',
            'value' => OpenAiModels::getDefaultModel(),
            'constraints' => implode('|', OpenAiModels::getConstraints()),
        ),
        array(
            'group' => 'sKussinChatGptProcessSettings',
            'name' => 'dKussinChatGptProcessTemperature',
            'type' => 'str',
            'value' => 0.7,
        ),
        array(
            'group' => 'sKussinChatGptProcessSettings',
            'name' => 'iKussinChatGptProcessMaxTokens',
            'type' => 'str',
            'value' => 350,
        ),
        array(
            'group' => 'sKussinChatGptProcessSettings',
            'name' => 'iKussinChatGptProcessLimitMaxPrompts',
            'type' => 'str',
            'value' => 10,
        ),
        array(
            'group' => 'sKussinChatGptProcessSettings',
            'name' => 'iKussinChatGptProcessLimitMaxGenerations',
            'type' => 'str',
            'value' => 1,
        ),
        array(
            'group' => 'sKussinChatGptProcessSettings',
            'name' => 'iKussinChatGptProcessLimitMaxReplacements',
            'type' => 'str',
            'value' => 10,
        ),
        array(
            'group' => 'sKussinChatGptProcessSettings',
            'name' => 'sKussinChatGptProcessProductAttributesForbiddenValues',
            'type' => 'str',
            'value' => 'unknown',
        ),
        array(
            'group' => 'sKussinChatGptProcessSettings',
            'name' => 'blKussinChatGptProcessQueueAutoApprovedEnabled',
            'type' => 'bool',
            'value' => 0,
        ),
        array(
            'group' => 'sKussinChatGptWeightSettings',
            'name' => 'sKussinChatGptWeightQueryTimestamp',
            'type' => 'str',
            'value' => '2025-01-01 00:00:00',
        ),
        array(
            //HIDDEN SETTING
            //'group' => 'sKussinChatGptWeightSettings',
            'name' => 'sKussinCategoryWeightRanges',
            'type' => 'str',
            'value' => '{"Water\/Wake\/Wakeboards":{"min":4,"max":6,"default":5}}',
        ),
        array(
            'group' => 'sKussinChatGptDebugSettings',
            'name' => 'blKussinChatGptDebugEnabled',
            'type' => 'bool',
            'value' => 0,
        ),
        array(
            'group' => 'sKussinChatGptDebugSettings',
            'name' => 'sKussinChatGptDebugLogFilename',
            'type' => 'str',
            'value' => 'log/KUSSIN_CHATGPT.log',
        ),
    )
);