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
 * @category Zend
 * @package Zend_Locale
 * @subpackage UTF8
 * @copyright Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Zend/Locale/UTF8/Interface.php';

/**
 * @category Zend
 * @package Zend_Locale
 * @subpackage UTF8
 * @copyright Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd     New BSD License
 * @see http://www.cl.cam.ac.uk/~mgk25/ucs/examples/UTF-8-test.txt
 */
class Zend_Locale_UTF8_PHP5 implements Zend_Locale_UTF8_Interface
{
	/**
	 * Returns a string object(either PHP5 or 6)
	 *
	 * @param strin $string
	 * @return Zend_Locale_UTF8_PHP5_String
	 */
	public function string( $string )
	{
		require_once 'Zend/Locale/UTF8/PHP5/String.php';
		return new Zend_Locale_UTF8_PHP5_String( &$string );
	}
}