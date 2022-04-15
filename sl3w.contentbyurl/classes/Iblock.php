<?php

namespace ContentByUrl;

use CIBlock;
use CIBlockProperty;
use CIBlockType;

class Iblock
{
    public static function AddIblockType($arFieldsIBT)
    {
        check_include_module('iblock');

        $iblockType = $arFieldsIBT['ID'];

        $db_iblock_type = CIBlockType::GetList([], ['ID' => $iblockType]);

        if (!$ar_iblock_type = $db_iblock_type->Fetch()) {
            $obBlocktype = new CIBlockType;
            global_db()->StartTransaction();
            $resIBT = $obBlocktype->Add($arFieldsIBT);
            if (!$resIBT) {
                global_db()->Rollback();
                echo 'Error: ' . $obBlocktype->LAST_ERROR;
                die();
            } else {
                global_db()->Commit();
            }
        } else {
            return false;
        }

        return $iblockType;
    }

    public static function AddIblock($arFieldsIB)
    {
        check_include_module('iblock');

        $iblockCode = $arFieldsIB['CODE'];
        $iblockType = $arFieldsIB['TYPE'];

        $ib = new CIBlock;

        $resIBE = CIBlock::GetList([], ['TYPE' => $iblockType, 'CODE' => $iblockCode]);
        if ($ar_resIBE = $resIBE->Fetch()) {
            return false;
        } else {
            $ID = $ib->Add($arFieldsIB);
            $iblockID = $ID;
        }

        return $iblockID;
    }

    public static function AddProp($arFieldsProp)
    {
        check_include_module('iblock');

        $ibp = new CIBlockProperty;
        $propID = $ibp->Add($arFieldsProp);

        return $propID;
    }

    public static function DeleteIblock($iBlockCode)
    {
        check_include_module('iblock');

        global_db()->StartTransaction();
        if (!CIBlockType::Delete($iBlockCode)) {
            global_db()->Rollback();

            return false;
        }

        global_db()->Commit();

        return true;
    }

    public static function GetElementCurrentUrl($iblockId, $keyField, $keyValue, $valueField, $sectionId)
    {
        $keyField .= str_contains($keyField, 'PROPERTY') ? '_VALUE' : '';

        $filter = ['IBLOCK_ID' => $iblockId, $keyField => $keyValue, 'SECTION_ID' => $sectionId, 'ACTIVE' => 'Y', 'ACTIVE_DATE' => 'Y'];
        $select = array_merge(['ID', 'IBLOCK_ID', 'NAME', 'ACTIVE', 'ACTIVE_DATE'], [$keyField, $valueField]);

        return self::GetElement($filter, $select, ['SORT' => 'DESC', 'ID' => 'ASC']);
    }

    public static function GetElement($filter = [], $select = [], $sort = [])
    {
        check_include_module('iblock');

        return \CIBlockElement::GetList($sort, $filter, false, false, $select)->Fetch();
    }
}