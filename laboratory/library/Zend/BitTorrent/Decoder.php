<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_BitTorrent
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** @see Zend_BitTorrent_Encoder */
require_once 'Zend/BitTorrent/Encoder.php';

/**
 * Decode bittorrent strings to it's PHP variable counterpart
 *
 * @category   Zend
 * @package    Zend_BitTorrent
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_BitTorrent_Decoder
{
    /**
     * Decode a file
     *
     * @param string $file
     * @param boolean $strict If set to true this method will check for certain elements in the dictionary.
     * @return array
     * @throws Zend_BitTorrent_Decoder_Exception
     * @uses Zend_BitTorrent_Decoder::decodeDictionary()
     */
    public static function decodeFile($file, $strict = false)
    {
        if (!is_readable($file)) {
            /** @see Zend_BitTorrent_Decoder_Exception */
            require_once 'Zend/BitTorrent/Decoder/Exception.php';

            throw new Zend_BitTorrent_Decoder_Exception('File ' . $file . ' does not exist or can not be read.');
        }

        $dictionary = self::decodeDictionary(file_get_contents($file, true));

        if ($strict) {
            if (!isset($dictionary['announce']) || !is_string($dictionary['announce'])) {
                /** @see Zend_BitTorrent_Decoder_Exception */
                require_once 'Zend/BitTorrent/Decoder/Exception.php';

                throw new Zend_BitTorrent_Decoder_Exception('Missing "announce" key.');
            } else if (!isset($dictionary['info']) || !is_array($dictionary['info'])) {
                /** @see Zend_BitTorrent_Decoder_Exception */
                require_once 'Zend/BitTorrent/Decoder/Exception.php';

                throw new Zend_BitTorrent_Decoder_Exception('Missing "info" key.');
            }
        }

        return $dictionary;
    }

    /**
     * Decode any bittorrent encoded string
     *
     * @param string $string
     * @return mixed
     * @uses Zend_BitTorrent_Decoder::decodeInteger()
     * @uses Zend_BitTorrent_Decoder::decodeString()
     * @uses Zend_BitTorrent_Decoder::decodeList()
     * @uses Zend_BitTorrent_Decoder::decodeDictionary()
     * @throws Zend_BitTorrent_Decoder_Exception
     */
    public static function decode($string)
    {
        if ($string[0] === 'i') {
            return self::decodeInteger($string);
        } else if ($string[0] === 'l') {
            return self::decodeList($string);
        } else if ($string[0] === 'd') {
            return self::decodeDictionary($string);
        } else if (preg_match('/^\d+:/', $string)) {
            return self::decodeString($string);
        }

        /** @see Zend_BitTorrent_Decoder_Exception */
        require_once 'Zend/BitTorrent/Decoder/Exception.php';

        throw new Zend_BitTorrent_Decoder_Exception('Parameter is not correctly encoded.');
    }

    /**
     * Decode an encoded PHP integer
     *
     * @param string $integer
     * @return int
     * @throws Zend_BitTorrent_Decoder_Exception
     */
    public static function decodeInteger($integer)
    {
        if ($integer[0] !== 'i' || (!$ePos = strpos($integer, 'e'))) {
            /** @see Zend_BitTorrent_Decoder_Exception */
            require_once 'Zend/BitTorrent/Decoder/Exception.php';

            throw new Zend_BitTorrent_Decoder_Exception('Invalid integer. Inteers must start wth "i" and end with "e".');
        }

        $int = substr($integer, 1, ($ePos - 1));
        $intLen = strlen($int);

        if (($int[0] === '0' && $intLen > 1) || ($int[0] === '-' && $int[1] === '0') || !is_numeric($int)) {
            /** @see Zend_BitTorrent_Decoder_Exception */
            require_once 'Zend/BitTorrent/Decoder/Exception.php';

            throw new Zend_BitTorrent_Decoder_Exception('Invalid integer value.');
        }

        return (int) $int;
    }

    /**
     * Decode an encoded PHP string
     *
     * @param string $string
     * @return string
     * @throws Zend_BitTorrent_Decoder_Exception
     */
    public static function decodeString($string)
    {
        $stringParts = explode(':', $string, 2);

        /* The string must have two parts */
        if (count($stringParts) !== 2) {
            /** @see Zend_BitTorrent_Decoder_Exception */
            require_once 'Zend/BitTorrent/Decoder/Exception.php';

            throw new Zend_BitTorrent_Decoder_Exception('Invalid string. Strings consist of two parts separated by ":".');
        }

        $length = (int) $stringParts[0];
        $lengthLen = strlen($length);

        if (($lengthLen + 1 + $length) > strlen($string)) {
            /** @see Zend_BitTorrent_Decoder_Exception */
            require_once 'Zend/BitTorrent/Decoder/Exception.php';

            throw new Zend_BitTorrent_Decoder_Exception('The length of the string does not match the prefix of the encoded data.');
        }

        return substr($string, ($lengthLen + 1), $length);
    }

    /**
     * Decode an encoded PHP array
     *
     * @param string $list
     * @return array
     * @throws Zend_BitTorrent_Decoder_Exception
     * @uses Zend_BitTorrent_Decoder::decode()
     * @uses Zend_BitTorrent_Encoder::encode()
     */
    public static function decodeList($list)
    {
        if ($list[0] !== 'l') {
            /** @see Zend_BitTorrent_Decoder_Exception */
            require_once 'Zend/BitTorrent/Decoder/Exception.php';

            throw new Zend_BitTorrent_Decoder_Exception('Parameter is not an encoded list.');
        }

        $ret = array();

        $length = strlen($list);
        $i = 1;

        while ($i < $length) {
            if ($list[$i] === 'e') {
                break;
            }

            $part = substr($list, $i);
            $decodedPart = self::decode($part);
            $ret[] = $decodedPart;
            $i += strlen(Zend_BitTorrent_Encoder::encode($decodedPart));
        }

        return $ret;
    }

    /**
     * Decode an encoded PHP associative array
     *
     * @param string $dictionary
     * @return array
     * @uses Zend_BitTorrent_Decoder::decodeString()
     * @uses Zend_BitTorrent_Decoder::decode()
     * @throws Zend_BitTorrent_Decoder_Exception
     */
    public static function decodeDictionary($dictionary)
    {
        if ($dictionary[0] !== 'd') {
            /** @see Zend_BitTorrent_Decoder_Exception */
            require_once 'Zend/BitTorrent/Decoder/Exception.php';

            throw new Zend_BitTorrent_Decoder_Exception('Parameter is not an encoded dictionary.');
        }

        $length = strlen($dictionary);
        $ret = array();
        $i = 1;

        while ($i < $length) {
            if ($dictionary[$i] === 'e') {
                break;
            }

            $keyPart = substr($dictionary, $i);
            $key = self::decodeString($keyPart);
            $keyPartLength = strlen(Zend_BitTorrent_Encoder::encodeString($key));

            $valuePart = substr($dictionary, ($i + $keyPartLength));
            $value = self::decode($valuePart);
            $valuePartLength = strlen(Zend_BitTorrent_Encoder::encode($value));

            $ret[$key] = $value;
            $i += ($keyPartLength + $valuePartLength);
        }

        return $ret;
    }
}