<?php
/**
 * Primitus
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 *
 * @category   Primitus
 * @package    Application
 * @copyright  Copyright (c) 2006 John Coggeshall
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Zend/Filter.php';
require_once 'Primitus/Exception.php';

/**
 * The Primitus implementation of Zend_Filter_Input. The current Zend version doesn't
 * let you do some obvious things (i.e. the source has to be an array rather then a
 * scalar value). This fixes those limitations
 * 
 * @category   Primitus
 * @package    Primitus
 * @subpackage View
 * @copyright  Copyright (c) 2006 John Coggeshall
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Primitus_Filter_Input
{
	protected $_source;
	protected $_isSingle;
	
	public function __construct(&$source = null, $strict = true) {
		switch(true) {
			case is_array($source):
				$this->_source = $source;
				$this->_isSingle = false;
				break;
			case is_scalar($source):
				$this->_source = $source;
				$this->_isSingle = true;
				break;
			default:
				throw new Primitus_Exception("You can only place scalars or arrays in this structure");
		}

		if($strict) {
			$source = null;
		}
	}
	
    /**
     * Returns only the alphabetic characters in value.
     *
     * @param mixed $key
     * @return mixed
     */
    public function getAlpha($key=null)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        return Zend_Filter::getAlpha($value);
    }


    /**
     * Returns only the alphabetic characters and digits in value.
     *
     * @param mixed $key
     * @return mixed
     */
    public function getAlnum($key=null)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        return Zend_Filter::getAlnum($value);
    }


    /**
     * Returns only the digits in value. This differs from getInt().
     *
     * @param mixed $key
     * @return mixed
     */
    public function getDigits($key=null)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        return Zend_Filter::getDigits($value);
    }


    /**
     * Returns dirname(value).
     *
     * @param mixed $key
     * @return mixed
     */
    public function getDir($key=null)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        return Zend_Filter::getDir($value);
    }


    /**
     * Returns (int) value.
     *
     * @param mixed $key
     * @return int
     */
    public function getInt($key=null)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        return Zend_Filter::getInt($value);
    }


    /**
     * Returns realpath(value).
     *
     * @param mixed $key
     * @return mixed
     */
    public function getPath($key=null)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        return Zend_Filter::getPath($value);
    }


    /**
     * Returns value.
     *
     * @param string $key
     * @return mixed
     */
    public function getRaw($key=null)
    {
    	$value = $this->keyExists($key);
        if (!($value = $this->keyExists($key))) {
            return false;
        }

        return $value;
    }


    /**
     * Returns value if every character is alphabetic or a digit,
     * FALSE otherwise.
     *
     * @param mixed $key
     * @return mixed
     */
    public function testAlnum($key=null)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        if (Zend_Filter::isAlnum($value)) {
            return $value;
        }

        return FALSE;
    }


    /**
     * Returns value if every character is alphabetic, FALSE
     * otherwise.
     *
     * @param mixed $key
     * @return mixed
     */
    public function testAlpha($key=null)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        if (Zend_Filter::isAlpha($value)) {
            return $value;
        }

        return FALSE;
    }


    /**
     * Returns value if it is greater than or equal to $min and less
     * than or equal to $max, FALSE otherwise. If $inc is set to
     * FALSE, then the value must be strictly greater than $min and
     * strictly less than $max.
     *
     * @param mixed $key
     * @param mixed $min
     * @param mixed $max
     * @param boolean $inclusive
     * @return mixed
     */
    public function testBetween($key=null, $min, $max, $inc = TRUE)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        if (Zend_Filter::isBetween($value, $min, $max, $inc)) {
            return $value;
        }

        return FALSE;
    }


    /**
     * Returns value if it is a valid credit card number format. The
     * optional second argument allows developers to indicate the
     * type.
     *
     * @param mixed $key
     * @param mixed $type
     * @return mixed
     */
    public function testCcnum($key=null, $type = NULL)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        if (Zend_Filter::isCcnum($value, $type)) {
            return $value;
        }

        return FALSE;
    }


    /**
     * Returns $value if it is a valid date, FALSE otherwise. The
     * date is required to be in ISO 8601 format.
     *
     * @param mixed $key
     * @return mixed
     */
    public function testDate($key=null)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        if (Zend_Filter::isDate($value)) {
            return $value;
        }

        return FALSE;
    }


    /**
     * Returns value if every character is a digit, FALSE otherwise.
     * This is just like isInt(), except there is no upper limit.
     *
     * @param mixed $key
     * @return mixed
     */
    public function testDigits($key=null)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        if (Zend_Filter::isDigits($value)) {
            return $value;
        }

        return FALSE;
    }


    /**
     * Returns value if it is a valid email format, FALSE otherwise.
     *
     * @param mixed $key
     * @return mixed
     */
    public function testEmail($key=null)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        if (Zend_Filter::isEmail($value)) {
            return $value;
        }

        return FALSE;
    }


    /**
     * Returns value if it is a valid float value, FALSE otherwise.
     *
     * @param mixed $key
     * @return mixed
     */
    public function testFloat($key=null)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        if (Zend_Filter::isFloat($value)) {
            return $value;
        }

        return FALSE;
    }


    /**
     * Returns value if it is greater than $min, FALSE otherwise.
     *
     * @param mixed $key
     * @param mixed $min
     * @return mixed
     */
    public function testGreaterThan($key=null, $min = NULL)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        if (Zend_Filter::isGreaterThan($value, $min)) {
            return $value;
        }

        return FALSE;
    }


    /**
     * Returns value if it is a valid hexadecimal format, FALSE
     * otherwise.
     *
     * @param mixed $key
     * @return mixed
     */
    public function testHex($key=null)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        if (Zend_Filter::isHex($value)) {
            return $value;
        }

        return FALSE;
    }


    /**
     * Returns value if it is a valid hostname, FALSE otherwise.
     * Depending upon the value of $allow, Internet domain names, IP
     * addresses, and/or local network names are considered valid.
     * The default is HOST_ALLOW_ALL, which considers all of the
     * above to be valid.
     *
     * @param mixed $key
     * @param integer $allow bitfield for HOST_ALLOW_DNS, HOST_ALLOW_IP, HOST_ALLOW_LOCAL
     * @return mixed
     */
    public function testHostname($key=null, $allow = Zend_Filter::HOST_ALLOW_ALL)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        if (Zend_Filter::isHostname($value, $allow)) {
            return $value;
        }

        return FALSE;
    }


    /**
     * Returns value if it is a valid integer value, FALSE otherwise.
     *
     * @param mixed $key
     * @return mixed
     */
    public function testInt($key=null)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        if (Zend_Filter::isInt($value)) {
            return $value;
        }

        return FALSE;
    }


    /**
     * Returns value if it is a valid IP format, FALSE otherwise.
     *
     * @param mixed $key
     * @return mixed
     */
    public function testIp($key=null)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        if (Zend_Filter::isIp($value)) {
            return $value;
        }

        return FALSE;
    }


    /**
     * Returns value if it is less than $max, FALSE otherwise.
     *
     * @param mixed $key
     * @param mixed $ma
     * @return mixed
     */
    public function testLessThan($key=null, $max = NULL)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        if (Zend_Filter::isLessThan($value, $max)) {
            return $value;
        }

        return FALSE;
    }


    /**
     * Returns value if it is a valid format for a person's name,
     * FALSE otherwise.
     *
     * @param mixed $key
     * @return mixed
     */
    public function testName($key=null)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }        if (Zend_Filter::isName($value)) {
            return $value;
        }

        return FALSE;
    }


    /**
     * Returns value if it is one of $allowed, FALSE otherwise.
     *
     * @param mixed $key
     * @return mixed
     */
    public function testOneOf($key=null, $allowed = NULL)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        if (Zend_Filter::isOneOf($value, $allowed)) {
            return $value;
        }

        return FALSE;
    }


    /**
     * Returns value if it is a valid phone number format, FALSE
     * otherwise. The optional second argument indicates the country.
     *
     * @param mixed $key
     * @return mixed
     */
    public function testPhone($key=null, $country = 'US')
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        if (Zend_Filter::isPhone($value, $country)) {
            return $value;
        }

        return FALSE;
    }


    /**
     * Returns value if it matches $pattern, FALSE otherwise. Uses
     * preg_match() for the matching.
     *
     * @param mixed $key
     * @param mixed $pattern
     * @return mixed
     */
    public function testRegex($key=null, $pattern = NULL)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        if (Zend_Filter::isRegex($value, $pattern)) {
            return $value;
        }

        return FALSE;
    }


    public function testUri($key=null)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        if (Zend_Filter::isUri($value)) {
            return $value;
        }

        return FALSE;
    }


    /**
     * Returns value if it is a valid US ZIP, FALSE otherwise.
     *
     * @param mixed $key
     * @return mixed
     */
    public function testZip($key=null)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        if (Zend_Filter::isZip($value)) {
            return $value;
        }

        return FALSE;
    }


    /**
     * Returns value with all tags removed.
     *
     * @param mixed $key
     * @return mixed
     */
    public function noTags($key=null)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        return Zend_Filter::noTags($value);
    }


    /**
     * Returns basename(value).
     *
     * @param mixed $key
     * @return mixed
     */
    public function noPath($key=null)
    {
        if (!($value = $this->keyExists($key))) {
            return false;
        }
        
        return Zend_Filter::noPath($value);
    }

    /**
     * Checks if a key exists
     *
     * @param mixed $key
     * @return bool
     */
    public function keyExists($key)
    {
    	if($this->_isSingle) {
    		return $this->_source;
    	}
    	
    	if(is_null($key)) {
    		return false;
    	}
    	
    	if(!array_key_exists($key, $this->_source)) {
    		return false;
    	} else {
	    	$value = $this->_source[$key];
    	}

    	if(!is_scalar($value)) {
    		throw new Primitus_Exception("Provided key was not a scalar value or null, cannot filter it");
    	}

    	return $value;
    }

}
