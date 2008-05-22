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
 * @package    Zend_Validate
 * @subpackage Builder
 * @author     Bryce Lohr
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

require_once 'Zend/Validate/Interface.php';
require_once 'Zend/Validate/Builder/ErrorManager.php';
require_once 'Zend/Validate/Builder/ErrorManager/Interface.php';

/**
 * Zend Validate Builder
 *
 * Validates an entire set of data in one pass using a composite structure of 
 * Zend_Validate_Interface instances. Provides a Builder API for constructing 
 * the validation structure. Zend_Validate_Builder follows the Composite pattern 
 * on the Zend_Validate_Interface interface.
 *
 */
class Zend_Validate_Builder implements Zend_Validate_Interface
{
    /**
     * Specifies that a field is optional. If an optional field has the empty 
     * string as its value, it will be skipped (ie, never passed to the 
     * validator). If not set, every field is passed to every valiator.
     */
    const OPTIONAL = 1;

    /**
     * Specifies that the field name given is to be interpreted as a PCRE regex.  
     * All input data field names that match this pattern will be passed to the 
     * validator. If not set, the field name is a simple literal string match.
     */
    const PATTERN = 2;

    /**
     * WHen this flag is set, passing an array of field names to add() will 
     * cause the all of the fields named in the array to be passed to the 
     * validator as one array value. For example:
     *   add(new Zend_Validate_Password, array('pw1', 'pw2'), PASS_GROUP);
     * will pass an array consisting of the fields 'pw1' and 'pw2' from the 
     * input data to the instance of Zend_Validate_Password. For this to work, 
     * the given validator instance must be coded to accept such an array, which 
     * none of the built-in Framework validators currently do.
     *
     * If this flag is not set, each field in the array gets passed to the 
     * validator one-at-a-time, iteratively. In this example, 
     * Zend_Validate_Password would have been called twice, with 'pw1' first, 
     * and 'pw2' second, under the standard behaviour.
     */
    const PASS_GROUP = 4;

    /**
     * Validator table. This is an array of arrays, just like a result set from 
     * a database query. There are three columns in each row: validator, fields, 
     * and flags. The validator column holds the instance of a validator, the 
     * fields column holds either a field name, an array of field names, or a 
     * PCRE regex of field names. The flags column holds any special flags set 
     * for this intersection of validator and field(s).
     *
     * @var array 2-dimensional array
     */
    protected $_validatorTable;

    /**
     * ErrorManager instance
     *
     * @var Zend_Validate_Builder_ErrorManager_Interface 
     */
    protected $_errorManager;

    /**
     * Input data set to validate. This is typically the $_GET or $_POST array 
     * (or a subset thereof) that contains input data from the user. Public so 
     * it can easily be read/written anytime before isValid() is called.
     *
     * @var array
     */
    public $dataSet;


    /**
     * Constructor
     *
     * @param array Optional array of data to validate
     * @param array Optional options
     * @returns void
     * @throws none
     */
    public function __construct(array $dataSet = null)
    {
        $this->clear();
        $this->dataSet = $dataSet;
    }

    /**
     * Clear
     *
     * Resets all the class's internal state.
     */
    public function clear()
    {
        $this->dataSet         = array();
        $this->_validatorTable = array();
        $this->_errorManager   = null;
    }

    /**
     * Get ErrorManager instance
     *
     * Creates a new instance of the default ErrorManager, if one doesn't 
     * already exist.
     *
     * @returns Zend_Validate_Builder_ErrorManager_Interface
     */
    public function getErrorManager()
    {
        // This is merely done for convenience, and to promote lazy loading, 
        // when applicable.
        if (empty($this->_errorManager)) {
            $this->_errorManager = new Zend_Validate_Builder_ErrorManager;
        }
        return $this->_errorManager;
    }

    /**
     * Set ErrorManager instance
     *
     * Also propagates the given Error Manager down to any other instances of 
     * this class nested in the composite validator structure.
     *
     * @param Zend_Validate_Builder_ErrorManager_Interface
     */
    public function setErrorManager(Zend_Validate_Builder_ErrorManager_Interface $em)
    {
        $this->_errorManager = $em;

        foreach ($this->_validatorTable as $row) {
            if (method_exists($row['validator'], 'setErrorManager')) {
                $row['validator']->setErrorManager($em);
            }
        }
    }

    /**
     * Set Flags
     *
     * Provides a way to set field/validator flags outside the context of a call 
     * to add(). It requires the numeric array index of the validator "row" you 
     * want to modify. (add() returns the new row index it added.)
     *
     * @param int Validator row index
     * @param int Flags, just like the 3rd parm of add()
     * @returns void
     * @throws Zend_Validate_Exception
     */
    public function setFlags($id, $flags = 0)
    {
        if ((int)$id < 0) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('A validator index is required');
        }

