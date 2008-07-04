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
 * @category  Zend
 * @package   Zend_File_Transfer
 * @copyright Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: $
 */

/**
 * Base class for all protocols supporting file transfers
 *
 * @category  Zend
 * @package   Zend_File_Transfer
 * @copyright Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_File_Transfer
{
    const ERR_INI_SIZE   = 1;
    const ERR_FORM_SIZE  = 2;
    const ERR_PARTITIAL  = 3;
    const ERR_NO_FILE    = 4;
    const ERR_NO_TMP_DIR = 6;
    const ERR_CANT_WRITE = 7;
    const ERR_EXTENSION  = 8;
    const ERR_ATTACK     = 100;

    /**
     * Creates a file processing handler
     *
     * @param string $protocol Protocol to use
     */
    public function __construct($protocol = null)
    {
        require_once 'Zend/File/Transfer/Exception.php';
        throw new Zend_File_Transfer_Exception('Implementation in progress');

        switch (strtoupper($protocol)) {
            default:
                $adapter = 'Zend_File_Transfer_Adapter_Http';
                break;
        }
        
        Zend_Loader::loadClass($adapter);
        $this->_adapter = new $adapter();
        if (!$this->_adapter instanceof Zend_File_Transfer_Adapter) {
            require_once 'Zend/File/Transfer/Exception.php';
            throw new Zend_File_Transfer_Exception("Adapter " . $adapter . " does not extend Zend_File_Transfer_Adapter'");
        }

        return $this->_adapter;
    }
}
