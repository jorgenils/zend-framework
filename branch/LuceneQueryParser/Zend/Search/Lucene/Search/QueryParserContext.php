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
 * @package    Zend_Search_Lucene
 * @subpackage Search
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/** Zend_Search_Lucene_Index_Term */
require_once 'Zend/Search/Lucene/Index/Term.php';

/** Zend_Search_Lucene_Search_Query_Term */
require_once 'Zend/Search/Lucene/Search/Query/Term.php';

/** Zend_Search_Lucene_Search_Query_MultiTerm */
require_once 'Zend/Search/Lucene/Search/Query/MultiTerm.php';

/** Zend_Search_Lucene_Search_Query_Boolean */
require_once 'Zend/Search/Lucene/Search/Query/Boolean.php';

/** Zend_Search_Lucene_Search_Query_Phrase */
require_once 'Zend/Search/Lucene/Search/Query/Phrase.php';

/** Zend_Search_Lucene_Exception */
require_once 'Zend/Search/Lucene/Exception.php';

/** Zend_Search_Lucene_Search_QueryParserException */
require_once 'Zend/Search/Lucene/Search/QueryParserException.php';


/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Search_QueryParserContext
{
    /**
     * Default field for the context.
     *
     * null means, that term should be searched through all fields
     * Zend_Search_Lucene_Search_Query::rewriteQuery($index) transletes such queries to several
     *
     * @var string|null
     */
    private $_defaultField;

    /**
     * Field specified for next entry
     *
     * @var string
     */
    private $_nextEntryField = null;

    /**
     * True means, that term is required.
     * False means, that term is prohibited.
     * null means, that term is neither prohibited, nor required
     *
     * @var boolean
     */
    private $_nextEntrySign = null;


    /**
     * Entries grouping mode
     */
    const GM_SIGNS   = 0;  // Signs mode: '+term1 term2 -term3 +(subquery1) -(subquery2)'
    const GM_BOOLEAN = 1;  // Boolean operators mode: 'term1 and term2  or  (subquery1) and not (subquery2)'

    /**
     * Grouping mode
     *
     * @var integer
     */
    private $_mode = null;

    /**
     * Entries signs.
     * Used in GM_SIGNS grouping mode
     *
     * @var arrays
     */
    private $_signs = array();

    /**
     * Query entries
     * Each entry is a Zend_Search_Lucene_Search_QueryEntry object or
     * boolean operator (Zend_Search_Lucene_Search_QueryToken class constant)
     *
     * @var array
     */
    private $_entries = array();


    /**
     * Context object constructor
     *
     * @param string|null $defaultField
     */
    public function __construct($defaultField)
    {
        $this->_defaultField = $defaultField;
    }


    /**
     * Get context default field
     *
     * @return string|null
     */
    public function getField()
    {
        return ($this->_nextEntryField !== null)  ?  $this->_nextEntryField : $this->_defaultField;
    }

    /**
     * Set field for next entry
     *
     * @param string $field
     */
    public function setNextEntryField($field)
    {
        $this->_nextEntryField = $field;
    }


    /**
     * Set sign for next entry
     *
     * @param integer $sign
     * @throws Zend_Search_Lucene_Search_QueryParserException
     */
    public function setNextEntrySign($sign)
    {
        if ($this->_mode === self::GM_BOOLEAN) {
            throw new Zend_Search_Lucene_Search_QueryParserException('It\'s not allowed to mix boolean and signs styles in the same subquery.');
        }

        $this->_mode = self::GM_SIGNS;

        if ($sign == Zend_Search_Lucene_Search_QueryToken::TT_REQUIRED) {
            $this->_nextEntrySign = true;
        } else if ($sign == Zend_Search_Lucene_Search_QueryToken::TT_PROHIBITED) {
            $this->_nextEntrySign = false;
        }
    }


    /**
     *
     *
     */
    public function addEntry(Zend_Search_Lucene_Search_QueryEntry $entry)
    {
        if ($this->_mode !== self::GM_BOOLEAN) {
            $this->_signs[] = $this->_nextEntrySign;
        }

        $this->_entries[] = $entry;

        $this->_nextEntryField = null;
        $this->_nextEntrySign  = null;
    }


    /**
     * Process fuzzy search or proximity search modifier
     *
     * @throws Zend_Search_Lucene_Search_QueryParserException
     */
    public function processFuzzyProximityModifier()
    {
        // Check, that modifier has came just after word or phrase
        if ($this->_nextEntryField !== null  ||  $this->_nextEntrySign !== null) {
            throw new Zend_Search_Lucene_Search_QueryParserException('\'~\' modifier must follow word or phrase.');
        }

        $lastEntry = array_pop($this->_entries);

        if (!$lastEntry instanceof Zend_Search_Lucene_Search_QueryEntry) {
            // there are no entries or last entry is boolean operator
            throw new Zend_Search_Lucene_Search_QueryParserException('\'~\' modifier must follow word or phrase.');
        }

        $lastEntry->processFuzzyProximityModifier()
    }

    /**
     * Set boost factor to the entry
     *
     */
    public function boostEntry()
    {
    }

    /**
     * Generate query from current context
     *
     * @return Zend_Search_Lucene_Search_Query
     */
    public function query()
    {
    }
}
