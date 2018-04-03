<?php
/**
 * Created by PhpStorm.
 * User: mingwang
 * Date: 2018/3/30
 * Time: 上午10:44
 */

include 'vendor/autoload.php';

use library\YuanbenHandle;

// 初始化设置
ini_set('date.timezone','Asia/Shanghai');


$imageUrl = 'http://f.hiphotos.baidu.com/image/h%3D300/sign=54f0ba11d154564efa65e23983df9cde/80cb39dbb6fd5266cdb2ba16a718972bd4073612.jpg';
$title = '百度图片';
$category = '分类12,分类23';
$dna = YuanbenHandle::handle($imageUrl, $title, $category);
dd($dna);
