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
 * Zend_Sniffs_Arrays_ArrayDeclarationSniff
 *
 * A test to ensure that arrays conform to the array coding standard
 *
 * @category  Zend
 * @package   Zend_CodingStandard
 * @copyright Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Sniffs_Arrays_ArrayDeclarationSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for
     *
     * @return array
     */
    public function register()
    {
        return array(T_ARRAY);
    }

    /**
     * Processes this sniff, when one of its tokens is encountered
     *
     * @param  PHP_CodeSniffer_File $phpcsFile The current file being checked
     * @param  integer              $stackPtr  The position of the current token in the stack passed in $tokens
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Array keyword should be lower case
        if (strtolower($tokens[$stackPtr]['content']) !== $tokens[$stackPtr]['content']) {
            $error = 'Array keyword should be lower case; expected "array" but found "'
                   . $tokens[$stackPtr]['content'] . '"';
            $phpcsFile->addError($error, $stackPtr);
        }

        $arrayStart   = $tokens[$stackPtr]['parenthesis_opener'];
        $arrayEnd     = $tokens[$arrayStart]['parenthesis_closer'];
        $keywordStart = $tokens[$stackPtr]['column'];

        if ($arrayStart !== ($stackPtr + 1)) {
            $error = 'There must be no space between the Array keyword and the opening parenthesis';
            $phpcsFile->addError($error, $stackPtr);
        }

        // Check for empty arrays
        $content = $phpcsFile->findNext(array(T_WHITESPACE), ($arrayStart + 1), ($arrayEnd + 1), true);
        if ($content === $arrayEnd) {
            // Empty array, but if the brackets aren't together, there's a problem
            if (($arrayEnd - $arrayStart) !== 1) {
                $error = 'Empty array declaration must have no space between the parentheses';
                $phpcsFile->addError($error, $stackPtr);

                // We can return here because there is nothing else to check. All code
                // below can assume that the array is not empty
                return;
            }
        }

        // @todo: Deeper Array checking
    }
}
