<?php
/**
 * Created by PhpStorm.
 * User: mingwang
 * Date: 2018/3/30
 * Time: 上午11:24
 */


if (!function_exists('dd')) {
    /**
     * 调试函数
     * @param array ...$args
     * @author mingwang
     * @date 2018.3.30
     */
    function dd(...$args)
    {
        foreach ($args as $v) {
            var_dump($v);
        }
        die;
    }
}

if (!function_exists('now')) {
    /**
     * 获取当前时间
     * @author mingwang
     * @date 2018.3.30
     */
    function now()
    {
        return date('Y-m-d H:i:s', time());
    }
}

if (!function_exists('log_error')) {
    /**
     * 记录错误日志
     * @param $message
     * @param int $code
     * @param array $context
     * @param null $file
     * @param null $line
     * @author mingwang
     * @date 2018.1.23
     */
    function log_error($message, array $context = [], $code = 500, $file = null, $line = null)
    {
        // 获取调用文件 和 行数
        $debugTrace = debug_backtrace();
        $file = $file ?? $debugTrace[0]['file'];
        $line = $line ?? $debugTrace[0]['line'];
        $datetime = now();

        // 日志格式
        $logStr = "$datetime; [级别]: ERROR; [信息]: $message; [文件]: $file; [行数]: $line; [错误码]: $code; [上下文参数]: " . json_encode($context, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        echo $logStr . PHP_EOL;
    }
}

if (!function_exists('log_info')) {
    /**
     * 记录Info日志
     * @param $sign "业务标志"
     * @param $message "记录信息"
     * @param array $context "上下文信息"
     */
    function log_info($sign, $message, array $context = [], $file = null, $line = null)
    {
        // 获取调用文件 和 行数
        $debugTrace = debug_backtrace();
        $file = $file ?? $debugTrace[0]['file'];
        $line = $line ?? $debugTrace[0]['line'];
        $datetime = now();

        // 日志格式
        $logStr = "$datetime; [级别]: INFO; [标志]: $sign; [信息]: $message; [文件]: $file; [行数]: $line; [上下文参数]: " . json_encode($context, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        echo $logStr . PHP_EOL;
    }
}

if (!function_exists('log_warning')) {
    /**
     * 记录Warning日志
     * @param $sign 业务标志
     * @param $message 记录信息
     * @param array $context 上下文信息
     */
    function log_warning($sign, $message, array $context = [], $file = null, $line = null)
    {
        // 获取调用文件 和 行数
        $debugTrace = debug_backtrace();
        $file = $file ?? $debugTrace[0]['file'];
        $line = $line ?? $debugTrace[0]['line'];
        $datetime = now();

        // 日志格式
        $logStr = "$datetime; [级别]: WARNING; [标志]: $sign; [信息]: $message; [文件]: $file; [行数]: $line; [上下文参数]: " . json_encode($context, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        echo $logStr . PHP_EOL;
    }
}


if (!function_exists('str_random')) {
    /**
     * 随机字符串
     * @param int $length
     * @author mingwang
     * @date 2018.3.30
     * @return string
     */
    function str_random($length = 16)
    {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytes = random_bytes($size);

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }
}