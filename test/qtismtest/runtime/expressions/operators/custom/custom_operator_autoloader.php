<?php

/**
 * @param $class
 */
function custom_operator_autoloader($class)
{
    $class = str_replace("\\", DIRECTORY_SEPARATOR, $class);
    $path = __DIR__ . DIRECTORY_SEPARATOR . $class . '.php';

    if (file_exists($path) === true) {
        require_once($path);
    }
}
