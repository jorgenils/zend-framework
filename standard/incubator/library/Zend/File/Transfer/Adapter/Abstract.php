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
 * Abstract class for file transfers (Downloads and Uploads)
 *
 * @category  Zend
 * @package   Zend_File_Transfer
 * @copyright Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_File_Transfer_Adapter_Abstract
{
    /**
     * Intern list of validators
     *
     * @var array
     */
    protected $_validator = array();

    /**
     * Intern list of filters
     *
     * @var array
     */
    protected $_filter = array();

    /**
     * Intern list of files
     *
     * @var array
     */
    protected $_files = array();

    /**
     * Intern list of types
     *
     * @var array
     */
    protected $_types = array();

    /**
     * Returns all set validators with their options
     *
     * @param  string $validator Returns the defined validator
     * @return null|array List of set validators
     */
    public function getValidator($validator = null)
    {
        if ($validator === null) {
            return $this->_validator;
        }

        if (isset($this->_validator[$validator])) {
            return $this->_validator[$validator];
        }

        return null;
    }

    /**
     * Sets a validator for the class, erasing all previous set
     *
     * @param  string|array $validator Validator to set
     * @param  string|array $options   Options to set for this validator
     * @param  string|array $files     Files to limit this validator to
     * @return Zend_File_Transfer_Adapter
     */
    public function setValidator($validator, $options = null, $files = null)
    {
        $this->_validator = null;
        $this->addValidator($validator, $options, $files);
        return $this;
    }

    /**
     * Adds a new validator for this class, old validators will not be erased
     *
     * @param string|array $validator Type of validator to add
     * @param string|array $options   Options to set for the validator
     * @param string|array $files     Files to limit this validator to
     * @return Zend_File_Transfer_Adapter
     */
    public function addValidator($validator, $options = null, $files = null)
    {
        foreach ($validator as $class => $options) {
            switch(strtolower($class)) {
                case 'size':
                    $class = 'Zend_Validate_File_Size';
                    break;

                case 'extension':
                    $class = 'Zend_Validate_File_Extension';
                    break;

                default:
                    break;
            }

            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($class);
            $this->_validator[] = new $class($options);
        }

        return $this;
    }

    /**
     * Returns all set filters with their options
     *
     * @param string $filter Filter to return
     * @return array List of set filters
     */
    public function getFilter($filter = null)
    {
        throw new Zend_File_Transfer_Exception('Method not implemented');
    }

    /**
     * Sets a filter for this class, erasing previous set
     *
     * @param string|array $filter  Filters to set
     * @param string|array $options Options to set for the filter
     * @param string|array $files   Files to limit this filter to
     */
    public function setFilter($filter, $options = null, $files = null)
    {
        throw new Zend_File_Transfer_Exception('Method not implemented');
    }

    /**
     * Adds a new filter for this class, old filters will not be erased
     *
     * @param string|array $filter  Type of filter to add
     * @param string|array $options Options to set for the filter
     * @param string|array $files   Files to limit this filter to
     * @return Zend_File_Transfer_Adapter
     */
    public function addFilter($filter, $options = null, $files = null)
    {
        throw new Zend_File_Transfer_Exception('Method not implemented');
    }

    /**
     * Returns all set files
     *
     * @return array List of set files
     */
    public function getFile()
    {
        throw new Zend_File_Transfer_Exception('Method not implemented');
    }

    /**
     * Adds one or more files
     *
     * @param string|array $file      File to add
     * @param string|array $validator Validators to use for this file, must be set before
     * @param string|array $filter    Filters to use for this file, must be set before
     */
    public function addFile($file, $validator = null, $filter = null)
    {
        throw new Zend_File_Transfer_Exception('Method not implemented');
    }

    /**
     * Returns all set types
     *
     * @return array List of set types
     */
    public function getType()
    {
        throw new Zend_File_Transfer_Exception('Method not implemented');
    }

    /**
     * Adds one or more type of files
     *
     * @param string|array $type Type of files to add
     * @param string|array $validator Validators to use for this file, must be set before
     * @param string|array $filter    Filters to use for this file, must be set before
     */
    public function addType($type, $validator = null, $filter = null)
    {
        throw new Zend_File_Transfer_Exception('Method not implemented');
    }

    /**
     * Sets a new destination for the given files
     *
     * @param string       $destination New destination directory
     * @param string|array $files       Files to set the new destination for
     * @return Zend_File_Transfer_Abstract
     */
    public function setDestination($destination, $files = null)
    {
        if ($files === null) {
            foreach($this->_files as $file => $content) {
                $this->_files[$file]['destination'] = $destination;
            }
        }

        if (is_array($files) === false) {
            $files = array($files);
        }

        foreach($files as $file) {
            $this->_files[$file]['destination'] = $destination;
        }

        return $this;
    }

    abstract public function send($options = null);

    abstract public function receive($options = null);

    abstract public function isSent($file = null);

    abstract public function isReceived($file = null);

    abstract public function isValid($file = null);

    abstract public function getProgress();
}