        $this->_validatorTable[(int)$id]['flags'] = $flags;
    }

    /**
     * Add Flags
     *
     * Just like setFlags, but appends (bitwise OR, actually) the given flags to 
     * the ones currently set, instead of overwriting.
     *
     * @param int Validator row index
     * @param int Flags, just like the 3rd parm of add()
     * @returns void
     * @throws Zend_Validate_Exception
     */
    public function addFlags($id, $flags = 0)
    {
        if ((int)$id < 0) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('A validator index is required');
        }

        $this->_validatorTable[(int)$id]['flags'] |= $flags;
    }

    /**
     * Get Flags
     *
     * Allows interrogation of the current flags for a specific "row" in the 
     * current set of validators.
     *
     * @param int Validator row index
     * @returns int The flags, or null if invalid index
     * @throws Zend_Validate_Exception
     */
    public function getFlags($id)
    {
        $id = (int) $id;

        if ($id < 0) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('A validator index is required');
        }

        return array_key_exists($id, $this->_validatorTable)? 
                       $this->_validatorTable[$id]['flags'] :
                                                       null ;
    }

    /**
     * Add Validator
     *
     * Adds a validator for a field or set of fields, with optional flags for 
     * additional behaviour.
     *
     * The second parameter can be a string or an array. If a string, it can be 
     * either a literal match for a field name in the input data, or it can be a  
     * PCRE regex to match against field names from the input data, depending on  
     * the flags passed (3rd arg). If an array, each element is a field name to 
     * apply the validator to.
     *
     * The third parameter, $flags, can be set to any bitwise combination of the 
     * class constants. These flags specify behaviour options for this 
     * intersection of validator and fields (see the class const descriptions).  
     * This defaults to 0, which means none of the flags are active.
     *
     * NOTE: Although the flags can (and should) be freely combinable in theory, 
     * the current implementation is limited, in that it only supports some 
     * combinations of flags. Setting PASS_GROUP causes PATTERN and OPTIONAL to 
     * be ignored for the given validator, and setting PATTERN does not work for 
     * an array (that is, you can't pass an array of field name patterns yet).
     *
     * @param Zend_Validate_Interface Validator object
     * @param string|array A field name or pattern, or a list of field names
     * @param int Optional flags. Bitwise OR'ed values of the class constants
     * @returns int The "id" (array index) of the new validator "record"
     * @throws Zend_Validate_Exception
     */
    public function add(Zend_Validate_Interface $validator, $fieldSpec, $flags = 0)
    {
        if (empty($fieldSpec)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('A field or fields must be specified');
        }

        $this->_validatorTable[] = array(
            'validator' => $validator,
            'fieldSpec' => $fieldSpec,
            'flags'     => $flags
        );

        // Propagate the current Error Manager object down to any nested Builder 
        // instances
        if (method_exists($validator, 'setErrorManager')) {
            $validator->setErrorManager($this->getErrorManager());
        }

        // Return the index of the new row. This is specifically for the fluent 
        // facade, so it can set the optional flag. It's a little hokey, but it 
        // might be useful for other things.
        return count($this->_validatorTable) - 1;
    }

    // Zend_Validate_Interface

    /**
     * Is Valid
     *
     * If a value is passed to the composite isValid, it should be an array 
     * representing the entire input data set the validator structure should 
     * validate. This will override the dataset given to the constructor or the 
     * public dataSet property. This will always processes the full data set, 
     * but if any field anywhere is found to be invalid, this method returns 
     * false. Otherwise, it returns true.
     *
     * Multiple calls to isValid() should behave the same as a single call (it's 
     * supposed to be idempotent). To this end, the state of any raised error 
     * messages is not reset once at the beginning, because it cannot be assumed 
     * that this object is the top-level one (it might be a nested ZVB). Because 
     * all nested instances share the same Error Manager instance, only the 
     * errors for fields processed by this Builder instance are reset before 
     * being validated.
     *
     * @see Zend_Validate_Interface::isValid()
     *
     * @param array Set of data to validate. May be null
     * @returns bool Whether or not the composite validation is valid
     * @throws none
     */
    public function isValid($dataSet)
    {
        if (null == $dataSet) {
            $dataSet = $this->dataSet;
        }
        if (null == $dataSet || !is_array($dataSet)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('A data set to validate is required, and must be an array');
        }

        $isValid = true;

        // One day I'm going to factor out all this nearly-duplicated code for 
        // better DRY-ness, but that day is not today.
        foreach ($this->_validatorTable as $row) {

            // If we have a normal, plain literal field name...
            if (is_string($row['fieldSpec']) && !($row['flags'] & self::PATTERN)) {

                // Validate the specific field
                $field = $row['fieldSpec'];

                // Clear any errors that may have previously been raised for 
                // this field by this validator.
                $this->getErrorManager()
                     ->clear($field, get_class($row['validator']));

                // If the given field exists in the data set, grap its value, 
                // otherwise default to an empty string (in case it's optional)
                $value = isset($dataSet[$field])? $dataSet[$field]: '';

                // If this field is optional and empty, skip it
                if (($row['flags'] & self::OPTIONAL) && '' === $value) {
                    continue;
                }

                if (!$row['validator']->isValid($value)) {
                    $isValid = false;
                    $this->getErrorManager()
                         ->raise($field, $row['validator']);
                }

            // If we have a field name pattern...
            } else if (is_string($row['fieldSpec']) && $row['flags'] & self::PATTERN) {

                // Validate each matching field
                foreach ($dataSet as $field => $value) {
                    if (0 == preg_match($row['fieldSpec'], $field)) {
                        continue;
                    }

                    // Clear any errors that may have previously been raised for 
                    // this field by this validator.
                    $this->getErrorManager()
                         ->clear($field, get_class($row['validator']));

                    // If this field is optional and empty, skip it
                    if (($row['flags'] & self::OPTIONAL) && '' === $value) {
                        continue;
                    }

                    if (!$row['validator']->isValid($value)) {
                        $isValid = false;
                        $this->getErrorManager()
                             ->raise($field, $row['validator']);
                    }
                }

            // If we have an array of fields to validate individually...
            } else if (is_array($row['fieldSpec']) && !($row['flags'] & self::PASS_GROUP)) {

                // Validate each given field name
                foreach ($row['fieldSpec'] as $field) {

                    // Clear any errors that may have previously been raised for 
                    // this field by this validator.
                    $this->getErrorManager()
                         ->clear($field, get_class($row['validator']));

                    // If the given field exists in the data set, grap its 
                    // value, otherwise default to an empty string (in case it's 
                    // optional)
                    $value = isset($dataSet[$field])? $dataSet[$field]: '';

                    // If this field is optional and empty, skip it
                    if (($row['flags'] & self::OPTIONAL) && '' === $value) {
                        continue;
                    }

                    if (!$row['validator']->isValid($value)) {
                        $isValid = false;
                        $this->getErrorManager()
                             ->raise($field, $row['validator']);
                    }
                }

            // If we have an array of fields to pass as a group...
            } else if (is_array($row['fieldSpec']) && $row['flags'] & self::PASS_GROUP) {

                // PASS_GROUP complicates error handling. Instead of raising or 
                // clearing any validation errors for each field in the subset, 
                // we use whatever value is in the special 'key' element of the 
                // fieldSpec array, or the name of the first field (if no 'key' 
                // element is found).

                $errorKey = array_key_exists('key', $row['fieldSpec']) ?
                                              $row['fieldSpec']['key'] :
                                              reset($row['fieldSpec']) ;

                $this->getErrorManager()
                     ->clear($errorKey, get_class($row['validator']));


                // Extract the whole set of fields named, and pass that subset 
                // to the validator
                $subset = array_intersect_key($dataSet, array_flip($row['fieldSpec']));

                // The OPTIONAL flag doesn't apply in this case. How the subset 
                // field values are handled should be decided by the validator.
                if (!$row['validator']->isValid($subset)) {
                    $isValid = false;
                    $this->getErrorManager()
                         ->raise($errorKey, $row['validator']);
                }
            }
        }

        return $isValid;
    }

    /**
     * Returns an array of messages that explain why a previous isValid()
     * call returned false.
     *
     * If isValid() was never called or if the most recent isValid() call
     * returned true, then this method returns an empty array.
     *
     * @see Zend_Validate_Interface::getMessages()
     *
     * @return array
     */
    public function getMessages()
    {
        // Return all the collected errors
        return $this->getErrorManager()->getMessages();
    }

    /**
     * Trying to be as compatible with Zend_Validate_Interface as possible, this 
     * method returns only the validation failure codes, just as it does on the 
     * individual validator implementations. But in this version, the failure 
     * codes are nested in subarrays by field and validator class.
     *
     * If isValid() was never called or if the most recent isValid() call
     * returned true, then this method returns an empty array.
     *
     * @see Zend_Validate_Interface::getErrors()
     * @deprecated Since 1.5.0
     *
     * @return array
     */
    public function getErrors()
    {
        $errors = $this->getMessages();
        $out    = array();

        foreach ($errors as $field => $t1) {
            foreach ($t1 as $valClass => $t2) {
                $out[$field][$valClass] = array_keys($t2);
            }
        }

        return $out;
    }
}
