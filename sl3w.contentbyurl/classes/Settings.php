<?php

namespace ContentByUrl;

use Bitrix\Main\Config\Option;

class Settings
{
    public static function get($name, $default = '')
    {
        return Option::get('sl3w.contentbyurl', $name, $default);
    }

    public static function set($name, $value)
    {
        Option::set('sl3w.contentbyurl', $name, $value);
    }

    public static function deleteAll()
    {
        Option::delete('sl3w.contentbyurl');
    }
}
