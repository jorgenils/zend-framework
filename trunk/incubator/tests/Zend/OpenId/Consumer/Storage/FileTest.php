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
 * @package    Zend_OpenId
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id:$
 */

/**
 * Zend_OpenId
 */
require_once 'Zend/OpenId/Consumer/Storage/File.php';


/**
 * PHPUnit test case
 */
require_once 'PHPUnit/Framework.php';


/**
 * @package    Zend_OpenId
 * @subpackage UnitTests
 */
class Zend_OpenId_Consumer_Storage_FileTest extends PHPUnit_Framework_TestCase
{
    const URL      = "http://www.myopenid.com/";
    const HANDLE   = "d41d8cd98f00b204e9800998ecf8427e";
    const MAC_FUNC = "sha256";
    const SECRET   = "4fa03202081808bd19f92b667a291873";

    const ID       = "http://id.myopenid.com/";
    const REAL_ID  = "http://real_id.myopenid.com/";
    const SERVER   = "http://www.myopenid.com/";
    const VERSION  = 1.0;

    /**
     * testing getAssociation
     *
     */
    public function testGetAssociation()
    {
        $expiresIn = time() + 600;
        $storage = new Zend_OpenId_Consumer_Storage_File();
        $storage->delAssociation(self::URL);
        $this->assertTrue( $storage->addAssociation(self::URL, self::HANDLE, self::MAC_FUNC, self::SECRET, $expiresIn) );
        $this->assertTrue( $storage->getAssociation(self::URL, $handle, $macFunc, $secret, $expires) );
        $this->assertSame( self::HANDLE, $handle );
        $this->assertSame( self::MAC_FUNC, $macFunc );
        $this->assertSame( self::SECRET, $secret );
        $this->assertSame( $expiresIn, $expires );
        $this->assertTrue( $storage->delAssociation(self::URL) );
        $this->assertFalse( $storage->getAssociation(self::URL, $handle, $macFunc, $secret, $expires) );
    }

    /**
     * testing getAssociationByHandle
     *
     */
    public function testGetAssociationByHandle()
    {
        $expiresIn = time() + 600;
        $storage = new Zend_OpenId_Consumer_Storage_File();
        $storage->delAssociation(self::URL);
        $this->assertTrue( $storage->addAssociation(self::URL, self::HANDLE, self::MAC_FUNC, self::SECRET, $expiresIn) );
        $this->assertTrue( $storage->getAssociationByHandle(self::HANDLE, $url, $macFunc, $secret, $expires) );
        $this->assertSame( self::URL, $url );
        $this->assertSame( self::MAC_FUNC, $macFunc );
        $this->assertSame( self::SECRET, $secret );
        $this->assertSame( $expiresIn, $expires );
        $this->assertTrue( $storage->delAssociation(self::URL) );
        $this->assertFalse( $storage->getAssociationByHandle(self::HANDLE, $url, $macFunc, $secret, $expires) );
    }

    /**
     * testing getAssociation
     *
     */
    public function testGetAssociationExpiratin()
    {
        $expiresIn = time() + 1;
        $storage = new Zend_OpenId_Consumer_Storage_File();
        $storage->delAssociation(self::URL);
        $this->assertTrue( $storage->addAssociation(self::URL, self::HANDLE, self::MAC_FUNC, self::SECRET, $expiresIn) );
        sleep(2);
        $this->assertFalse( $storage->getAssociation(self::URL, $handle, $macFunc, $secret, $expires) );
    }

    /**
     * testing getAssociationByHandle
     *
     */
    public function testGetAssociationByHandleExpiration()
    {
        $expiresIn = time() + 1;
        $storage = new Zend_OpenId_Consumer_Storage_File();
        $storage->delAssociation(self::URL);
        $this->assertTrue( $storage->addAssociation(self::URL, self::HANDLE, self::MAC_FUNC, self::SECRET, $expiresIn) );
        sleep(2);
        $this->assertFalse( $storage->getAssociationByHandle(self::HANDLE, $url, $macFunc, $secret, $expires) );
    }

    /**
     * testing getDiscoveryInfo
     *
     */
    public function testGetDiscoveryInfo()
    {
        $expiresIn = time() + 600;
        $storage = new Zend_OpenId_Consumer_Storage_File();
        $storage->delDiscoveryInfo(self::ID);
        $this->assertTrue( $storage->addDiscoveryInfo(self::ID, self::REAL_ID, self::SERVER, self::VERSION, $expiresIn) );
        $this->assertTrue( $storage->getDiscoveryInfo(self::ID, $realId, $server, $version, $expires) );
        $this->assertSame( self::REAL_ID, $realId );
        $this->assertSame( self::SERVER, $server );
        $this->assertSame( self::VERSION, $version );
        $this->assertSame( $expiresIn, $expires );
        $this->assertTrue( $storage->delDiscoveryInfo(self::ID) );
        $this->assertFalse( $storage->getDiscoveryInfo(self::ID, $realId, $server, $version, $expires) );
    }

    /**
     * testing getDiscoveryInfo
     *
     */
    public function testGetDiscoveryInfoExpiration()
    {
        $expiresIn = time() + 1;
        $storage = new Zend_OpenId_Consumer_Storage_File();
        $storage->delDiscoveryInfo(self::ID);
        $this->assertTrue( $storage->addDiscoveryInfo(self::ID, self::REAL_ID, self::SERVER, self::VERSION, $expiresIn) );
        sleep(2);
        $this->assertFalse( $storage->getDiscoveryInfo(self::ID, $realId, $server, $version, $expires) );
    }
}
