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
 * @package   Zend_CodingStandard
 * @copyright Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: $
 */
if (class_exists('PHP_CodeSniffer_CommentParser_FunctionCommentParser', true) === false) {
    throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_CommentParser_FunctionCommentParser not found');
}

/**
 * Zend_Sniffs_Commenting_FunctionCommentSniff
 *
 * Parses and verifies the doc comments for functions
 *
 * @category  Zend
 * @package   Zend_CodingStandard
 * @copyright Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Sniffs_Commenting_FunctionCommentSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * The name of the method that we are currently processing
     *
     * @var string
     */
    private $_methodName = '';

    /**
     * The position in the stack where the function token was found
     *
     * @var integer
     */
    private $_functionToken = null;

    /**
     * The position in the stack where the class token was found
     *
     * @var integer
     */
    private $_classToken = null;

    /**
     * The index of the current tag we are processing
     *
     * @var integer
     */
    private $_tagIndex = 0;

    /**
     * The found tokens
     *
     * @var array
     */
    private $_tokens = null;

    /**
     * The function comment parser for the current method
     *
     * @var PHP_CodeSniffer_Comment_Parser_FunctionCommentParser
     */
    protected $_commentParser = null;

    /**
     * The current PHP_CodeSniffer_File object we are processing
     *
     * @var PHP_CodeSniffer_File
     */
    protected $_currentFile = null;

    /**
     * Returns an array of tokens this test wants to listen for
     *
     * @return array
     */
    public function register()
    {
        return array(T_FUNCTION);

    }

    /**
     * Processes this test, when one of its tokens is encountered
     *
     * @param  PHP_CodeSniffer_File $phpcsFile The file being scanned
     * @param  integer              $stackPtr  The position of the current token
     *                                         in the stack passed in $tokens
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $this->_currentFile = $phpcsFile;
        $this->_tokens      = $phpcsFile->getTokens();

        $find = array(
                 T_COMMENT,
                 T_DOC_COMMENT,
                 T_CLASS,
                 T_FUNCTION,
                 T_OPEN_TAG,
                );

        $commentEnd = $phpcsFile->findPrevious($find, ($stackPtr - 1));

        if ($commentEnd === false) {
            return;
        }

        // If the token that we found was a class or a function, then this
        // function has no doc comment
        $code = $this->_tokens[$commentEnd]['code'];

        if ($code === T_COMMENT) {
            $error = 'You must use "/**" style comments for a function comment';
            $phpcsFile->addError($error, $stackPtr);
            return;
        } else if ($code !== T_DOC_COMMENT) {
            $error = 'Missing function doc comment';
            $phpcsFile->addError($error, $stackPtr);
            return;
        }

        // If there is any code between the function keyword and the doc block
        // then the doc block is not for us
        $ignore    = PHP_CodeSniffer_Tokens::$scopeModifiers;
        $ignore[]  = T_STATIC;
        $ignore[]  = T_WHITESPACE;
        $ignore[]  = T_ABSTRACT;
        $ignore[]  = T_FINAL;
        $prevToken = $phpcsFile->findPrevious($ignore, ($stackPtr - 1), null, true);
        if ($prevToken !== $commentEnd) {
            $phpcsFile->addError('Missing function doc comment', $stackPtr);
            return;
        }

        $this->_functionToken = $stackPtr;

        foreach ($this->_tokens[$stackPtr]['conditions'] as $condPtr => $condition) {
            if ($condition === T_CLASS or $condition === T_INTERFACE) {
                $this->_classToken = $condPtr;
                break;
            }
        }

        // Find the first doc comment
        $commentStart      = ($phpcsFile->findPrevious(T_DOC_COMMENT, ($commentEnd - 1), null, true) + 1);
        $comment           = $phpcsFile->getTokensAsString($commentStart, ($commentEnd - $commentStart + 1));
        $this->_methodName = $phpcsFile->getDeclarationName($stackPtr);

        try {
            $this->_commentParser = new PHP_CodeSniffer_CommentParser_FunctionCommentParser($comment, $phpcsFile);
            $this->_commentParser->parse();
        } catch (PHP_CodeSniffer_CommentParser_ParserException $e) {
            $line = ($e->getLineWithinComment() + $commentStart);
            $phpcsFile->addError($e->getMessage(), $line);
            return;
        }

        $comment = $this->_commentParser->getComment();
        if (is_null($comment) === true) {
            $error = 'Function doc comment is empty';
            $phpcsFile->addError($error, $commentStart);
            return;
        }

        $tagOrder = $this->_commentParser->getTagOrders();
        $cnt      = -1;
        $con      = 'comment';
        foreach ($tagOrder as $tag => $content) {
            switch ($content) {
                case 'comment':
                    $cnt = $tag;
                    break;

                case 'param':
                    if (($con !== 'comment') and ($con !== 'param')) {
                        $error = 'The @param tag is in the wrong order; the tag follows the function comment';
                        $this->_currentFile->addError($error, ($commentStart + $tag));
                    }
                    break;

                case 'since':
                    if (($con !== 'comment') and ($con !== 'param')) {
                        $error = 'The @since tag is in the wrong order; the tag follows @param';
                        $this->_currentFile->addError($error, ($commentStart + $tag));
                    }
                    break;

                case 'see':
                    if (($con !== 'comment') and ($con !== 'param') and ($con !== 'since') and
                        ($con !== 'see')) {
                        $error = 'The @see tag is in the wrong order; the tag follows @since or @param';
                        $this->_currentFile->addError($error, ($commentStart + $tag));
                    }
                    break;

                case 'throws':
                    if (($con !== 'comment') and ($con !== 'param') and ($con !== 'since') and
                        ($con !== 'see') and ($con !== 'throws')) {
                        $error = 'The @throws tag is in the wrong order; the tag follows @see or @since or @param';
                        $this->_currentFile->addError($error, ($commentStart + $tag));
                    }
                    break;

                case 'return':
                    if (($con !== 'comment') and ($con !== 'param') and ($con !== 'since') and
                        ($con !== 'see') and ($con !== 'throws')) {
                        $error = 'The @throws tag is in the wrong order; the tag follows @see or @since or @param';
                        $this->_currentFile->addError($error, ($commentStart + $tag));
                    }
                    break;

                default:
                    $error = 'The @' . $content . ' tag is not allowed';
                    $this->_currentFile->addError($error, ($commentStart + $tag));
                    break;
            }

            $con = $content;
        }

        $this->_processParams($commentStart, $commentEnd);
        $this->_processSince($commentStart);
        $this->_processSees($commentStart);
        $this->_processThrows($commentStart);
        $this->_processReturn($commentStart, $commentEnd);

        // Check for a comment description
        $short = $comment->getShortComment();
        if (trim($short) === '') {
            $error = 'Missing short description in function doc comment';
            $phpcsFile->addError($error, $commentStart);
            return;
        }

        // No extra newline before short description
        $newlineCount = 0;
        $newlineSpan  = strspn($short, $phpcsFile->eolChar);
        if ($short !== '' and $newlineSpan > 0) {
            $line  = ($newlineSpan > 1) ? 'newlines' : 'newline';
            $error = "Extra $line found before function comment short description";
            $phpcsFile->addError($error, ($commentStart + 1));
        }

        $newlineCount = (substr_count($short, $phpcsFile->eolChar) + 1);

        // Exactly one blank line between short and long description
        $long = $comment->getLongComment();
        if (empty($long) === false) {
            $between        = $comment->getWhiteSpaceBetween();
            $newlineBetween = substr_count($between, $phpcsFile->eolChar);
            if ($newlineBetween !== 2) {
                $error = 'There must be exactly one blank line between descriptions in function comment';
                $phpcsFile->addError($error, ($commentStart + $newlineCount + 1));
            }

            $newlineCount += $newlineBetween;

            $testLong = trim($long);
            if (preg_match('|[A-Z]|', $testLong[0]) === 0) {
                $error = 'Function comment long description must start with a capital letter';
                $phpcsFile->addError($error, ($commentStart + $newlineCount));
            }
        }

        // Exactly one blank line before tags
        $params = $this->_commentParser->getTagOrders();
        if (count($params) > 1) {
            $newlineSpan = $comment->getNewlineAfter();
            if ($newlineSpan !== 2) {
                $error = 'There must be exactly one blank line before the tags in function comment';
                if ($long !== '') {
                    $newlineCount += (substr_count($long, $phpcsFile->eolChar) - $newlineSpan + 1);
                }

                $phpcsFile->addError($error, ($commentStart + $newlineCount));
                $short = rtrim($short, $phpcsFile->eolChar . ' ');
            }
        }

        $testShort = trim($short);
        $lastChar  = $testShort[(strlen($testShort) - 1)];
        if (preg_match('|[A-Z]|', $testShort[0]) === 0) {
            $error = 'Function comment short description must start with a capital letter';
            $phpcsFile->addError($error, ($commentStart + 1));
        }

        // Check for unknown/deprecated tags
        $unknownTags = $this->_commentParser->getUnknown();
        foreach ($unknownTags as $errorTag) {
            $error = "@$errorTag[tag] tag is not allowed in function comment";
            $phpcsFile->addWarning($error, ($commentStart + $errorTag['line']));
        }

    }

    /**
     * Process the since tag
     *
     * @param  integer $commentStart The position in the stack where the comment started
     * @return void
     */
    protected function _processSince($commentStart)
    {
        $since = $this->_commentParser->getSince();

        if ($since !== null) {
            $errorPos = ($commentStart + $since->getLine());
            $tagOrder = $this->_commentParser->getTagOrders();
            $firstTag = 0;

            while ($tagOrder[$firstTag] === 'comment' or $tagOrder[$firstTag] === 'param') {
                $firstTag++;
            }

            $cnt = 99;
            foreach ($tagOrder as $tag => $content) {
                if ($content === 'param') {
                    if ($cnt < $tag) {
                        $error = 'The @since tag is in the wrong order; the tag follows @param (if used)';
                        $this->_currentFile->addError($error, $errorPos);
                    }
                }

                if ($content === 'since') {
                    $cnt = $tag;
                }
            }

            $this->_tagIndex = $firstTag;
            $index           = array_keys($this->_commentParser->getTagOrders(), 'since');
            if (count($index) > 1) {
                $error = 'Only 1 @since tag is allowed in function comment';
                $this->_currentFile->addError($error, $errorPos);
                return;
            }

            if ($index[0] !== $firstTag) {
                $error = 'The @since tag is in the wrong order; the tag preceds @see (if used), '
                       . '@throws (if used) and @return';
                $this->_currentFile->addError($error, $errorPos);
            }

            $content = $since->getContent();
            if (empty($content) === true) {
                $error = 'Version number missing for @since tag in function comment';
                $this->_currentFile->addError($error, $errorPos);
                return;
            } else if ($content !== '%release_version%') {
                if (preg_match('/^([0-9]+)\.([0-9]+)\.([0-9]+)/', $content) === 0) {
                    $error = 'Expected version number to be in the form x.x.x in @since tag';
                    $this->_currentFile->addError($error, $errorPos);
                }
            }

            $spacing = substr_count($since->getWhitespaceBeforeContent(), ' ');
            if ($spacing !== 2) {
                $error  = '@since tag indented incorrectly; ';
                $error .= "expected 2 spaces but found $spacing.";
                $this->_currentFile->addError($error, $errorPos);
            }
        }

    }

    /**
     * Process the see tags
     *
     * @param  integer $commentStart The position in the stack where the comment started
     * @return void
     */
    protected function _processSees($commentStart)
    {
        $sees = $this->_commentParser->getSees();
        if (empty($sees) === false) {
            $tagOrder = $this->_commentParser->getTagOrders();
            $index    = array_keys($this->_commentParser->getTagOrders(), 'see');

            foreach ($sees as $i => $see) {
                $errorPos = ($commentStart + $see->getLine());
                $since    = array_keys($tagOrder, 'since');
                if (count($since) === 1 and $this->_tagIndex !== 0) {
                    $this->_tagIndex++;
                    if ($index[$i] !== $this->_tagIndex) {
                        $error = 'The @see tag is in the wrong order; the tag follows @since (if used) or @param';
                        $this->_currentFile->addError($error, $errorPos);
                    }
                }

                $content = $see->getContent();
                if (empty($content) === true) {
                    $error = 'Content missing for @see tag in function comment';
                    $this->_currentFile->addError($error, $errorPos);
                    continue;
                }

                $spacing = substr_count($see->getWhitespaceBeforeContent(), ' ');
                if ($spacing !== 4) {
                    $error  = '@see tag indented incorrectly; ';
                    $error .= "expected 4 spaces but found $spacing";
                    $this->_currentFile->addError($error, $errorPos);
                }
            }
        }

    }

    /**
     * Process the return comment of this function comment
     *
     * @param  integer $commentStart The position in the stack where the comment started
     * @param  integer $commentEnd   The position in the stack where the comment ended
     * @return void
     */
    protected function _processReturn($commentStart, $commentEnd)
    {
        // Skip constructor and destructor
        $className = '';
        if ($this->_classToken !== null) {
            $className = $this->_currentFile->getDeclarationName($this->_classToken);
            $className = strtolower(ltrim($className, '_'));
        }

        $methodName      = strtolower(ltrim($this->_methodName, '_'));
        $isSpecialMethod = ($this->_methodName === '__construct' or $this->_methodName === '__destruct');
        $return          = $this->_commentParser->getReturn();

        if ($methodName === $className or $isSpecialMethod !== false) {
            // No return tag for constructor and destructor
            if ($return !== null) {
                $errorPos = ($commentStart + $return->getLine());
                $error    = '@return tag is not required for constructor and destructor';
                $this->_currentFile->addError($error, $errorPos);
            }

            return;
        }

        if ($return === null) {
            $error = 'Missing @return tag in function comment';
            $this->_currentFile->addError($error, $commentEnd);
            return;
        }

        $tagOrder = $this->_commentParser->getTagOrders();
        $index    = array_keys($tagOrder, 'return');
        $errorPos = ($commentStart + $return->getLine());
        $content  = trim($return->getRawContent());

        if (count($index) > 1) {
            $error = 'Only 1 @return tag is allowed in function comment';
            $this->_currentFile->addError($error, $errorPos);
            return;
        }

        $since = array_keys($tagOrder, 'since');
        if (count($since) === 1 and $this->_tagIndex !== 0) {
            $this->_tagIndex++;
            if ($index[0] !== $this->_tagIndex) {
                $error = 'The @return tag is in the wrong order; the tag follows @see (if used) or @since';
                $this->_currentFile->addError($error, $errorPos);
            }
        }

        if (empty($content) === true) {
            $error = 'Return type missing for @return tag in function comment';
            $this->_currentFile->addError($error, $errorPos);
        } else {
            // Check return type (can be multiple, separated by '|')
            $typeNames      = explode('|', $content);
            $suggestedNames = array();
            foreach ($typeNames as $i => $typeName) {
                $suggestedName = PHP_CodeSniffer::suggestType($typeName);
                if (in_array($suggestedName, $suggestedNames) === false) {
                    $suggestedNames[] = $suggestedName;
                }
            }

            $suggestedType = implode('|', $suggestedNames);
            if ($content !== $suggestedType) {
                $error = "Function return type \"$content\" is invalid";
                $this->_currentFile->addError($error, $errorPos);
            }

            $tokens = $this->_currentFile->getTokens();

            // If the return type is void, make sure there is
            // no return statement in the function
            if ($content === 'void') {
                if (isset($tokens[$this->_functionToken]['scope_closer']) === true) {
                    $endToken = $tokens[$this->_functionToken]['scope_closer'];
                    $return   = $this->_currentFile->findNext(T_RETURN, $this->_functionToken, $endToken);
                    if ($return !== false) {
                        // If the function is not returning anything, just
                        // exiting, then there is no problem
                        $semicolon = $this->_currentFile->findNext(T_WHITESPACE, ($return + 1), null, true);
                        if ($tokens[$semicolon]['code'] !== T_SEMICOLON) {
                            $error = 'Function return type is void, but function contains return statement';
                            $this->_currentFile->addError($error, $errorPos);
                        }
                    }
                }
            } else {
                // If return type is not void, there needs to be a
                // returns statement somewhere in the function that
                // returns something
                if (isset($tokens[$this->_functionToken]['scope_closer']) === true) {
                    $endToken = $tokens[$this->_functionToken]['scope_closer'];
                    $return   = $this->_currentFile->findNext(T_RETURN, $this->_functionToken, $endToken);
                    if ($return === false) {
                        $error = 'Function return type is not void, but function has no return statement';
                        $this->_currentFile->addError($error, $errorPos);
                    } else {
                        $semicolon = $this->_currentFile->findNext(T_WHITESPACE, ($return + 1), null, true);
                        if ($tokens[$semicolon]['code'] === T_SEMICOLON) {
                            $error = 'Function return type is not void, but function is returning void here';
                            $this->_currentFile->addError($error, $return);
                        }
                    }
                }
            }
        }
    }

    /**
     * Process any throw tags that this function comment has
     *
     * @param  integer $commentStart The position in the stack where the comment started
     * @return void
     */
    protected function _processThrows($commentStart)
    {
        if (count($this->_commentParser->getThrows()) === 0) {
            return;
        }

        $tagOrder = $this->_commentParser->getTagOrders();
        $index    = array_keys($this->_commentParser->getTagOrders(), 'throws');

        foreach ($this->_commentParser->getThrows() as $i => $throw) {
            $exception = $throw->getValue();
            $content   = trim($throw->getComment());
            $errorPos  = ($commentStart + $throw->getLine());
            if (empty($exception) === true) {
                $error = 'Exception type and comment missing for @throws tag in function comment';
                $this->_currentFile->addError($error, $errorPos);
            } else if (empty($content) === true) {
                $error = 'Comment missing for @throws tag in function comment';
                $this->_currentFile->addError($error, $errorPos);
            } else {
                // Starts with a capital letter and ends with a fullstop
                $firstChar = $content{0};
                if (strtoupper($firstChar) !== $firstChar) {
                    $error = '@throws tag comment must start with a capital letter';
                    $this->_currentFile->addError($error, $errorPos);
                }
            }

            $since = array_keys($tagOrder, 'since');
            if (count($since) === 1 and $this->_tagIndex !== 0) {
                $this->_tagIndex++;
                if ($index[$i] !== $this->_tagIndex) {
                    $error = 'The @throws tag is in the wrong order; the tag follows @return';
                    $this->_currentFile->addError($error, $errorPos);
                }
            }
        }

    }

    /**
     * Process the function parameter comments
     *
     * @param  integer $commentStart The position in the stack where the comment started
     * @param  integer $commentEnd   The position in the stack where the comment ended
     * @return void
     */
    protected function _processParams($commentStart, $commentEnd)
    {
        $realParams  = $this->_currentFile->getMethodParameters($this->_functionToken);
        $params      = $this->_commentParser->getParams();
        $foundParams = array();

        if (empty($params) === false) {
            $isSpecialMethod = ($this->_methodName === '__construct' or $this->_methodName === '__destruct');
            if ((substr_count($params[(count($params) - 1)]->getWhitespaceAfter(),
                              $this->_currentFile->eolChar) !== 1) and ($isSpecialMethod === false)) {
                $error    = 'No empty line after last parameter comment allowed';
                $errorPos = ($params[(count($params) - 1)]->getLine() + $commentStart);
                $this->_currentFile->addError($error, ($errorPos + 1));
            }

            // Parameters must appear immediately after the comment
            if ($params[0]->getOrder() !== 2) {
                $error    = 'Parameters must appear immediately after the comment';
                $errorPos = ($params[0]->getLine() + $commentStart);
                $this->_currentFile->addError($error, $errorPos);
            }

            $previousParam      = null;
            $spaceBeforeVar     = 10000;
            $spaceBeforeComment = 10000;
            $longestType        = 0;
            $longestVar         = 0;
            if (count($this->_commentParser->getThrows()) !== 0) {
                $isSpecialMethod = false;
            }

            foreach ($params as $param) {
                $paramComment = trim($param->getComment());
                $errorPos     = ($param->getLine() + $commentStart);

                // Make sure that there is only one or two space before the var type
                if (($isSpecialMethod === false) and ($param->getWhitespaceBeforeType() !== '  ')) {
                    $error = 'Expected 2 spaces before variable type';
                    $this->_currentFile->addError($error, $errorPos);
                }

                if (($isSpecialMethod === true) and ($param->getWhitespaceBeforeType() !== ' ')) {
                    $error = 'Expected 1 space before variable type';
                    $this->_currentFile->addError($error, $errorPos);
                }

                $spaceCount = substr_count($param->getWhitespaceBeforeVarName(), ' ');
                if ($spaceCount < $spaceBeforeVar) {
                    $spaceBeforeVar = $spaceCount;
                    $longestType    = $errorPos;
                }

                $spaceCount = substr_count($param->getWhitespaceBeforeComment(), ' ');

                if ($spaceCount < $spaceBeforeComment and $paramComment !== '') {
                    $spaceBeforeComment = $spaceCount;
                    $longestVar         = $errorPos;
                }

                // Make sure they are in the correct order, and have the correct name
                $pos       = $param->getPosition();
                $paramName = ($param->getVarName() !== '') ? $param->getVarName() : '[ UNKNOWN ]';

                if ($previousParam !== null) {
                    $previousName = ($previousParam->getVarName() !== '') ? $previousParam->getVarName() : 'UNKNOWN';

                    // Check to see if the parameters align properly
                    if ($param->alignsVariableWith($previousParam) === false) {
                        $error = 'The variable names for parameters ' . $previousName . ' (' . ($pos - 1) . ') and '
                               . $paramName . ' (' . $pos . ') do not align';
                        $this->_currentFile->addError($error, $errorPos);
                    }

                    if ($param->alignsCommentWith($previousParam) === false) {
                        $error = 'The comments for parameters ' . $previousName . ' (' . ($pos - 1) . ') and '
                               . $paramName . ' (' . $pos . ') do not align';
                        $this->_currentFile->addError($error, $errorPos);
                    }
                }

                // Variable must be one of the supported standard type
                $typeNames = explode('|', $param->getType());
                foreach ($typeNames as $typeName) {
                    $suggestedName = PHP_CodeSniffer::suggestType($typeName);
                    if ($typeName !== $suggestedName) {
                        $error = "Expected \"$suggestedName\"; found \"$typeName\" for $paramName at position $pos";
                        $this->_currentFile->addError($error, $errorPos);
                        continue;
                    }

                    if (count($typeNames) !== 1) {
                        continue;
                    }

                    // Check type hint for array and custom type
                    $suggestedTypeHint = '';
                    if (strpos($suggestedName, 'array') !== false) {
                        $suggestedTypeHint = 'array';
                    } else if (in_array($typeName, PHP_CodeSniffer::$allowedTypes) === false) {
                        $suggestedTypeHint = $suggestedName;
                    }

                    if ($suggestedTypeHint !== '' and isset($realParams[($pos - 1)]) === true) {
                        $typeHint = $realParams[($pos - 1)]['type_hint'];
                        if ($typeHint === '') {
                            $error = "Type hint \"$suggestedTypeHint\" missing for $paramName at position $pos";
                            $this->_currentFile->addError($error, ($commentEnd + 2));
                        } else if ($typeHint !== $suggestedTypeHint) {
                            $error = "Expected type hint \"$suggestedTypeHint\"; found \"$typeHint\""
                                   . " for $paramName at position $pos";
                            $this->_currentFile->addError($error, ($commentEnd + 2));
                        }
                    } else if ($suggestedTypeHint === '' and isset($realParams[($pos - 1)]) === true) {
                        $typeHint = $realParams[($pos - 1)]['type_hint'];
                        if ($typeHint !== '') {
                            $error = "Unknown type hint \"$typeHint\" found for $paramName at position $pos";
                            $this->_currentFile->addError($error, ($commentEnd + 2));
                        }
                    }
                }

                // Make sure the names of the parameter comment matches the
                // actual parameter
                if (isset($realParams[($pos - 1)]) === true) {
                    $realName      = $realParams[($pos - 1)]['name'];
                    $foundParams[] = $realName;

                    // Append ampersand to name if passing by reference
                    if ($realParams[($pos - 1)]['pass_by_reference'] === true) {
                        $realName = '&' . $realName;
                    }

                    if ($realName !== $param->getVarName()) {
                        $error  = 'Doc comment var "' . $paramName;
                        $error .= '" does not match actual variable name "' . $realName;
                        $error .= '" at position ' . $pos;
                        $this->_currentFile->addError($error, $errorPos);
                    }
                } else {
                    // We must have an extra parameter comment
                    $error = 'Superfluous doc comment at position ' . $pos;
                    $this->_currentFile->addError($error, $errorPos);
                }

                if ($param->getVarName() === '') {
                    $error = 'Missing parameter name at position ' . $pos;
                     $this->_currentFile->addError($error, $errorPos);
                }

                if ($param->getType() === '') {
                    $error = 'Missing type at position ' . $pos;
                    $this->_currentFile->addError($error, $errorPos);
                }

                if ($paramComment === '') {
                    $error = 'Missing comment for param "' . $paramName . '" at position ' . $pos;
                    $this->_currentFile->addError($error, $errorPos);
                } else {
                    // Param comments must start with a capital letter
                    $firstChar = $paramComment{0};
                    if ((preg_match('|[A-Z]|', $firstChar) === 0) and ($firstChar !== '(')) {
                        $error = 'Param comment must start with a capital letter';
                        $this->_currentFile->addError($error, $errorPos);
                    }

                    // Check if optional params include (Optional) within their description
                    $functionBegin = $this->_currentFile->findNext(array(T_FUNCTION), $commentStart);
                    $functionName  = $this->_currentFile->findNext(array(T_STRING), $functionBegin);
                    $openBracket   = $this->_tokens[$functionBegin]['parenthesis_opener'];
                    $closeBracket  = $this->_tokens[$functionBegin]['parenthesis_closer'];
                    $nextParam     = $this->_currentFile->findNext(T_VARIABLE, ($openBracket + 1), $closeBracket);
                    while ($nextParam !== false) {
                        $nextToken = $this->_currentFile->findNext(T_WHITESPACE, ($nextParam + 1), ($closeBracket + 1), true);
                        if (($nextToken === false) and ($this->_tokens[($nextParam + 1)]['code'] === T_CLOSE_PARENTHESIS)) {
                            break;
                        }

                        $nextCode = $this->_tokens[$nextToken]['code'];
                        $arg      = $this->_tokens[$nextParam]['content'];
                        if (($nextCode === T_EQUAL) and ($paramName === $arg)) {
                            if (substr($paramComment, 0, 11) !== '(Optional) ') {
                                $error = "Optional param comment for '$paramName' must start with '(Optional)'";
                                $this->_currentFile->addError($error, $errorPos);
                            } else if (preg_match('|[A-Z]|', $paramComment{11}) === 0) {
                                $error = 'Param comment must start with a capital letter';
                                $this->_currentFile->addError($error, $errorPos);
                            }
                        }

                        $nextParam = $this->_currentFile->findNext(T_VARIABLE, ($nextParam + 1), $closeBracket);
                    }
                }

                $previousParam = $param;

            }

            if ($spaceBeforeVar !== 1 and $spaceBeforeVar !== 10000 and $spaceBeforeComment !== 10000) {
                $error = 'Expected 1 space after the longest type';
                $this->_currentFile->addError($error, $longestType);
            }

            if ($spaceBeforeComment !== 1 and $spaceBeforeComment !== 10000) {
                $error = 'Expected 1 space after the longest variable name';
                $this->_currentFile->addError($error, $longestVar);
            }
        }

        $realNames = array();
        foreach ($realParams as $realParam) {
            $realNames[] = $realParam['name'];
        }

        // Report missing comments
        $diff = array_diff($realNames, $foundParams);
        foreach ($diff as $neededParam) {
            if (count($params) !== 0) {
                $errorPos = ($params[(count($params) - 1)]->getLine() + $commentStart);
            } else {
                $errorPos = $commentStart;
            }

            $error = 'Doc comment for "' . $neededParam . '" missing';
            $this->_currentFile->addError($error, $errorPos);
        }
    }
}
