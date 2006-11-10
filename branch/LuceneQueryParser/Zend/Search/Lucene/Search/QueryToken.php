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


/** Zend_Search_Lucene_Exception */
require_once 'Zend/Search/Lucene/Exception.php';


/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Search_QueryToken
{
    /**
     * Token types.
     */
    const TT_WORD                 = 0;  // Word
    const TT_PHRASE               = 1;  // Phrase (one or several quoted words)
    const TT_FIELD                = 2;  // Field name in 'field:word', field:<phrase> or field:(<subquery>) pairs
    const TT_FIELD_INDICATOR      = 3;  // ':'
    const TT_REQUIRED             = 4;  // '+'
    const TT_PROHIBITED           = 5;  // '-'
    const TT_FUZZY_PROX_MARK      = 6;  // '~'
    const TT_BOOSTING_MARK        = 7;  // '^'
    const TT_RANGE_INCL_START     = 8;  // '['
    const TT_RANGE_INCL_END       = 9;  // ']'
    const TT_RANGE_EXCL_START     = 10; // '['
    const TT_RANGE_EXCL_END       = 11; // ']'
    const TT_SUBQUERY_START       = 12; // '('
    const TT_SUBQUERY_END         = 13; // ')'
    const TT_AND_LEXEM            = 14; // 'AND' or 'and'
    const TT_OR_LEXEME            = 15; // 'OR'  or 'or'
    const TT_NOT                  = 16; // 'NOT' or 'not'
    const TT_TO                   = 17; // 'TO'  or 'to'
    const TT_NUMBER               = 18; // Number, like: 10, 0.8, .64, ....

    /**
     * Token type.
     *
     * @var integer
     */
    public $type;

    /**
     * Token text.
     *
     * @var integer
     */
    public $text;


    /**
     * IndexReader constructor needs token type and token text as a parameters.
     *
     * @param $tokType integer
     * @param $tokText string
     */
    public function __construct($tokType, $tokText)
    {
        if (!strlen($tokText)) {
            throw new Zend_Search_Lucene_Exception('Token text must be supplied.');
        }

        $this->type = $tokType;
        $this->text = $tokText;
    }
}

