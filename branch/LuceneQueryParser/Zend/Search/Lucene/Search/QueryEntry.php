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
    public $query;


    /**
     * Query entries
     * Each entry may be term, phrase or subquery
     *
     * @var unknown_type
     */
    public $entries = array();


    public function __construct()
    {
    }


    public function addEntry()
    {

    }

    /**
     * Set boost factor to the entry
     *
     */
    public function boostEntry()
    {
    }


}
