<?php

use Bitrix\Main\Localization\Loc;
use ContentByUrl\Settings;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

if (!CModule::IncludeModule('iblock')) {
    ShowMessage(Loc::getMessage('IBLOCK_MODULE_ERROR'));
    return false;
}

if (!CModule::IncludeModule('sl3w.contentbyurl')) {
    ShowMessage(Loc::getMessage('CONTENT_BY_URL_MODULE_ERROR'));
    return false;
}

/*$dbIBlockTypes = CIBlockType::GetList(array('SORT'=>'ASC'), array('ACTIVE'=>'Y'));
while ($arIBlockTypes = $dbIBlockTypes->GetNext())
{
    $paramIBlockTypes[$arIBlockTypes['ID']] = $arIBlockTypes['ID'];
}*/

//if ($arCurrentValues['IBLOCK_TYPE']) {

$paramIBlocks['MODULE'] = Loc::getMessage('FROM_MODULE');

$dbIBlocks = CIBlock::GetList(
    [
        'SORT' => 'ASC'
    ],
    [
        'ACTIVE' => 'Y',
//        'TYPE' => $arCurrentValues['IBLOCK_TYPE'],
    ]);

while ($arIBlock = $dbIBlocks->GetNext()) {
    $paramIBlocks[$arIBlock['ID']] = '[' . $arIBlock['ID'] . '] ' . $arIBlock['NAME'];
}
//}

$selectFields = [
    'MODULE' => Loc::getMessage('FROM_MODULE'),
    'NAME' => '[NAME] Название',
    'PREVIEW_TEXT' => '[PREVIEW_TEXT] Описание для анонса',
    'DETAIL_TEXT' => '[DETAIL_TEXT] Детальное описание',
];

$selectFieldsAndProps = $selectFields;

$sections = ['0' => Loc::getMessage('NO_SECTION')];

if ($iblockId = intval($arCurrentValues['IBLOCK_ID'])) {
    $propsRes = CIBlock::GetProperties($iblockId);

    while ($prop = $propsRes->Fetch()) {
        if ($prop['PROPERTY_TYPE'] == 'S') {
            $selectFieldsAndProps['PROPERTY_' . $prop['CODE']] = '[PROPERTY_' . $prop['CODE'] . '] ' . $prop['NAME'];
        }
    }

    $sectionsRes = CIBlockSection::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], ['IBLOCK_ID' => $iblockId], false, ['ID', 'NAME']);

    while ($section = $sectionsRes->Fetch()) {
        $sections[$section['ID']] = '[' . $section['ID'] . '] ' . $section['NAME'];
    }
}

//формирование массива параметров
$arComponentParameters = [
    'GROUPS' => [
        'RESET' => [
            'NAME' => Loc::getMessage('GROUP_RESET'),
            'SORT' => '300',
        ],
    ],
    'PARAMETERS' => [
        /*'IBLOCK_TYPE' => array(
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('IBLOCK_TYPE'),
            'TYPE' => 'LIST',
            'VALUES' => $paramIBlockTypes,
            'REFRESH' => 'Y',
            'MULTIPLE' => 'N',
            'ADDITIONAL_VALUES' => 'Y',
        ),*/
        'IBLOCK_ID' => [
            'PARENT' => 'RESET',
            'NAME' => Loc::getMessage('IBLOCK_ID'),
            'TYPE' => 'LIST',
            'VALUES' => $paramIBlocks,
            'REFRESH' => 'Y',
            'MULTIPLE' => 'N',
            'DEFAULT' => 'MODULE',
        ],
        'KEY_FIELD_NAME' => [
            'PARENT' => 'RESET',
            'NAME' => Loc::getMessage('KEY_FIELD_NAME'),
            'TYPE' => 'LIST',
            'VALUES' => $selectFields,
            'REFRESH' => 'N',
            'MULTIPLE' => 'N',
            'DEFAULT' => 'MODULE'
        ],
        'VALUE_FIELD_NAME' => [
            'PARENT' => 'RESET',
            'NAME' => Loc::getMessage('VALUE_FIELD_NAME'),
            'TYPE' => 'LIST',
            'VALUES' => $selectFieldsAndProps,
            'REFRESH' => 'N',
            'MULTIPLE' => 'N',
            'DEFAULT' => 'MODULE'
        ],
        'ADDITIONAL_CLASS' => [
            'PARENT' => 'RESET',
            'NAME' => Loc::getMessage('ADDITIONAL_CLASS'),
            'TYPE' => 'STRING',
            'DEFAULT' => Settings::get('additional_class')
        ],
        'SECTION_ID' => [
            'PARENT' => 'RESET',
            'NAME' => Loc::getMessage('SECTION_ID'),
            'TYPE' => 'LIST',
            'VALUES' => $sections,
            'REFRESH' => 'N',
            'MULTIPLE' => 'N',
            'DEFAULT' => 0
        ],
        'CACHE_TIME' => ['DEFAULT' => Settings::get('cache_time')]
    ],
];