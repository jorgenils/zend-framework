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


/** Zend_Search_Lucene_Search_QueryLexer */
require_once 'Zend/Search/Lucene/Search/QueryLexer.php';


/** Zend_Search_Lucene_FSM */
require_once 'Zend/Search/Lucene/FSM.php';

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
class Zend_Search_Lucene_Search_QueryParser extends Zend_Search_Lucene_FSM
{
    /**
     * Parser instance
     *
     * @var Zend_Search_Lucene_Search_QueryParser
     */
    static private $_instance = null;


    /**
     * Query lexer
     *
     * @var Zend_Search_Lucene_Search_QueryLexer
     */
    private $_lexer;


    /**
     * Tokens list
     * Array of Zend_Search_Lucene_Search_QueryToken objects
     *
     * @var array
     */
    private $_tokens;


    /**
     * Parser constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->_lexer = new Zend_Search_Lucene_Search_QueryLexer();
    }



    /**
     * Parses a query string
     *
     * @param string $strQuery
     * @return Zend_Search_Lucene_Search_Query
     * @throws Zend_Search_Lucene_Search_QueryParserException
     */
    static public function parse($strQuery)
    {
        if (self::$_instance === null) {
            self::$_instance = new Zend_Search_Lucene_Search_QueryParser();
        }

        self::$_instance->_tokens = self::$_instance->_lexer->tokenize($strQuery);

        // Empty query
        if (count(self::$_instance->_tokens) == 0) {
            throw new Zend_Search_Lucene_Search_QueryParserException('Syntax error: query string cannot be empty.');
        }


        // test output
        return self::$_instance->_tokens;

        // ...
    }

}

