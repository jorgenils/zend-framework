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
 * Zend_Sniffs_Debug_CodeAnalyzerSniff
 *
 * Runs the Zend Code Analyzer (from Zend Studio) on the file
 *
 * @category  Zend
 * @package   Zend_CodingStandard
 * @copyright Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Sniffs_Debug_CodeAnalyzerSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array
     */
    public function register()
    {
        return array(T_OPEN_TAG);

    }

    /**
     * Processes the tokens that this sniff is interested in
     *
     * @param  PHP_CodeSniffer_File $phpcsFile The file where the token was found
     * @param  integer              $stackPtr  The position in the stack where the token was found
     * @throws PHP_CodeSniffer_Exception On error initialising Zend_Code_Analyser
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        // Because we are analyzing the whole file in one step, execute this method
        // only on first occurence of a T_OPEN_TAG
        $prevOpenTag = $phpcsFile->findPrevious(T_OPEN_TAG, ($stackPtr - 1));
        if ($prevOpenTag !== false) {
            return;
        }

        $fileName = $phpcsFile->getFilename();

        $analyzerPath = PHP_CodeSniffer::getConfigData('zend_ca_path');
        if (is_null($analyzerPath) === true) {
            return;
        }

        // In the command, 2>&1 is important because the code analyzer sends its
        // findings to stderr. $output normally contains only stdout, so using 2>&1
        // will pipe even stderr to stdout
        $cmd = $analyzerPath . ' ' . $fileName . ' 2>&1';

        // There is the possibility to pass "--ide" as an option to the analyzer
        // This would result in an output format which would be easier to parse
        // The problem here is that no cleartext error messages are returnwd; only
        // error-code-labels. So for a start we go for cleartext output
        $exitCode = exec($cmd, $output, $retval);

        // The last line of $output is $exitCode if no error occures, on error it
        // is numeric. Try to handle various error conditions and provide useful error reporting
        if (is_numeric($exitCode) === true and $exitCode > 0) {
            if (is_array($output) === true) {
                $msg = join('\n', $output);
            }

            throw new PHP_CodeSniffer_Exception("Failed invoking ZendCodeAnalyzer, exitcode was [$exitCode], "
                                              . "retval was [$retval], output was [$msg]");
        }

        if (is_array($output) === true) {
            $tokens = $phpcsFile->getTokens();

            foreach ($output as $finding) {
                // The first two lines of analyzer output contain
                // something like this:
                // > Zend Code Analyzer 1.2.2
                // > Analyzing <filename>...
                // So skip these...
                $res = eregi('^.+\(line ([0-9]+)\):(.+)$', $finding, $regs);
                if ($regs === null or $res === false) {
                    continue;
                }

                // Find the token at the start of the line
                $lineToken = null;
                foreach ($tokens as $ptr => $info) {
                    if ($info['line'] === $regs[1]) {
                        $lineToken = $ptr;
                        break;
                    }
                }

                if ($lineToken !== null) {
                    $phpcsFile->addWarning(trim($regs[2]), $ptr);
                }
            }
        }
    }
}
