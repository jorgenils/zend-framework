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
    const ST_WHITE_SPACE                = 0;   // Wait for next query element (term, phrase or subquery)
    const ST_BOOLEAN_OPERATOR           = 1;   // AND, OR or NOT operators
    const ST_PRESENCE_SIGN              = 2;   // '+' or '-' signs (required or prohibited operators)
    const ST_FIELD_QUALIFIER            = 3;   // Default search field qualifier for next query element
                                               // Ex. 'title:Zend', 'title:"Zend Framework"' or
                                               // 'title:("Zend Framework" OR release OR contents:MVC)'
    const ST_WORD                       = 4;   // Search term
    const ST_PHRASE                     = 5;   // Search phrase
    const ST_SUBQUERY                   = 6;   // Subquery
    const ST_BOOST_OPERATOR             = 7;   // Term/phrase/subquery boost operator - '^'
    const ST_BOOST_FACTOR               = 8;   // Boost factor
    const ST_FUZZY_PROXIMITY_OPERATOR   = 9;   // Fuzzy search or proximity search operator - '~'
    const ST_FUZZY_PROXIMITY_PARAMETER  = 10;  // Similarity for fuzzy search queries
                                               // and distance for proximity search queries
    const ST_CLOSEDINT_RQ_START         = 11;  // Range query start (closed interval) - '['
    const ST_CLOSEDINT_RQ_FIRST_TERM    = 12;  // First term in '[term1 to term2]' construction
    const ST_CLOSEDINT_RQ_TO_TERM       = 13;  // 'TO' lexeme in '[term1 to term2]' construction
    const ST_CLOSEDINT_RQ_LAST_TERM     = 14;  // Second term in '[term1 to term2]' construction
    const ST_CLOSEDINT_RQ_END           = 15;  // Range query end (closed interval) - ']'
    const ST_OPENEDINT_RQ_START         = 16;  // Range query start (opened interval) - '{'
    const ST_OPENEDINT_RQ_FIRST_TERM    = 17;  // First term in '{term1 to term2}' construction
    const ST_OPENEDINT_RQ_TO_TERM       = 18;  // 'TO' lexeme in '{term1 to term2}' construction
    const ST_OPENEDINT_RQ_LAST_TERM     = 19;  // Second term in '{term1 to term2}' construction
    const ST_OPENEDINT_RQ_END           = 20;  // Range query end (opened interval) - '}'
    const ST_ERROR                      = 21;  // Error state


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
                                  self::ST_SUBQUERY,
                                  self::ST_BOOST_OPERATOR,
                                  self::ST_BOOST_FACTOR,
                                  self::ST_FUZZY_PROXIMITY_OPERATOR,
                                  self::ST_FUZZY_PROXIMITY_PARAMETER,
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

        $this->addRules(
             array(array(self::ST_WHITE_SPACE, Zend_Search_Lucene_Search_QueryToken::TT_WORD,             self::ST_WORD),
                   array(self::ST_WHITE_SPACE, Zend_Search_Lucene_Search_QueryToken::TT_PHRASE,           self::ST_PHRASE),
                   array(self::ST_WHITE_SPACE, Zend_Search_Lucene_Search_QueryToken::TT_FIELD,            self::ST_FIELD_QUALIFIER),
                   array(self::ST_WHITE_SPACE, Zend_Search_Lucene_Search_QueryToken::TT_REQUIRED,         self::ST_PRESENCE_SIGN),
                   array(self::ST_WHITE_SPACE, Zend_Search_Lucene_Search_QueryToken::TT_PROHIBITED,       self::ST_PRESENCE_SIGN),
                   array(self::ST_WHITE_SPACE, Zend_Search_Lucene_Search_QueryToken::TT_RANGE_INCL_START, self::ST_CLOSEDINT_RQ_START),
                   array(self::ST_WHITE_SPACE, Zend_Search_Lucene_Search_QueryToken::TT_RANGE_EXCL_START, self::ST_OPENEDINT_RQ_START),
                   array(self::ST_WHITE_SPACE, Zend_Search_Lucene_Search_QueryToken::TT_SUBQUERY_START,   self::ST_WHITE_SPACE),
                   array(self::ST_WHITE_SPACE, Zend_Search_Lucene_Search_QueryToken::TT_NOT_LEXEME,       self::ST_BOOLEAN_OPERATOR)
                  ));
        $this->addRules(
             array(array(self::ST_BOOLEAN_OPERATOR, Zend_Search_Lucene_Search_QueryToken::TT_WORD,             self::ST_WORD),
                   array(self::ST_BOOLEAN_OPERATOR, Zend_Search_Lucene_Search_QueryToken::TT_PHRASE,           self::ST_PHRASE),
                   array(self::ST_BOOLEAN_OPERATOR, Zend_Search_Lucene_Search_QueryToken::TT_FIELD,            self::ST_FIELD_QUALIFIER),
                   array(self::ST_BOOLEAN_OPERATOR, Zend_Search_Lucene_Search_QueryToken::TT_RANGE_INCL_START, self::ST_CLOSEDINT_RQ_START),
                   array(self::ST_BOOLEAN_OPERATOR, Zend_Search_Lucene_Search_QueryToken::TT_RANGE_EXCL_START, self::ST_OPENEDINT_RQ_START),
                   array(self::ST_BOOLEAN_OPERATOR, Zend_Search_Lucene_Search_QueryToken::TT_SUBQUERY_START,   self::ST_WHITE_SPACE),
                   array(self::ST_BOOLEAN_OPERATOR, Zend_Search_Lucene_Search_QueryToken::TT_NOT_LEXEME,       self::ST_BOOLEAN_OPERATOR)
                  ));
        $this->addRules(
             array(array(self::ST_PRESENCE_SIGN, Zend_Search_Lucene_Search_QueryToken::TT_WORD,             self::ST_WORD),
                   array(self::ST_PRESENCE_SIGN, Zend_Search_Lucene_Search_QueryToken::TT_PHRASE,           self::ST_PHRASE),
                   array(self::ST_PRESENCE_SIGN, Zend_Search_Lucene_Search_QueryToken::TT_FIELD,            self::ST_FIELD_QUALIFIER),
                   array(self::ST_PRESENCE_SIGN, Zend_Search_Lucene_Search_QueryToken::TT_RANGE_INCL_START, self::ST_CLOSEDINT_RQ_START),
                   array(self::ST_PRESENCE_SIGN, Zend_Search_Lucene_Search_QueryToken::TT_RANGE_EXCL_START, self::ST_OPENEDINT_RQ_START),
                   array(self::ST_PRESENCE_SIGN, Zend_Search_Lucene_Search_QueryToken::TT_SUBQUERY_START,   self::ST_WHITE_SPACE),
                  ));
        $this->addRules(
             array(array(self::ST_FIELD_QUALIFIER, Zend_Search_Lucene_Search_QueryToken::TT_WORD,             self::ST_WORD),
                   array(self::ST_FIELD_QUALIFIER, Zend_Search_Lucene_Search_QueryToken::TT_PHRASE,           self::ST_PHRASE),
                   array(self::ST_FIELD_QUALIFIER, Zend_Search_Lucene_Search_QueryToken::TT_RANGE_INCL_START, self::ST_CLOSEDINT_RQ_START),
                   array(self::ST_FIELD_QUALIFIER, Zend_Search_Lucene_Search_QueryToken::TT_RANGE_EXCL_START, self::ST_OPENEDINT_RQ_START),
                   array(self::ST_FIELD_QUALIFIER, Zend_Search_Lucene_Search_QueryToken::TT_SUBQUERY_START,   self::ST_WHITE_SPACE),
                  ));
        $this->addRules(
             array(array(self::ST_WORD, Zend_Search_Lucene_Search_QueryToken::TT_WORD,             self::ST_WORD),
                   array(self::ST_WORD, Zend_Search_Lucene_Search_QueryToken::TT_PHRASE,           self::ST_PHRASE),
                   array(self::ST_WORD, Zend_Search_Lucene_Search_QueryToken::TT_FIELD,            self::ST_FIELD_QUALIFIER),
                   array(self::ST_WORD, Zend_Search_Lucene_Search_QueryToken::TT_REQUIRED,         self::ST_PRESENCE_SIGN),
                   array(self::ST_WORD, Zend_Search_Lucene_Search_QueryToken::TT_PROHIBITED,       self::ST_PRESENCE_SIGN),
                   array(self::ST_WORD, Zend_Search_Lucene_Search_QueryToken::TT_FUZZY_PROX_MARK,  self::ST_FUZZY_PROXIMITY_OPERATOR),
                   array(self::ST_WORD, Zend_Search_Lucene_Search_QueryToken::TT_BOOSTING_MARK,    self::ST_BOOST_OPERATOR),
                   array(self::ST_WORD, Zend_Search_Lucene_Search_QueryToken::TT_RANGE_INCL_START, self::ST_CLOSEDINT_RQ_START),
                   array(self::ST_WORD, Zend_Search_Lucene_Search_QueryToken::TT_RANGE_EXCL_START, self::ST_OPENEDINT_RQ_START),
                   array(self::ST_WORD, Zend_Search_Lucene_Search_QueryToken::TT_SUBQUERY_START,   self::ST_WHITE_SPACE),
                   array(self::ST_WORD, Zend_Search_Lucene_Search_QueryToken::TT_AND_LEXEME,       self::ST_BOOLEAN_OPERATOR),
                   array(self::ST_WORD, Zend_Search_Lucene_Search_QueryToken::TT_OR_LEXEME,        self::ST_BOOLEAN_OPERATOR)
                  ));
        $this->addRules(
             array(array(self::ST_PHRASE, Zend_Search_Lucene_Search_QueryToken::TT_WORD,             self::ST_WORD),
                   array(self::ST_PHRASE, Zend_Search_Lucene_Search_QueryToken::TT_PHRASE,           self::ST_PHRASE),
                   array(self::ST_PHRASE, Zend_Search_Lucene_Search_QueryToken::TT_FIELD,            self::ST_FIELD_QUALIFIER),
                   array(self::ST_PHRASE, Zend_Search_Lucene_Search_QueryToken::TT_REQUIRED,         self::ST_PRESENCE_SIGN),
                   array(self::ST_PHRASE, Zend_Search_Lucene_Search_QueryToken::TT_PROHIBITED,       self::ST_PRESENCE_SIGN),
                   array(self::ST_PHRASE, Zend_Search_Lucene_Search_QueryToken::TT_FUZZY_PROX_MARK,  self::ST_FUZZY_PROXIMITY_OPERATOR),
                   array(self::ST_PHRASE, Zend_Search_Lucene_Search_QueryToken::TT_BOOSTING_MARK,    self::ST_BOOST_OPERATOR),
                   array(self::ST_PHRASE, Zend_Search_Lucene_Search_QueryToken::TT_RANGE_INCL_START, self::ST_CLOSEDINT_RQ_START),
                   array(self::ST_PHRASE, Zend_Search_Lucene_Search_QueryToken::TT_RANGE_EXCL_START, self::ST_OPENEDINT_RQ_START),
                   array(self::ST_PHRASE, Zend_Search_Lucene_Search_QueryToken::TT_SUBQUERY_START,   self::ST_WHITE_SPACE),
                   array(self::ST_PHRASE, Zend_Search_Lucene_Search_QueryToken::TT_AND_LEXEME,       self::ST_BOOLEAN_OPERATOR),
                   array(self::ST_PHRASE, Zend_Search_Lucene_Search_QueryToken::TT_OR_LEXEME,        self::ST_BOOLEAN_OPERATOR)
                  ));
        $this->addRules(
             array(array(self::ST_SUBQUERY, Zend_Search_Lucene_Search_QueryToken::TT_WORD,             self::ST_WORD),
                   array(self::ST_SUBQUERY, Zend_Search_Lucene_Search_QueryToken::TT_PHRASE,           self::ST_PHRASE),
                   array(self::ST_SUBQUERY, Zend_Search_Lucene_Search_QueryToken::TT_FIELD,            self::ST_FIELD_QUALIFIER),
                   array(self::ST_SUBQUERY, Zend_Search_Lucene_Search_QueryToken::TT_REQUIRED,         self::ST_PRESENCE_SIGN),
                   array(self::ST_SUBQUERY, Zend_Search_Lucene_Search_QueryToken::TT_PROHIBITED,       self::ST_PRESENCE_SIGN),
                   array(self::ST_SUBQUERY, Zend_Search_Lucene_Search_QueryToken::TT_FUZZY_PROX_MARK,  self::ST_FUZZY_PROXIMITY_OPERATOR),
                   array(self::ST_SUBQUERY, Zend_Search_Lucene_Search_QueryToken::TT_BOOSTING_MARK,    self::ST_BOOST_OPERATOR),
                   array(self::ST_SUBQUERY, Zend_Search_Lucene_Search_QueryToken::TT_RANGE_INCL_START, self::ST_CLOSEDINT_RQ_START),
                   array(self::ST_SUBQUERY, Zend_Search_Lucene_Search_QueryToken::TT_RANGE_EXCL_START, self::ST_OPENEDINT_RQ_START),
                   array(self::ST_SUBQUERY, Zend_Search_Lucene_Search_QueryToken::TT_SUBQUERY_START,   self::ST_WHITE_SPACE),
                   array(self::ST_SUBQUERY, Zend_Search_Lucene_Search_QueryToken::TT_AND_LEXEME,       self::ST_BOOLEAN_OPERATOR),
                   array(self::ST_SUBQUERY, Zend_Search_Lucene_Search_QueryToken::TT_OR_LEXEME,        self::ST_BOOLEAN_OPERATOR)
                  ));
        $this->addRules(
             array(array(self::ST_BOOST_OPERATOR, Zend_Search_Lucene_Search_QueryToken::TT_WORD,             self::ST_WORD),
                   array(self::ST_BOOST_OPERATOR, Zend_Search_Lucene_Search_QueryToken::TT_PHRASE,           self::ST_PHRASE),
                   array(self::ST_BOOST_OPERATOR, Zend_Search_Lucene_Search_QueryToken::TT_FIELD,            self::ST_FIELD_QUALIFIER),
                   array(self::ST_BOOST_OPERATOR, Zend_Search_Lucene_Search_QueryToken::TT_REQUIRED,         self::ST_PRESENCE_SIGN),
                   array(self::ST_BOOST_OPERATOR, Zend_Search_Lucene_Search_QueryToken::TT_PROHIBITED,       self::ST_PRESENCE_SIGN),
                   array(self::ST_BOOST_OPERATOR, Zend_Search_Lucene_Search_QueryToken::TT_FUZZY_PROX_MARK,  self::ST_FUZZY_PROXIMITY_OPERATOR),
                   array(self::ST_BOOST_OPERATOR, Zend_Search_Lucene_Search_QueryToken::TT_BOOSTING_MARK,    self::ST_BOOST_OPERATOR),
                   array(self::ST_BOOST_OPERATOR, Zend_Search_Lucene_Search_QueryToken::TT_RANGE_INCL_START, self::ST_CLOSEDINT_RQ_START),
                   array(self::ST_BOOST_OPERATOR, Zend_Search_Lucene_Search_QueryToken::TT_RANGE_EXCL_START, self::ST_OPENEDINT_RQ_START),
                   array(self::ST_BOOST_OPERATOR, Zend_Search_Lucene_Search_QueryToken::TT_SUBQUERY_START,   self::ST_WHITE_SPACE),
                   array(self::ST_BOOST_OPERATOR, Zend_Search_Lucene_Search_QueryToken::TT_AND_LEXEME,       self::ST_BOOLEAN_OPERATOR),
                   array(self::ST_BOOST_OPERATOR, Zend_Search_Lucene_Search_QueryToken::TT_OR_LEXEME,        self::ST_BOOLEAN_OPERATOR)
                  ));



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

