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

/**
 * Encode encodable PHP variables to the BitTorrent counterpart
 *
 * @category   Zend
 * @package    Zend_BitTorrent
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_BitTorrent_Encoder
{
    /**
     * Encode any encodable variable
     *
     * @param mixed $var
     * @return string
     * @uses Zend_BitTorrent_Encoder::encodeInteger()
     * @uses Zend_BitTorrent_Encoder::encodeString()
     * @uses Zend_BitTorrent_Encoder::encodeList()
     * @uses Zend_BitTorrent_Encoder::encodeDictionary()
     * @throws Zend_BitTorrent_Encoder_Exception
     */
    public static function encode($var)
    {
        switch (gettype($var)) {
            case 'integer':
               return self::encodeInteger($var);
            case 'string':
                return self::encodeString($var);
            case 'array':
                $indexed = true;
                $size = count($var);

                for ($i = 0; $i < $size; $i++) {
                    if (!isset($var[$i])) {
                        return self::encodeDictionary($var);
                    }
                }

                return self::encodeList($var);
        }

        /** @see Zend_BitTorrent_Encoder_Exception */
        require_once 'Zend/BitTorrent/Encoder/Exception.php';

        throw new Zend_BitTorrent_Encoder_Exception('Variables of type ' . gettype($var) . ' can not be encoded.');
    }

    /**
     * Encode an integer
     *
     * @param int $integer
     * @return string
     * @throws Zend_BitTorrent_Encoder_Exception
     */
    public static function encodeInteger($integer)
    {
        if (!is_int($integer)) {
            /** @see Zend_BitTorrent_Encoder_Exception */
            require_once 'Zend/BitTorrent/Encoder/Exception.php';

            throw new Zend_BitTorrent_Encoder_Exception('Expected integer, got: ' . gettype($integer) . '.');
        }

        return 'i' . $integer . 'e';
    }

    /**
     * Encode a string
     *
     * @param string $string
     * @return string
     * @throws Zend_BitTorrent_Encoder_Exception
     */
    public static function encodeString($string)
    {
        if (!is_string($string)) {
            /** @see Zend_BitTorrent_Encoder_Exception */
            require_once 'Zend/BitTorrent/Encoder/Exception.php';

            throw new Zend_BitTorrent_Encoder_Exception('Expected string, got: ' . gettype($string) . '.');
        }

        return strlen($string) . ':' . $string;
    }

    /**
     * Encode a list (regular PHP array)
     *
     * @param array $list
     * @return string
     * @throws Zend_BitTorrent_Encoder_Exception
     * @uses Zend_BitTorrent_Encode::encode()
     */
    public static function encodeList($list)
    {
        if (!is_array($list)) {
            /** @see Zend_BitTorrent_Encoder_Exception */
            require_once 'Zend/BitTorrent/Encoder/Exception.php';

            throw new Zend_BitTorrent_Encoder_Exception('Expected array, got: ' . gettype($list) . '.');
        }

        $ret = 'l';

        foreach ($list as $value) {
            $ret .= self::encode($value);
        }

        return $ret . 'e';
    }

    /**
     * Encode a dictionary (associative PHP array)
     *
     * @param array $dictionary
     * @return string
     * @throws Zend_BitTorrent_Encoder_Exception
     * @uses Zend_BitTorrent_Encode::encodeString()
     * @uses Zend_BitTorrent_Encode::encode()
     */
    public static function encodeDictionary($dictionary)
    {
        if (!is_array($dictionary)) {
            /** @see Zend_BitTorrent_Encoder_Exception */
            require_once 'Zend/BitTorrent/Encoder/Exception.php';

            throw new Zend_BitTorrent_Encoder_Exception('Expected array, got: ' . gettype($dictionary) . '.');
        }

        $ret = 'd';

        foreach ($dictionary as $key => $value) {
            $ret .= self::encodeString($key) . self::encode($value);
        }

        return $ret . 'e';
    }
}