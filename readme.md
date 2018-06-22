# 原本链SDK for PHP

### 功能
* 使用php接入原本链
> 原本链API文档 [http://yuanbenlian.mydoc.io/docs/api.md?t=268053](http://yuanbenlian.mydoc.io/docs/api.md?t=268053)

### 安装

> 请在Linux系统下运行

> PHP Version >= 7.0

> 本包依赖于secp256k1库,安装secp256k1库请参考[secp256k1-php](https://github.com/Bit-Wasp/secp256k1-php)进行安装

* git 安装
> git clone https://github.com/yuanbenio/universe-php-sdk.git & composer install

* composer 安装
> composer require yuanben/universe-php-sdk
   
### SDK使用

#### 1.请求上链

* 以接入图片类型示例
```php
require_once __DIR__ . "/vendor/autoload.php";

$src = __DIR__ . "/images/user1.png";

$handle = app(\YuanBen\YuanBenLian\BlockHandle::class);
$request = app(\YuanBen\YuanBenLian\BlockRequest::class);
$privateKey = $handle->generatePrivateKey();
$contentHash = $handle->getContentHash(file_get_contents($src));
$data = $request->getBlockMsg();
if (!$data) {
    // TODO
    logger("获取最新区块高度失败");
}
// 拼接 metadata
$blockHash = $data['latest_block_hash'];
$blockHeight = (string)$data['latest_block_height'];  // 原本链要求所有metadata的内容均为string类型
$metadataArr = [
    'language' => 'zh-CN',
    'type' => 'image',
    'title' => 'user',
    'category' => 'test',
    // 特殊字段
    'created' => (string)time(),
    'content_hash' => $contentHash,
    // 类型数据(可没有)  具体数据参考 http://yuanbenlian.mydoc.io/docs/api.md?t=268053
    /* 'data'=>[
        "thumb"=>"",
     ],*/
    'license' => [
        'type' => 'cc',
        'parameters' => [
            "adaptation" => "y",
            "commercial" => "y"
        ],
    ],
    'block_hash' => $blockHash,
    'block_height' => (string)$blockHeight,
];
// 补全metadata 公钥 & ID,也可自行拼接
$metadataArr = $handle->supplyMetadataArr($privateKey, $metadataArr);
// 对metadata进行签名时需先按照键值排序在进行签名,案例已封装于getSign方法中
$sign = $handle->getSign($privateKey, $metadataArr);
$metadataArr['signature'] = $sign;
$dna = $handle->generateDNA($sign);
$metadataArr['dna'] = $dna;
// TODO 可自定义方法对必须字段进行校验
$createMetadataRes = $request->_createMetadata(json_encode($metadataArr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
logger($createMetadataRes);

```    
#### 根据dna信息查询元数据

```php
$request = app(\YuanBen\YuanBenLian\BlockRequest::class);
$metadata=$request->searchMetaData($dna);
```


