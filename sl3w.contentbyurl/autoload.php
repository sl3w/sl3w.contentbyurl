<?php

use Bitrix\Main\Loader;

$prefix = '\ContentByUrl';
$parent = 'classes/';
$module = 'sl3w.contentbyurl';

global $dir;
$dir = __DIR__;

if (!function_exists('getDirectoryChildren')) {
    function getDirectoryChildren($directory, $parentDirectory)
    {
        global $dir;

        $fullParentPath = sprintf('%s/%s/%s/', $dir, $parentDirectory, $directory);

        $result = [];
        foreach (scandir($fullParentPath) as $child) {
            if ($child == '.' || $child == '..') {
                continue;
            }

            $fullChildPath = sprintf('%s/%s', $fullParentPath, $child);
            $childPath = str_replace('//', '/', sprintf('%s/%s', $directory, $child));

            if (is_dir($fullChildPath)) {
                $result = array_merge($result, getDirectoryChildren($childPath, $parentDirectory));
            } else {
                $result[] = $childPath;
            }
        }

        return $result;
    }
}

$formattedClassFiles = [];
foreach (getDirectoryChildren('', $parent) as $classFile) {
    $class = $prefix . str_replace('/', '\\', $classFile);
    $class = mb_substr($class, 0, mb_strpos($class, '.'));

    $formattedClassFiles[$class] = str_replace('//', '/', $parent . $classFile);
}

Loader::registerAutoLoadClasses($module, $formattedClassFiles);