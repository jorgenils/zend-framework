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
     * Current token Id
     *
     * @var integer|string
     */
    private $_currentToken;

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
    const ST_COMMON_QUERY_ELEMENT       = 0;   // Terms, phrases, operators
    const ST_CLOSEDINT_RQ_START         = 1;   // Range query start (closed interval) - '['
    const ST_CLOSEDINT_RQ_FIRST_TERM    = 2;   // First term in '[term1 to term2]' construction
    const ST_CLOSEDINT_RQ_TO_TERM       = 3;   // 'TO' lexeme in '[term1 to term2]' construction
    const ST_CLOSEDINT_RQ_LAST_TERM     = 4;   // Second term in '[term1 to term2]' construction
    const ST_CLOSEDINT_RQ_END           = 5;   // Range query end (closed interval) - ']'
    const ST_OPENEDINT_RQ_START         = 6;   // Range query start (opened interval) - '{'
    const ST_OPENEDINT_RQ_FIRST_TERM    = 7;   // First term in '{term1 to term2}' construction
    const ST_OPENEDINT_RQ_TO_TERM       = 8;   // 'TO' lexeme in '{term1 to term2}' construction
    const ST_OPENEDINT_RQ_LAST_TERM     = 9;   // Second term in '{term1 to term2}' construction
    const ST_OPENEDINT_RQ_END           = 10;  // Range query end (opened interval) - '}'

    /**
     * Parser constructor
     */
    public function __construct()
    {
        parent::__construct(array(self::ST_COMMON_QUERY_ELEMENT,
                                  self::ST_CLOSEDINT_RQ_START,
                                  self::ST_CLOSEDINT_RQ_FIRST_TERM,
                                  self::ST_CLOSEDINT_RQ_TO_TERM,
                                  self::ST_CLOSEDINT_RQ_LAST_TERM,
                                  self::ST_CLOSEDINT_RQ_END,
                                  self::ST_OPENEDINT_RQ_START,
                                  self::ST_OPENEDINT_RQ_FIRST_TERM,
                                  self::ST_OPENEDINT_RQ_TO_TERM,
                                  self::ST_OPENEDINT_RQ_LAST_TERM,
                                  self::ST_OPENEDINT_RQ_END
                                 ),
                            Zend_Search_Lucene_Search_QueryToken::getTypes());

        $this->addRules(
             array(array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_WORD,             self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_PHRASE,           self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_FIELD,            self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_REQUIRED,         self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_PROHIBITED,       self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_FUZZY_PROX_MARK,  self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_BOOSTING_MARK,    self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_RANGE_INCL_START, self::ST_CLOSEDINT_RQ_START),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_RANGE_EXCL_START, self::ST_OPENEDINT_RQ_START),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_SUBQUERY_START,   self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_SUBQUERY_END,     self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_AND_LEXEME,       self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_OR_LEXEME,        self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_NOT_LEXEME,       self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_NUMBER,           self::ST_COMMON_QUERY_ELEMENT)
                  ));
        $this->addRules(
             array(array(self::ST_CLOSEDINT_RQ_START,      Zend_Search_Lucene_Search_QueryToken::TT_WORD,           self::ST_CLOSEDINT_RQ_FIRST_TERM),
                   array(self::ST_CLOSEDINT_RQ_FIRST_TERM, Zend_Search_Lucene_Search_QueryToken::TT_TO_LEXEME,      self::ST_CLOSEDINT_RQ_TO_TERM),
                   array(self::ST_CLOSEDINT_RQ_TO_TERM,    Zend_Search_Lucene_Search_QueryToken::TT_WORD,           self::ST_CLOSEDINT_RQ_LAST_TERM),
                   array(self::ST_CLOSEDINT_RQ_LAST_TERM,  Zend_Search_Lucene_Search_QueryToken::TT_RANGE_INCL_END, self::ST_COMMON_QUERY_ELEMENT)
                  ));
        $this->addRules(
             array(array(self::ST_OPENEDINT_RQ_START,      Zend_Search_Lucene_Search_QueryToken::TT_WORD,           self::ST_OPENEDINT_RQ_FIRST_TERM),
                   array(self::ST_OPENEDINT_RQ_FIRST_TERM, Zend_Search_Lucene_Search_QueryToken::TT_TO_LEXEME,      self::ST_OPENEDINT_RQ_TO_TERM),
                   array(self::ST_OPENEDINT_RQ_TO_TERM,    Zend_Search_Lucene_Search_QueryToken::TT_WORD,           self::ST_OPENEDINT_RQ_LAST_TERM),
                   array(self::ST_OPENEDINT_RQ_LAST_TERM,  Zend_Search_Lucene_Search_QueryToken::TT_RANGE_EXCL_END, self::ST_COMMON_QUERY_ELEMENT)
                  ));



        $addTermEntryAction   = new Zend_Search_Lucene_FSMAction($this, 'addTermEntry');
        $addPhraseEntryAction = new Zend_Search_Lucene_FSMAction($this, 'addPhraseEntry');
        $setFieldAction       = new Zend_Search_Lucene_FSMAction($this, 'setField');
        $setSignAction        = new Zend_Search_Lucene_FSMAction($this, 'setSign');
        $setFuzzyProxAction   = new Zend_Search_Lucene_FSMAction($this, 'processFuzzyProximityModifier');


        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_WORD,            $addTermEntryAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_PHRASE,          $addPhraseEntryAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_FIELD,           $setFieldAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_REQUIRED,        $setSignAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_PROHIBITED,      $setSignAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_FUZZY_PROX_MARK, $setSignAction);


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


        foreach (self::$_instance->_tokens as $tokId => $token) {
            try {
                self::$_instance->_currentToken = $tokId;
                self::$_instance->process($token->type);
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'There is no any rule for') !== false) {
                    throw new Zend_Search_Lucene_Search_QueryParserException( 'Syntax error at char position ' . $token->position . '.' );
                }

                throw $e;
            }
        }

        // test output
        return self::$_instance->_tokens;
    }


    /*********************************************************************
     * Actions implementation
     *
     * Actions affect on recognized lexemes list
     *********************************************************************/

    /**
     * Add term to a query
     */
    public function addTermEntry()
    {
        $term = new Zend_Search_Lucene_Index_Term($this->_tokens[$this->_currentToken]->text,
                                                  $this->_context->getField());

        $this->_context->addEntry(new Zend_Search_Lucene_Search_QueryEntry_Term($term));
    }

    /**
     * Add phrase to a query
     */
    public function addPhraseEntry()
    {
        $text = $this->_tokens[$this->_currentToken]->text;
        $this->_context->addEntry(new Zend_Search_Lucene_Search_QueryEntry_Term($text, $this->_context->getField()));
    }

    /**
     * Set entry field
     */
    public function setField()
    {
        $fieldName = $this->_tokens[$this->_currentToken]->text;
        $this->_context->setNextEntryField($fieldName);
    }

    /**
     * Set entry sign
     */
    public function setSign()
    {
        $sign = $this->_tokens[$this->_currentToken]->type;
        $this->_context->setNextEntrySign($fieldName);
    }


    /**
     * Process fuzzy search/proximity modifier - '~'
     */
    public function processFuzzyProximityModifier()
    {
        $this->_context->processFuzzyProximityModifier($fieldName);
    }

}

