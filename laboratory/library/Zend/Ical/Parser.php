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
 * @package   Zend_Ical
 * @copyright Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: $
 */

/**
 * @see Zend_Ical_Exception
 */
require_once 'Zend/Ical/Exception.php';

/**
 * This parser will take raw data from a source and put it into an array
 *
 * @category  Zend
 * @package   Zend_Ical
 * @copyright Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Ical_Parser
{
    /**
     * Raw ICS data
     *
     * @var string
     */
    protected $_rawData;

    /**
     * Length of the raw data
     *
     * @var integer
     */
    protected $_rawDataLength;

    /**
     * Current position of the parser
     *
     * @var integer
     */
    protected $_currentPos = 0;

    /**
     * List of valid node names
     *
     * @var array
     */
    protected $_validNodeNames = array('DAYLIGHT', 'STANDARD', 'VALARM',
                                       'VCALENDAR', 'VEVENT', 'VFREEBUSY',
                                       'VJOURNAL', 'VTIMEZONE', 'VTODO');

    /**
     * Takes the raw data and makes some comaptibility magic on it
     *
     * @param string  $rawData   The raw (partial) ICS data
     * @param boolean $cleanData Wether to clean the data or not; this should
     *                           only be done once on the entire data
     */
    public function __construct($rawData, $cleanData = true)
    {
        // Clean the data only if required
        if ($cleanData === true) {
            // Convert all lonely \rs and \ns to \r\n
            $rawData = preg_replace("#(?:\r([^\n])|([^\r])\n)#", "\\2\r\n\\1", $rawData);

            // Unfold the raw data
            $rawData = str_replace(array("\r\n ", "\r\n\t"), '', $rawData);
        }

        // Finally set them internally
        $this->_rawData       = $rawData;
        $this->_rawDataLength = strlen($rawData);
    }

    /**
     * Parse the given data and return an array with all nodes and properties
     * of this node.
     *
     * @return array
     */
    public function parse()
    {
        $rootNode = $this->_parseNode();

        return $rootNode['nodes'];
    }

    /**
     * Parse a node until it's end. If $nodeName is null, the document will be
     * parsed until it's end.
     *
     * @param  string $currentNodeName Name of the node which this run is parsing
     * @throws Zend_Ical_Exception When there is a lonely \r
     * @throws Zend_Ical_Exception When there is an unknown node
     * @throws Zend_Ical_Exception When an unexpected END: occurs
     * @return array
     */
    protected function _parseNode($currentNodeName = null)
    {
        $nodes            = array();
        $properties       = array();
        $token            = '';
        $tokenIsNodeBegin = false;
        $tokenIsNodeEnd   = false;

        // Loop through all characters
        while ($this->_rawDataLength > $this->_currentPos) {
            // Get the current character, but treat "\r\n" as a single character
            $char = $this->_rawData[$this->_currentPos++];

            if ($char === "\r") {
                if ($this->_rawData[$this->_currentPos++] === "\n") {
                    $char .= "\n";
                } else {
                    throw new Zend_Ical_Exception('Expected \n after \r, but not found');
                }
            }

            // Add the character to the token
            $token .= $char;

            // Check the token
            if ($tokenIsNodeBegin === true && substr($token, -2) === "\r\n") {
                // This is the full node name, so we can now start to parse it
                $nodeName = strtoupper(substr($token, 0, -2));

                // Validate the node name
                if (in_array($nodeName, $this->_validNodeNames) === true) {
                    $node = array('name' => $nodeName);
                } else {
                    throw new Zend_Ical_Exception('Unknown node `' . $nodeName . '`');
                }

                // If everything was fine, parse the node
                $nodes[] = array_merge($node, $this->_parseNode($nodeName));

                $tokenIsNodeBegin = false;
                $token            = '';
            } else if ($tokenIsNodeEnd === true && substr($token, -2) === "\r\n") {
                // This is the full node name, so we can check if it is the end
                $nodeName = strtoupper(substr($token, 0, -2));

                if ($nodeName !== $currentNodeName) {
                    throw new Zend_Ical_Exception('Expected `END:' . $currentNodeName
                                                  . '`, but `END:' . $nodeName . '` found');
                }

                // Node is finished, break out and return it
                break;
            } else if ($token === 'BEGIN:') {
                // This is a new node, tell the next round that it should treat
                // it like that.
                $tokenIsNodeBegin = true;
                $token            = '';
            } else if ($token === 'END:') {
                // It seems like the current node would end yet, let's check it
                $tokenIsNodeEnd = true;
                $token          = '';
            } else if (substr($token, -2) === "\r\n") {
                // This could be a property, check it
                if (trim($token) !== '') {
                    $colonPos     = strpos($token, ':');
                    $semicolonPos = strpos($token, ';');

                    if ($semicolonPos !== false && $semicolonPos < $colonPos) {
                        $propertyName = substr($token, 0, $semicolonPos);
                        $parameters   = substr($token, ($semicolonPos + 1), ($colonPos - $semicolonPos - 1));
                        $value        = substr($token, ($colonPos + 1), -2);
                    } else {
                        $propertyName = substr($token, 0, $colonPos);
                        $parameters   = null;
                        $value        = substr($token, ($colonPos + 1), -2);
                    }

                    $properties[] = array('name'       => $propertyName,
                                          'parameters' => $parameters,
                                          'value'      => $value);
                }

                // Reset the token
                $token = '';
            }
        }

        return array('nodes'      => $nodes,
                     'properties' => $properties);
    }
}