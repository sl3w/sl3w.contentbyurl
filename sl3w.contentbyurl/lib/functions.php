<?php

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Loader;

if (!function_exists('get_iblock_id_by_code')) {
    function get_iblock_id_by_code($code)
    {
        $iblocksId = session_get('iblock_id');

        if (array_key_exists($code, $iblocksId)) {
            return $iblocksId[$code];
        }

        check_include_module('iblock');

        $iBlock = IblockTable::getRow([
            'filter' => ['=CODE' => $code],
            'select' => ['ID'],
        ]);

        $iblockId = $iBlock['ID'];

        $iblocksId[$code] = $iblockId;
        session_set('iblock_id', $iblocksId);

        return $iblockId;
    }
}

if (!function_exists('check_include_module')) {
    function check_include_module($modules, $throwable = true)
    {
        if (is_string($modules)) {
            $modules = [$modules];
        }

        if (!is_array($modules)) {
            return [];
        }

        $errors = [];
        foreach ($modules as $module) {
            if (!Loader::includeModule($module)) {
                $errors[] = sprintf('Ошибка при подключении модуля "%s"', $module);
            }
        }

        if (!empty($errors) && $throwable) {
            throw new Exception(implode(', ', $errors));
        }

        return $errors;
    }
}

if (!function_exists('global_db')) {
    function global_db()
    {
        global $DB;

        return $DB;
    }
}

if (!function_exists('global_application')) {
    function global_application()
    {
        global $APPLICATION;

        return $APPLICATION;
    }
}

if (!function_exists('session_get')) {
    function session_get($code)
    {
        return $_SESSION[CONTENT_BY_URL_SESSION_DATA_CONTAINER][$code];
    }
}

if (!function_exists('session_set')) {
    function session_set($code, $value)
    {
        if (!array_key_exists(CONTENT_BY_URL_SESSION_DATA_CONTAINER, $_SESSION)) {
            $_SESSION[CONTENT_BY_URL_SESSION_DATA_CONTAINER] = [];
        }

        $_SESSION[CONTENT_BY_URL_SESSION_DATA_CONTAINER][$code] = $value;
    }
}

if (!function_exists('to_upper')) {
    function to_upper($string)
    {
        $function = (function_exists('mb_strtoupper')) ? 'mb_strtoupper' : 'strtoupper';

        return call_user_func($function, $string);
    }
}

if (!function_exists('to_lower')) {
    function to_lower($string)
    {
        $function = (function_exists('mb_strtolower')) ? 'mb_strtolower' : 'strtolower';

        return call_user_func($function, $string);
    }
}

if (!function_exists('array_get')) {
    /**
     * Получает элемент массива по ключу.
     * Имеется возможность использовать dot-нотацию (т.е. по ключу `foo.bar` из массива `['foo' => ['bar' => 100]]` достанется 100)
     *
     * @param array $array
     * @param string $key
     * @param mixed|null $default
     * @return mixed|null
     */
    function array_get($array, $key, $default = null)
    {
        if (!is_array($array)) {
            return $default;
        }

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        $key = explode('.', $key);
        $innerKey = array_shift($key);

        if (array_key_exists($innerKey, $array)) {
            $array = $array[$innerKey];
        } else {
            return $default;
        }

        return array_get($array, implode('.', $key));
    }
}

if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle)
    {
        return stripos($haystack, $needle) !== false;
    }
}