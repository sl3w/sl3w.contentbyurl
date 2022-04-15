<?php

use ContentByUrl\Iblock;
use ContentByUrl\Settings;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

if (!\Bitrix\Main\Loader::includeModule('sl3w.contentbyurl')) {
    ShowError(Loc::getMessage('CONTENT_BY_URL_MODULE_NOT_INSTALLED'));
    return;
}

class ContentByUrlComponent extends \CBitrixComponent
{
    private $cacheId;

    public function onPrepareComponentParams($params)
    {
        $params['CURRENT_PAGE'] = global_application()->GetCurPage();

        $this->cacheId = 'content_by_url_' . md5(json_encode($params));

        return $params;
    }

    public function getElement()
    {
        $iblockId = intval($this->arParams['IBLOCK_ID']) ?: (Settings::get('iblock_id') ?:
            get_iblock_id_by_code(CONTENT_BY_URL_DEFAULT_IBLOCK_CODE));

        if (!intval($iblockId)) {
            return false;
        }

        $this->arResult['IBLOCK_ID'] = $iblockId;

        $this->arResult['ADDITIONAL_CLASS'] = $this->arParams['ADDITIONAL_CLASS'] ?: Settings::get('additional_class');

        $keyFieldName = to_upper($this->paramOrFromModule('KEY_FIELD_NAME'));

        $valueFieldName = to_upper($this->paramOrFromModule('VALUE_FIELD_NAME'));

        $sectionId = intval($this->arParams['SECTION_ID']) && Settings::get('section_separation') == 'Y' ? $this->arParams['SECTION_ID'] : '';

        $element = Iblock::GetElementCurrentUrl($iblockId, $keyFieldName, $this->arParams['CURRENT_PAGE'], $valueFieldName, $sectionId);

        if (!$element) {
            return false;
        }

        $valueFieldName .= str_contains($valueFieldName, 'PROPERTY') ?
            (is_array($element[$valueFieldName . '_VALUE']) ? '_VALUE.TEXT' : '_VALUE') : '';

        //$this->arResult['ELEMENT'] = $element;
        $this->arResult['TEXT'] = array_get($element, $valueFieldName);
    }

    public function paramOrFromModule($paramName, $inModName = '')
    {
        return $this->arParams[$paramName] && $this->arParams[$paramName] != 'MODULE' ?
            $this->arParams[$paramName] : Settings::get($inModName ?: to_lower($paramName));
    }

    public function cacheTime()
    {
        return $this->arParams['CACHE_TIME'] ?: CONTENT_BY_URL_DEFAULT_CACHE_TIME;
    }

    public function executeComponent()
    {
        if (Settings::get('switch_on') != 'Y') {
            return false;
        }

        $cache = new CPHPCache();
        $cache->InitCache($this->cacheTime(), $this->cacheId);
        $this->arResult = $cache->GetVars();

        if (!$this->arResult) {
            $this->getElement();

            $cache->StartDataCache($this->cacheTime(), $this->cacheId);
            $cache->EndDataCache($this->arResult);
        }

        if ($this->arResult) {
            $this->includeComponentTemplate();
        }
    }
}