<?php

use \Illuminate\Container\Container;

if (! function_exists('app')) {

    /**
     * @param null $make
     * @param array $parameters
     * @return mixed|static
     */
    function app($make = null, $parameters = [])
    {
        if (is_null($make)) {
            return Container::getInstance();
        }

        return Container::getInstance()->make($make, $parameters);
    }
}


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