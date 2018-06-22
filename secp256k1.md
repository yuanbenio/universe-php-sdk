# Secp256k1-PHP

### 功能
* PHP实现secp256k1算法

### 安装

> 本包依赖于secp256k1库,安装secp256k1库请参考[secp256k1-php](https://github.com/Bit-Wasp/secp256k1-php)进行安装

* git 安装
> git clone https://github.com/xiongchao123/secp256k1-php.git & composer install

* composer 安装
> composer require xiongchao/secp256k1-php
    
### 如何使用
```php

require_once __DIR__."/vendor/autoload.php";

use Xiongchao\Secp256k1\Secp256k1;

$secp256k=app(Secp256k1::class);

$priKey=$secp256k->generatePrivateKey();

$pubKey=$secp256k->generatePublicKey($priKey);

$content="This is a message!";

$contentHash=$secp256k->getContentHash($content);

$msg32 = $secp256k->_toBinary32($contentHash);

$rSign=$secp256k->getRecoverableSign($priKey,$msg32);

$sign=$secp256k->getSign($priKey,$secp256k->_toBinary32($contentHash));

var_dump($secp256k->verifyRecoverableSign($pubKey,$rSign,$msg32));

var_dump($secp256k->verifySign($pubKey,$sign,$secp256k->_toBinary32($contentHash)));

```


