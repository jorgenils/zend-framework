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

/**
 * Zend_Sniffs_WhiteSpace_ControlStructureSpacingSniff
 *
 * Checks that any array declarations are lower case
 *
 * @category  Zend
 * @package   Zend_CodingStandard
 * @copyright Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Sniffs_WhiteSpace_ControlStructureSpacingSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for
     *
     * @return array
     */
    public function register()
    {
        return array(T_IF, T_WHILE, T_FOREACH, T_FOR, T_SWITCH, T_DO, T_ELSE, T_ELSEIF);

    }

    /**
     * Processes this test, when one of its tokens is encountered
     *
     * @param  PHP_CodeSniffer_File $phpcsFile The file being scanned
     * @param  integer              $stackPtr  The position of the current token in the stack passed in $tokens
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (isset($tokens[$stackPtr]['scope_closer']) === false) {
            return;
        }

        $scopeCloser = $tokens[$stackPtr]['scope_closer'];
        $scopeOpener = $tokens[$stackPtr]['scope_opener'];

        $firstContent = $phpcsFile->findNext(T_WHITESPACE, ($scopeOpener + 1), null, true);
        $bracket      = $phpcsFile->findNext(T_OPEN_CURLY_BRACKET, $scopeOpener, null, true);
        if ($bracket > $scopeCloser) {
            $bracket = $stackPtr;
        }

        if ($tokens[$firstContent]['line'] !== ($tokens[$bracket]['line'] + 1)) {
            $error = 'Blank line found at start of control structure';
            $phpcsFile->addError($error, $scopeOpener);
        }

        $trailingContent = $phpcsFile->findNext(T_WHITESPACE, ($scopeCloser + 1), null, true);
        if ($tokens[$trailingContent]['code'] === T_ELSE) {
            if ($tokens[$stackPtr]['code'] === T_IF) {
                // IF with ELSE
                return;
            }
        }

        if ($tokens[$trailingContent]['code'] === T_COMMENT) {
            if ($tokens[$trailingContent]['line'] === $tokens[$scopeCloser]['line']) {
                if (substr($tokens[$trailingContent]['content'], 0, 5) === '//end') {
                    // There is an end comment, so we have to get the next piece of content
                    $trailingContent = $phpcsFile->findNext(T_WHITESPACE, ($trailingContent + 1), null, true);
                }
            }
        }

        if ($tokens[$trailingContent]['code'] === T_BREAK) {
            // If this BREAK is closing a CASE, we don't need the
            // blank line after this control structure
            if (isset($tokens[$trailingContent]['scope_condition']) === true) {
                $condition = $tokens[$trailingContent]['scope_condition'];
                if ($tokens[$condition]['code'] === T_CASE or $tokens[$condition]['code'] === T_DEFAULT) {
                    return;
                }
            }
        }

        if ($tokens[$trailingContent]['code'] === T_CLOSE_TAG) {
            // At the end of the script or embedded code
            return;
        }

        if ($tokens[$trailingContent]['code'] === T_CLOSE_CURLY_BRACKET) {
            // Another control structure's closing brace.
            if (isset($tokens[$trailingContent]['scope_condition']) === true) {
                $owner = $tokens[$trailingContent]['scope_condition'];
                if ($tokens[$owner]['code'] === T_FUNCTION) {
                    // The next content is the closing brace of a function
                    // so normal function rules apply and we can ignore it
                    return;
                }
            }

            if ($tokens[$trailingContent]['line'] !== ($tokens[$scopeCloser]['line'] + 1)) {
                $error = 'Blank line found after control structure';
                $phpcsFile->addError($error, $scopeCloser);
            }
        } else {
            if ($tokens[$trailingContent]['line'] === ($tokens[$scopeCloser]['line'] + 1)) {
                $error = 'No blank line found after control structure';
                $phpcsFile->addError($error, $scopeCloser);
            }
        }
    }
}
