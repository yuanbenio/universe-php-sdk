<?php

if (!function_exists('dd')) {
    /**
     * @param mixed ...$args
     */
    function dd(...$args)
    {
        foreach ($args as $v) {
            var_dump($v);
        }
        die;
    }
}

if (!function_exists('logger')) {
    /**
     * @param mixed ...$args
     */
    function logger(...$args)
    {
        foreach ($args as $v) {
            print_r($v);
            echo "\n";
        }
    }
}