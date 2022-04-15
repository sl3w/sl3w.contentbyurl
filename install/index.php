<?php

use ContentByUrl\Iblock;
use ContentByUrl\Settings;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class sl3w_contentbyurl extends CModule
{
    var $MODULE_ID = 'sl3w.contentbyurl';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $PARTNER_NAME;
    var $PARTNER_URI;
    var $MODULE_DIR;

    public function __construct()
    {
        if (file_exists(__DIR__ . '/version.php')) {

            $arModuleVersion = [];

            include_once(__DIR__ . '/version.php');

            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

            $this->MODULE_NAME = Loc::getMessage('CONTENT_BY_URL_MODULE_NAME');
            $this->MODULE_DESCRIPTION = Loc::getMessage('CONTENT_BY_URL_MODULE_DESC');

            $this->PARTNER_NAME = Loc::getMessage('CONTENT_BY_URL_PARTNER_NAME');
            $this->PARTNER_URI = Loc::getMessage('CONTENT_BY_URL_PARTNER_URI');

            $this->MODULE_DIR = dirname(__FILE__) . '/../';
        }
    }

    public function InstallFiles()
    {
        CopyDirFiles(__DIR__ . '/components',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components', true, true);

        return false;
    }

    public function UnInstallFiles()
    {
        DeleteDirFilesEx('/bitrix/components/sl3w/content.by.url');

        return false;
    }

    public function DoInstall()
    {
        global $APPLICATION;

        self::IncludeServiceFiles();

        RegisterModule($this->MODULE_ID);

        $this->CreateIblocks();
        $this->SetOptions();
        $this->InstallFiles();

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage('CONTENT_BY_URL_INSTALL_TITLE') . ' "' . Loc::getMessage('CONTENT_BY_URL_MODULE_NAME') . '"',
            __DIR__ . '/step.php'
        );
    }

    public function DoUninstall()
    {
        global $APPLICATION;

        self::IncludeServiceFiles();

        $this->UnInstallFiles();
        $this->DeleteIblocks();
        $this->ClearOptions();
        $this->ClearSession();

        UnRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage('CONTENT_BY_URL_UNINSTALL_TITLE') . ' "' . Loc::getMessage('CONTENT_BY_URL_MODULE_NAME') . '"',
            __DIR__ . '/unstep.php'
        );
    }

    private function CreateIblocks()
    {
        $arFieldsForType = [
            'ID' => CONTENT_BY_URL_DEFAULT_IBLOCK_TYPE,
            'SECTIONS' => 'Y',
            'IN_RSS' => 'N',
            'SORT' => 500,
            'LANG' => [
                'ru' => [
                    'NAME' => Loc::getMessage('CONTENT_BY_URL_IBLOCK_TYPE_NAME'),
                ]
            ]
        ];

        if (Iblock::AddIblockType($arFieldsForType)) {

            $arFieldsForIblock = [
                'ACTIVE' => 'Y',
                'NAME' => Loc::getMessage('CONTENT_BY_URL_IBLOCK_NAME'),
                'CODE' => CONTENT_BY_URL_DEFAULT_IBLOCK_CODE,
                'IBLOCK_TYPE_ID' => $arFieldsForType['ID'],
                'SITE_ID' => 's1',
                'GROUP_ID' => ['2' => 'R'],
                'FIELDS' => [
                    'CODE' => [
                        'IS_REQUIRED' => 'N',
                        'DEFAULT_VALUE' => [
                            'UNIQUE' => 'N',
                            'TRANSLITERATION' => 'N',
                        ]
                    ]
                ]
            ];

            if ($iblockID = Iblock::AddIblock($arFieldsForIblock)) {
                /*$arFieldsProp = [
                    'NAME' => Loc::getMessage('CONTENT_BY_URL_IBLOCK_PROP'),
                    'ACTIVE' => 'Y',
                    'SORT' => '100',
                    'MULTIPLE' => 'Y',
                    'CODE' => 'PROP',
                    'PROPERTY_TYPE' => 'S',
                    'USER_TYPE' => 'UserID',
                    'IBLOCK_ID' => $iblockID
                ];
                Iblock::AddProp($arFieldsProp);*/
            } else {
                self::ShowAdminError(Loc::getMessage('CONTENT_BY_URL_IBLOCK_NOT_INSTALLED'));
            }

        } else {
            self::ShowAdminError(Loc::getMessage('CONTENT_BY_URL_IBLOCK_TYPE_NOT_INSTALLED'));
        }
    }

    private function DeleteIblocks()
    {
        if (!Iblock::DeleteIblock(CONTENT_BY_URL_DEFAULT_IBLOCK_TYPE)) {
            self::ShowAdminError(Loc::getMessage('CONTENT_BY_URL_IBLOCK_TYPE_DELETE_ERROR'));
        }
    }

    private function SetOptions()
    {
        Settings::set('switch_on', 'Y');
        Settings::set('iblock_id', get_iblock_id_by_code(CONTENT_BY_URL_DEFAULT_IBLOCK_CODE));
        Settings::set('cache_time', CONTENT_BY_URL_DEFAULT_CACHE_TIME);
        Settings::set('key_field_name', CONTENT_BY_URL_DEFAULT_KEY_FIELD_NAME);
        Settings::set('value_field_name', CONTENT_BY_URL_DEFAULT_VALUE_FIELD_NAME);
    }

    private function ClearOptions()
    {
        Settings::deleteAll();
    }

    private function ClearSession()
    {
        unset($_SESSION[CONTENT_BY_URL_SESSION_DATA_CONTAINER]);
    }

    private static function ShowAdminError($errorText)
    {
        CAdminMessage::ShowMessage([
            'TYPE' => 'ERROR',
            'MESSAGE' => $errorText,
            'DETAILS' => '',
            'HTML' => true
        ]);
    }

    private static function IncludeServiceFiles()
    {
        include_once('service.php');
    }
}
