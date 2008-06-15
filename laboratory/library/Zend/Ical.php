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
 * @package    Zend_Ical
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/**
 * @see Zend_Whois_Exception
 */
require_once 'Zend/Ical/Exception.php';

/**
 * @see Zend_Ical_Parser
 */
require_once 'Zend/Ical/Parser.php';


/**
 * @category   Zend
 * @package    Zend_Ical
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Ical
{
    /**
     * The source object to get and put data.
     *
     * @var Zend_Ical_Source_Interface
     */
    protected $_source;

    /**
     * Contains all data from the source
     *
     * @var array
     */
    protected $_data;


    /**
     * Zend_Ical provides a simple interface for working with ICS files.
     *
     * Zend_Ical follows the specification of the RFC 2445:
     * http://tools.ietf.org/html/rfc445
     *
     * @param string $soruceUri
     * @throws Zend_Ical_Exception
     */
    public function __construct($sourceUri)
    {
        // Determine the source type
        if (preg_match('#^([a-z]+)://#', $sourceUri, $protocolData)) {
            switch ($protocolData[1]) {
                case 'http': case 'ftp':
                    $sourceType = $protocolData[1];

                default:
                    throw new Zend_Ical_Exception('Unknown protocol `' . $protocolData[1] . '`');
                    break;
            }
        } else {
            $sourceType = 'filesystem';
        }

        // Create the source object
        switch ($sourceType) {
            case 'http':
                /**
                 * @see Zend_Ical_Source_Http
                 */
                require_once 'Zend/Ical/Source/Http.php';

                $source = new Zend_Ical_Source_Http($sourceUri);
                break;

            case 'ftp':
                /**
                 * @see Zend_Ical_Source_Ftp
                 */
                require_once 'Zend/Ical/Source/Ftp.php';

                $source = new Zend_Ical_Source_Ftp($sourceUri);
                break;

            case 'filesystem':
                /**
                 * @see Zend_Ical_Source_Filesystem
                 */
                require_once 'Zend/Ical/Source/Filesystem.php';

                $source = new Zend_Ical_Source_Filesystem($sourceUri);
                break;

            default:
                throw new Zend_Ical_Exception('Unknown source type `' . $sourceType . '`');
                break;
        }

        $this->_source = $source;

        // Import all data from the source
        $this->_importData();
    }

    /**
     * Import data from the source
     */
    protected function _importData()
    {
        // Get the raw data from the source
        $rawData = $this->_source->getRawData();

        // Parse the data into a simple struct
        $parser      = new Zend_Ical_Parser($rawData);
        $this->_data = $parser->parse();

        print_r($this->_data);
    }
}
