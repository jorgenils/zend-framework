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

/** @see Zend_BitTorrent_Decoder */
require_once 'Zend/BitTorrent/Decoder.php';

/**
 * Convenient class that uses the Zend_BitTorrent_Encoder and Zend_BitTorrent_Decoder classes.
 *
 * @category   Zend
 * @package    Zend_BitTorrent
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_BitTorrent 
{
    /**
     * Encode a variable
     *
     * @param mixed $var
     * @return string
     * @uses Zend_BitTorrent_Encoder::encode()
     */
    public static function encode($var) 
    {
        return Zend_BitTorrent_Encoder::encode($var);
    }

    /**
     * Decode a string
     *
     * @param string $var
     * @param boolean $isFilePath If $var is a path to a file, set this to true to decode the file.
     * @return integer|string|array
     * @uses Zend_BitTorrent_Decoder::decodeFile()
     * @uses Zend_BitTorrent_Decoder::decode()
     */
    public static function decode($var, $isFilePath = false) 
    {
        if ($isFilePath) {
            return Zend_BitTorrent_Decoder::decodeFile($var);
        }

        return Zend_BitTorrent_Decoder::decode($var);
    }
}