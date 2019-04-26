<?php

namespace YBL\Kernel\Eth;

use YBL\Kernel\Types\Byte;

class Key
{
    /**
     * @var Byte
     */
    private static $privateKey;

    /**
     * @var Byte
     */
    private static $publicKey;


    /**
     * create private key
     * @return string
     */
    public static function createPrivateKey()
    {
        $context = \secp256k1_context_create(SECP256K1_CONTEXT_SIGN | SECP256K1_CONTEXT_VERIFY);

        do {
            $key = \openssl_random_pseudo_bytes(32);
        } while (secp256k1_ec_seckey_verify($context, $key) == 0);

        unset($context);

        return bin2hex($key);
    }

    /**
     * @param string $privateKey
     * @throws \Exception
     */
    public static function init(string $privateKey)
    {
        self::$privateKey = Byte::initWithHex($privateKey);

        self::$publicKey = self::createPublicKey(self::$privateKey);
    }

    /**
     * @param Byte $privateKey
     * @return Byte
     * @throws \Exception
     */
    private static function createPublicKey(Byte $privateKey): Byte
    {
        $context = \secp256k1_context_create(SECP256K1_CONTEXT_SIGN | SECP256K1_CONTEXT_VERIFY);
        /** @var resource $publicKey */
        $publicKey = null;
        $result = secp256k1_ec_pubkey_create($context, $publicKey, $privateKey->getBinary());
        if ($result === 1) {
            $serialized = '';
            $fcompressed = true;
            if (1 !== secp256k1_ec_pubkey_serialize($context, $serialized, $publicKey, $fcompressed)) {
                throw new Exception('secp256k1_ec_pubkey_serialize: failed to serialize public key');
            }
            unset($publicKey, $context);
            return Byte::init($serialized);
        }
        throw new Exception('secp256k1_pubkey_create: secret key was invalid');
    }

    /**
     * @return Byte
     */
    public static function getPrivateKey(): Byte
    {
        return self::$privateKey;
    }

    /**
     * @return Byte
     */
    public static function getPublicKey(): Byte
    {
        return self::$publicKey;
    }
}