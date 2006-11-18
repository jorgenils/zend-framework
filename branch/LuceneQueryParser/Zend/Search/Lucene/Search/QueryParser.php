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

/** Zend_Search_Lucene_Search_QueryParserContext */
require_once 'Zend/Search/Lucene/Search/QueryParserContext.php';


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
     * Current query parser context
     *
     * @var Zend_Search_Lucene_Search_QueryParserContext
     */
    private $_context;

    /**
     * Context stack
     *
     * @var array
     */
    private $_contextStack;


    /** Query parser State Machine states */
    const ST_WHITE_SPACE             = 0;   // Wait for next query element (term, phrase or subquery)
    const ST_BOOLEAN_OPERATOR        = 1;   // AND, OR or NOT operators
    const ST_PRESENCE_SIGN           = 2;   // '+' or '-' signs (required or prohibited operators)
    const ST_FIELD_QUALIFIER         = 3;   // Default search field qualifier for next query element
                                            // Ex. 'title:Zend', 'title:"Zend Framework"' or
                                            // 'title:("Zend Framework" OR release OR contents:MVC)'
    const ST_WORD                    = 4;   // Search term
    const ST_PHRASE                  = 5;   // Search phrase
    const ST_MODIFIER                = 6;   // Term/phrase modifiers - '~' or '^'
    const ST_MODIFIER_PARAMETER      = 7;   // Modifiers parameter. It's a number, which defines
                                            // similarity for fuzzy search queries,
                                            // word distance for proximity search queries
                                            // or boost factor
    const ST_CLOSEDINT_RQ_START      = 8;   // Range query start (closed interval) - '['
    const ST_CLOSEDINT_RQ_FIRST_TERM = 9;   // First term in '[term1 to term2]' construction
    const ST_CLOSEDINT_RQ_TO_TERM    = 10;  // 'TO' lexeme in '[term1 to term2]' construction
    const ST_CLOSEDINT_RQ_LAST_TERM  = 11;  // Second term in '[term1 to term2]' construction
    const ST_CLOSEDINT_RQ_END        = 12;  // Range query end (closed interval) - ']'
    const ST_OPENEDINT_RQ_START      = 13;  // Range query start (opened interval) - '{'
    const ST_OPENEDINT_RQ_FIRST_TERM = 14;  // First term in '{term1 to term2}' construction
    const ST_OPENEDINT_RQ_TO_TERM    = 15;  // 'TO' lexeme in '{term1 to term2}' construction
    const ST_OPENEDINT_RQ_LAST_TERM  = 16;  // Second term in '{term1 to term2}' construction
    const ST_OPENEDINT_RQ_END        = 17;  // Range query end (opened interval) - '}'
    const ST_ERROR                   = 18;  // Error state


    /**
     * Parser constructor
     */
    public function __construct()
    {
        parent::__construct(array(self::ST_WHITE_SPACE,
                                  self::ST_BOOLEAN_OPERATOR,
                                  self::ST_PRESENCE_SIGN,
                                  self::ST_FIELD_QUALIFIER,
                                  self::ST_WORD,
                                  self::ST_PHRASE,
                                  self::ST_MODIFIER,
                                  self::ST_MODIFIER_PARAMETER,
                                  self::ST_CLOSEDINT_RQ_START,
                                  self::ST_CLOSEDINT_RQ_FIRST_TERM,
                                  self::ST_CLOSEDINT_RQ_TO_TERM,
                                  self::ST_CLOSEDINT_RQ_LAST_TERM,
                                  self::ST_CLOSEDINT_RQ_END,
                                  self::ST_OPENEDINT_RQ_START,
                                  self::ST_OPENEDINT_RQ_FIRST_TERM,
                                  self::ST_OPENEDINT_RQ_TO_TERM,
                                  self::ST_OPENEDINT_RQ_LAST_TERM,
                                  self::ST_OPENEDINT_RQ_END,
                                  self::ST_ERROR
                                 ),
                            Zend_Search_Lucene_Search_QueryToken::getTypes());

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

