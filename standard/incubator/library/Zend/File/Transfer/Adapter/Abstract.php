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
    protected $_validators = array();

    /**
     * Intern list of messages
     *
     * @var array
     */
    protected $_messages = array();
    /**
     * Intern list of filters
     *
     * @var array
     */
    protected $_filters = array();

    /**
     * Intern list of files
     * This array looks like this:
     *     array(form => array( - Form is the name within the form or, if not set the filename
     *         name,            - Original name of this file
     *         type,            - Mime type of this file
     *         size,            - Filesize in bytes
     *         tmp_name,        - Internally temporary filename for uploaded files
     *         error,           - Error which has occured
     *         destination,     - New destination for this file
     *         validators,      - Set validator names for this file
     *         files            - Set file names for this file
     *     ))
     *
     * @var array
     */
    protected $_files = array();

    /**
     * Returns all set validators
     *
     * @param  string|array $files (Optional) Returns the validator for this files
     * @return null|array List of set validators
     */
    public function getValidators($files = null)
    {
        if ($files === null) {
            return $this->_validators;
        }

        if (is_array($files) === false) {
            $files = array($files);
        }

        foreach($files as $file) {
            if (isset($this->_files[$file]) === false) {
                throw new Zend_File_Transfer_Exception('Unknown file');
            }

            $validators += $this->_files[$file]['validators'];
        }
        $validators = array_unique($validators);

        foreach($validators as $validator) {
            $result[] = $this->_validators[$validator];
        }
        return $result;
    }

    /**
     * Sets a validator for the class, erasing all previous set
     *
     * @param  string|array $validator Validator to set
     * @param  string|array $options   Options to set for this validator
     * @param  string|array $files     Files to limit this validator to
     * @return Zend_File_Transfer_Adapter
     */
    public function setValidators($validator, $options = null, $files = null)
    {
        $this->_validators = null;
        foreach($this->_files as $file => $content) {
            $this->_files[$file]['validators'] = array();
        }
        $this->addValidators($validator, $options, $files);

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
    public function addValidators($validator, $options = null, $files = null)
    {
        if (is_array($validator) === false) {
            $validator = array($validator => $options);
        }

        require_once 'Zend/Loader.php';
        foreach ($validator as $class => $options) {
            if (Zend_Loader::isReadable("Zend/Validate/File/" . ucfirst(strtolower($class)) . ".php")) {
                $class = "Zend_Validate_File_" . ucfirst(strtolower($class));
            }

            Zend_Loader::loadClass($class);
            $this->_validators[] = new $class($options);

            if ($files === null) {
                $files = array_keys($this->_files);
            } else {
                if (is_array($files) === false) {
                    $files = array($files);
                }
            }

            foreach($files as $file) {
                $this->_files[$file]['validators'][] = $class;
            }
        }

        return $this;
    }

    /**
     * Checks if the files are valid
     *
     * @param  string|array $files Files to check
     * @return boolean True if all checks are valid
     */
    public function isValid($files = null)
    {
        $check           = $this->_getFiles($files);
        $this->_messages = array();
        foreach ($check as $file => $content) {
            $uploaderror = false;
            foreach ($content['validators'] as $valid => $class) {
                if (($this->_validators[$valid]->isValid($content['tmp_name'], $content['name']) === false) and
                    ($uploaderror === false)) {
                    $this->_messages += $this->_validators[$valid]->getMessages();
                }
                if (($valid === 'Zend_Validator_File_Upload') and (count($this->_messages) > 0)) {
                    $uploaderror = true;
                }
            }
        }

        if (count($this->_messages) > 0) {
            return false;
        }

        return true;
    }

    /**
     * Returns found validation messages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
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
     * @depriciated Will be changed to be a filter!!!
     */
    public function setDestination($destination, $files = null)
    {
        if ($files === null) {
            foreach($this->_files as $file => $content) {
                $this->_files[$file]['destination'] = $destination;
            }
        } else {
            if (is_array($files) === false) {
                $files = array($files);
            }

            foreach($files as $file) {
                $this->_files[$file]['destination'] = $destination;
            }
        }

        return $this;
    }

    /**
     * Returns found files based on internal file array and given files
     *
     * @param string|array $files (Optional) Files to return
     * @throws Zend_File_Transfer_Exception On false filename
     * @return array Found files
     */
    protected function _getFiles($files = null)
    {
        $check = null;

        if (is_string($files) === true) {
            $files = array($files);
        }

        if (is_array($files) === true) {
            foreach($files as $find) {
                $found = null;
                foreach($this->_files as $file => $content) {
                    if ($content['name'] === $find) {
                        $found = $file;
                        break;
                    }

                    if ($file === $find) {
                        $found = $file;
                        break;
                    }
                }

                if ($found === null) {
                    throw new Zend_File_Transfer_Exception("'$file' not found");
                }

                $check[$found] = $this->_files[$found];
            }
        }

        if ($files === null) {
            $check = $this->_files;
        }

        return $check;
    }

    abstract public function send($options = null);

    abstract public function receive($options = null);

    abstract public function isSent($file = null);

    abstract public function isReceived($file = null);

    abstract public function getProgress();
}
