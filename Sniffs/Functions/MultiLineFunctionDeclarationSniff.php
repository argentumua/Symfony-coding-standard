<?php
/**
 * Symfony_Sniffs_Functions_MultiLineFunctionDeclarationSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

if (class_exists('PEAR_Sniffs_Functions_FunctionDeclarationSniff', true) === false) {
    $error = 'Class PEAR_Sniffs_Functions_FunctionDeclarationSniff not found';
    throw new PHP_CodeSniffer_Exception($error);
}

/**
 * Symfony_Sniffs_Functions_MultiLineFunctionDeclarationSniff.
 *
 * Ensure single and multi-line function declarations are defined correctly.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class Symfony_Sniffs_Functions_MultiLineFunctionDeclarationSniff extends PEAR_Sniffs_Functions_FunctionDeclarationSniff
{


    /**
     * Processes mutli-line declarations.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     * @param array                $tokens    The stack of tokens that make up
     *                                        the file.
     *
     * @return void
     */
    public function processMultiLineDeclaration(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $tokens)
    {
        // We need to work out how far indented the function
        // declaration itself is, so we can work out how far to
        // indent parameters.
        $functionIndent = 0;
        for ($i = ($stackPtr - 1); $i >= 0; $i--) {
            if ($tokens[$i]['line'] !== $tokens[$stackPtr]['line']) {
                $i++;
                break;
            }
        }

        if ($tokens[$i]['code'] === T_WHITESPACE) {
            $functionIndent = strlen($tokens[$i]['content']);
        }

        // The closing parenthesis must be on a new line, even
        // when checking abstract function definitions.
        $closeBracket = $tokens[$stackPtr]['parenthesis_closer'];
        $prev = $phpcsFile->findPrevious(
            T_WHITESPACE,
            ($closeBracket - 1),
            null,
            true
        );

        if ($tokens[$closeBracket]['line'] !== $tokens[$tokens[$closeBracket]['parenthesis_opener']]['line']) {
            if ($tokens[$prev]['line'] === $tokens[$closeBracket]['line']) {
                $error = 'The closing parenthesis of a multi-line function declaration must be on a new line';
                $phpcsFile->addError($error, $closeBracket, 'CloseBracketLine');
            }
        }

        // If this is a closure and is using a USE statement, the closing
        // parenthesis we need to look at from now on is the closing parenthesis
        // of the USE statement.
        if ($tokens[$stackPtr]['code'] === T_CLOSURE) {
            $use = $phpcsFile->findNext(T_USE, ($closeBracket + 1), $tokens[$stackPtr]['scope_opener']);
            if ($use !== false) {
                $open         = $phpcsFile->findNext(T_OPEN_PARENTHESIS, ($use + 1));
                $closeBracket = $tokens[$open]['parenthesis_closer'];

                $prev = $phpcsFile->findPrevious(
                    T_WHITESPACE,
                    ($closeBracket - 1),
                    null,
                    true
                );

                if ($tokens[$closeBracket]['line'] !== $tokens[$tokens[$closeBracket]['parenthesis_opener']]['line']) {
                    if ($tokens[$prev]['line'] === $tokens[$closeBracket]['line']) {
                        $error = 'The closing parenthesis of a multi-line use declaration must be on a new line';
                        $phpcsFile->addError($error, $closeBracket, 'CloseBracketLine');
                    }
                }
            }//end if
        }//end if

        // Each line between the parenthesis should be indented 2 spaces.
        $openBracket  = $tokens[$stackPtr]['parenthesis_opener'];
        $lastLine     = $tokens[$openBracket]['line'];
        for ($i = ($openBracket + 1); $i < $closeBracket; $i++) {
            if ($tokens[$i]['line'] !== $lastLine) {
                if ($i === $tokens[$stackPtr]['parenthesis_closer']
                    || ($tokens[$i]['code'] === T_WHITESPACE
                    && ($i + 1) === $tokens[$stackPtr]['parenthesis_closer'])
                ) {
                    // Closing braces need to be indented to the same level
                    // as the function.
                    $expectedIndent = $functionIndent;
                } else {
                    $expectedIndent = ($functionIndent + 2);
                }

                // We changed lines, so this should be a whitespace indent token.
                if ($tokens[$i]['code'] !== T_WHITESPACE) {
                    $foundIndent = 0;
                } else {
                    $foundIndent = strlen($tokens[$i]['content']);
                }

                if ($expectedIndent !== $foundIndent) {
                    $error = 'Multi-line function declaration not indented correctly; expected %s spaces but found %s';
                    $data  = array(
                              $expectedIndent,
                              $foundIndent,
                             );
                    $phpcsFile->addError($error, $i, 'Indent', $data);
                }

                $lastLine = $tokens[$i]['line'];
            }//end if

            if ($tokens[$i]['code'] === T_ARRAY) {
                // Skip arrays as they have their own indentation rules.
                $i        = $tokens[$i]['parenthesis_closer'];
                $lastLine = $tokens[$i]['line'];
                continue;
            }
        }//end for

        if (isset($tokens[$stackPtr]['scope_opener']) === true) {
            // The openning brace needs to be one space away
            // from the closing parenthesis.
            $next = $tokens[($closeBracket + 1)];
            if ($next['code'] !== T_WHITESPACE) {
                $length = 0;
            } else if ($next['content'] === $phpcsFile->eolChar) {
                $length = -1;
            } else {
                $length = strlen($next['content']);
            }

            if ($length !== -1) {
                $data = array($length);
                $code = 'SpaceBeforeOpenBrace';

                $error = 'There must be a newline between the closing parenthesis and the opening brace of a multi-line function declaration; found ';
                if ($length === -1) {
                    $error .= 'newline';
                    $code   = 'NewlineBeforeOpenBrace';
                } else {
                    $error .= '%s spaces';
                }

                $phpcsFile->addError($error, ($closeBracket + 1), $code, $data);
                return;
            }

            // And just in case they do something funny before the brace...
            $next = $phpcsFile->findNext(
                T_WHITESPACE,
                ($closeBracket + 1),
                null,
                true
            );

            if ($next !== false && $tokens[$next]['code'] !== T_OPEN_CURLY_BRACKET) {
                $error = 'There must be a single space between the closing parenthesis and the opening brace of a multi-line function declaration';
                $phpcsFile->addError($error, $next, 'NoSpaceBeforeOpenBrace');
            }
        }//end if

    }//end processMultiLineDeclaration()


}//end class

?>
