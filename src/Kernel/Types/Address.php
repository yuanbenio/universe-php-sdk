<?php

namespace YBL\Kernel\Types;

use LengthException;
use Exception;
use YBL\Kernel\Support\Utils;

class Address extends TypeAbstract
{
    /**
     * @param string $address
     * @return Address
     * @throws Exception
     */
    public static function init($address = ''): Address
    {
        if (strlen($address) === 0) {
            $buffer = new Buffer;
        } else {
            $address = Utils::removeHexPrefix($address);
            if (strlen($address) !== 40) {
                throw new LengthException($address.' is invalid.');
            }
            $buffer = Buffer::hex($address);
        }

        return new static($buffer);
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return Utils::ensureHexPrefix($this->getHex());
    }
}
