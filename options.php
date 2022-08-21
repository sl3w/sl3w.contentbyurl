<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

$request = HttpApplication::getInstance()->getContext()->getRequest();

$module_id = htmlspecialcharsbx($request['mid'] != '' ? $request['mid'] : $request['id']);

Loader::includeModule($module_id);

if (!CModule::IncludeModule('iblock')) {
    ShowMessage(GetMessage('IBLOCK_ERROR'));
    return false;
}

$dbIBlocks = CIBlock::GetList(['SORT' => 'ASC'], ['ACTIVE' => 'Y']);

while ($arIBlock = $dbIBlocks->GetNext()) {
    $selectIBlocks[$arIBlock['ID']] = '[' . $arIBlock['ID'] . '] ' . $arIBlock['NAME'];
}

$selectFields = [
    'NAME' => Loc::getMessage('CONTENT_BY_URL_OPTION_KEY_OPTION_NAME'),
    'PREVIEW_TEXT' => Loc::getMessage('CONTENT_BY_URL_OPTION_KEY_OPTION_PREVIEW_TEXT'),
    'DETAIL_TEXT' => Loc::getMessage('CONTENT_BY_URL_OPTION_KEY_OPTION_DETAIL_TEXT'),
];

$selectFieldsAndProps = $selectFields;

$propsRes = CIBlock::GetProperties(Option::get($module_id, 'iblock_id'));

while ($prop = $propsRes->Fetch()) {
    if ($prop['PROPERTY_TYPE'] == 'S') {
        $selectFieldsAndProps['PROPERTY_' . $prop['CODE']] = '[PROPERTY_' . $prop['CODE'] . '] ' . $prop['NAME'];
    }
}

$aTabs = array(
    [
        'DIV' => 'edit',
        'TAB' => Loc::getMessage('CONTENT_BY_URL_OPTIONS_TAB_NAME'),
        'TITLE' => Loc::getMessage('CONTENT_BY_URL_OPTIONS_TAB_NAME'),
        'OPTIONS' => [
            Loc::getMessage('CONTENT_BY_URL_BLOCK_COMMON'),
            [
                'switch_on',
                Loc::getMessage('CONTENT_BY_URL_OPTION_SWITCH_ON'),
                'Y',
                ['checkbox']
            ],
            [
                'cache_time',
                Loc::getMessage('CONTENT_BY_URL_OPTION_CACHE_TIME'),
                CONTENT_BY_URL_DEFAULT_CACHE_TIME,
                ['text', 10]
            ],
            [
                'additional_class',
                Loc::getMessage('CONTENT_BY_URL_OPTION_ADDITIONAL_CLASS'),
                '',
                ['text', 30]
            ],
            Loc::getMessage('CONTENT_BY_URL_BLOCK_IBLOCK'),
            [
                'iblock_id',
                Loc::getMessage('CONTENT_BY_URL_OPTION_IBLOCK_ID'),
                get_iblock_id_by_code(CONTENT_BY_URL_DEFAULT_IBLOCK_CODE),
                ['selectbox', $selectIBlocks]
            ],
            ['note' => Loc::getMessage('CONTENT_BY_URL_SAVE_AFTER_CHANGE_IBLOCK')],
            [
                'key_field_name',
                Loc::getMessage('CONTENT_BY_URL_OPTION_KEY_FIELD_NAME'),
                CONTENT_BY_URL_DEFAULT_KEY_FIELD_NAME,
                ['selectbox', $selectFields]
            ],
            [
                'value_field_name',
                Loc::getMessage('CONTENT_BY_URL_OPTION_VALUE_FIELD_NAME'),
                CONTENT_BY_URL_DEFAULT_VALUE_FIELD_NAME,
                ['selectbox', $selectFieldsAndProps]
            ],
            [
                'section_separation',
                Loc::getMessage('CONTENT_BY_URL_SECTION_SEPARATION'),
                'N',
                ['checkbox']
            ],
        ]
    ]
);

$tabControl = new CAdminTabControl(
    'tabControl',
    $aTabs
);

$tabControl->Begin();
?>

    <form action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= $module_id ?>&lang=<?= LANG ?>"
          method="post">

        <?php
        foreach ($aTabs as $aTab) {

            if ($aTab['OPTIONS']) {

                $tabControl->BeginNextTab();

                __AdmSettingsDrawList($module_id, $aTab['OPTIONS']);
            }
        }

        $tabControl->Buttons();
        ?>

        <input type="submit" name="apply" value="<?= Loc::GetMessage('CONTENT_BY_URL_BUTTON_APPLY') ?>"
               class="adm-btn-save"/>
        <input type="submit" name="default" value="<?= Loc::GetMessage('CONTENT_BY_URL_BUTTON_DEFAULT') ?>"/>

        <?= bitrix_sessid_post() ?>

    </form>

<?php
$tabControl->End();

if ($request->isPost() && check_bitrix_sessid()) {

    foreach ($aTabs as $aTab) {

        foreach ($aTab['OPTIONS'] as $arOption) {

            if (!is_array($arOption) || $arOption['note']) {
                continue;
            }

            if ($request['apply']) {

                $optionValue = $request->getPost($arOption[0]);

                if ($arOption[0] == 'switch_on' && $optionValue == '') {
                    $optionValue = 'N';
                }

                Option::set($module_id, $arOption[0], is_array($optionValue) ? implode(',', $optionValue) : $optionValue);

            } elseif ($request['default']) {

                Option::set($module_id, $arOption[0], $arOption[2]);
            }
        }
    }

    LocalRedirect($APPLICATION->GetCurPage() . '?mid=' . $module_id . '&lang=' . LANG);
}
