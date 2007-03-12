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
 * @package    Zend_Locale
 * @subpackage Format
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id$
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/**
 * include needed classes
 */
require_once 'Zend/Locale/Data.php';
require_once 'Zend/Locale/Exception.php';
require_once 'Zend/Locale/Math.php';


/**
 * @category   Zend
 * @package    Zend_Locale
 * @subpackage Format
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Locale_Format
{
    private static $_Options = array('format'    => null,
                                     'type'      => 'iso',
                                     'fixdate'   => false,
                                     'locale'    => null,
                                     'precision' => null);

    private static $_signs = array(
        'Default'=>array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9'), // Default == Latin
        'Latin'=> array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9'), // Latin == Default
        'Arab' => array( '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'), // 0660 - 0669 arabic
        'Deva' => array( '०', '१', '२', '३', '४', '५', '६', '७', '८', '९'), // 0966 - 096F devanagari
        'Beng' => array( '০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'), // 09E6 - 09EF bengali
        'Guru' => array( '੦', '੧', '੨', '੩', '੪', '੫', '੬', '੭', '੮', '੯'), // 0A66 - 0A6F gurmukhi
        'Gujr' => array( '૦', '૧', '૨', '૩', '૪', '૫', '૬', '૭', '૮', '૯'), // 0AE6 - 0AEF gujarati
        'Orya' => array( '୦', '୧', '୨', '୩', '୪', '୫', '୬', '୭', '୮', '୯'), // 0B66 - 0B6F orija
        'Taml' => array( '௦', '௧', '௨', '௩', '௪', '௫', '௬', '௭', '௮', '௯'), // 0BE6 - 0BEF tamil
        'Telu' => array( '౦', '౧', '౨', '౩', '౪', '౫', '౬', '౭', '౮', '౯'), // 0C66 - 0C6F telugu
        'Knda' => array( '೦', '೧', '೨', '೩', '೪', '೫', '೬', '೭', '೮', '೯'), // 0CE6 - 0CEF kannada
        'Mlym' => array( '൦', '൧', '൨', '൩', '൪', '൫', '൬', '൭', '൮', '൯ '), // 0D66 - 0D6F malayalam
        'Tale' => array( '๐', '๑', '๒', '๓', '๔', '๕', '๖', '๗', '๘', '๙ '), // 0E50 - 0E59 thai
        'Laoo' => array( '໐', '໑', '໒', '໓', '໔', '໕', '໖', '໗', '໘', '໙'), // 0ED0 - 0ED9 lao
        'Tibt' => array( '༠', '༡', '༢', '༣', '༤', '༥', '༦', '༧', '༨', '༩ '), // 0F20 - 0F29 tibetan
        'Mymr' => array( '၀', '၁', '၂', '၃', '၄', '၅', '၆', '၇', '၈', '၉'), // 1040 - 1049 myanmar
        'Khmr' => array( '០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩'), // 17E0 - 17E9 khmer
        'Mong' => array( '᠐', '᠑', '᠒', '᠓', '᠔', '᠕', '᠖', '᠗', '᠘', '᠙'), // 1810 - 1819 mongolian
        'Limb' => array( '᥆', '᥇', '᥈', '᥉', '᥊', '᥋', '᥌', '᥍', '᥎', '᥏'), // 1946 - 194F limbu
        'Talu' => array( '᧐', '᧑', '᧒', '᧓', '᧔', '᧕', '᧖', '᧗', '᧘', '᧙'), // 19D0 - 19D9 tailue
        'Bali' => array( '᭐', '᭑', '᭒', '᭓', '᭔', '᭕', '᭖', '᭗', '᭘', '᭙'), // 1B50 - 1B59 balinese
        'Nkoo' => array( '߀', '߁', '߂', '߃', '߄', '߅', '߆', '߇', '߈', '߉')  // 07C0 - 07C9 nko
    );

    /**
     * Sets class wide options, if no option was given, the actual set options will be returned
     * The 'precision' option of a value is used to truncate or stretch extra digits. -1 means not to touch the extra digits.
     * The 'locale' option helps when parsing numbers and dates using separators and month names.
     * The date format 'type' option selects between CLDR/ISO date format specifier tokens and PHP's date() tokens.
     * The 'fixdate' option enables or disables heuristics that attempt to correct invalid dates.
     *
     * @param  array  $options  Array of options, keyed by option name: type = 'iso' | 'php', fixdate = true | false,
     *                          locale = Zend_Locale | locale string, precision = whole number between -1 and 30
     * @throws Zend_Locale_Exception
     * @return Options array if no option was given 
     */
    public static function setOptions(array $options = array())
    {
        if (empty($options)) {
            return self::$_Options;
        }
        foreach ($options as $name => $value) {
            $name  = strtolower($name);
            $value = strtolower($value);

            if (array_key_exists($name, self::$_Options)) {
                switch($name) {
                    case 'format' :
                        $type = gettype($value);
                        if ($type !== 'string') {
                            throw new Zend_Locale_Exception("Unknown format type '$type'. "
                                . "Format '$value' must be a valid ISO or PHP date format string.");
                        }
                        // $value could now be checked using the same code near the top of _parseDate()
                        break;
                    case 'type' :
                        if (($value != 'php') && ($value != 'iso')) {
                            throw new Zend_Locale_Exception("Unknown date format type '$value'. Only 'iso' and 'php'"
                               . " are supported.");
                        }
                        break;
                    case 'fixdate' :
                        if (($value !== true) && ($value !== false)) {
                            throw new Zend_Locale_Exception("Enabling correction of dates must be either true or false"
                                . "(fixdate='$value').");
                        }
                        break;
                    case 'locale' :
                        if (!Zend_Locale::isLocale($value)) {
                            throw new Zend_Locale_Exception("'" .
                                (gettype($value) === object ? get_class($value) : $value) . "' is not a known locale.");
                        }
                        break;
                    case 'precision' :
                        if (($value < -1) || ($value > 30)) {
                            throw new Zend_Locale_Exception("'$value' precision is not a whole number less than 30.");
                        }
                        break;
                    default :
                        break;
                }
                self::$_Options[$name] = $value;
            }
            else {
                throw new Zend_Locale_Exception("Unknown option: '$name' = '$value'");
            }
        }
        return true;
    }

    /**
     * Changes the numbers/digits within a given string from one script to another
     * 'Decimal' representated the stardard numbers 0-9, if a script does not exist
     * an exception will be thrown.
     *
     * Examples for conversion from Arabic to Latin numerals:
     *   convertNumerals('١١٠ Tests', 'Arab'); -> returns '100 Tests'
     * Example for conversion from Latin to Arabic numerals:
     *   convertNumerals('100 Tests', 'Latin', 'Arab'); -> returns '١١٠ Tests'
     * 
     * @param  string  $input  String to convert
     * @param  string  $from   Script to parse, see {@link Zend_Locale::getScriptList()} for details.
     * @param  string  $to     OPTIONAL Script to convert to
     * @return string  Returns the converted input
     * @throws Zend_Locale_Exception
     */
    public static function convertNumerals($input, $from, $to = null)
    {
        if (!array_key_exists($from, self::$_signs)) {
            throw new Zend_Locale_Exception("script ($from) is no known script, use 'Latin' for 0-9");
        }
        if (($to !== null) and (!array_key_exists($to, self::$_signs))) {
            throw new Zend_Locale_Exception("script ($to) is no known script, use 'Latin' for 0-9");
        }
        
        if (isset(self::$_signs[$from])) {
            for ($X = 0; $X < 10; ++$X) {
                $source[$X + 10] = "/" . self::$_signs[$from][$X] . "/u";
            }
        }

        if (isset(self::$_signs[$to])) {
            for ($X = 0; $X < 10; ++$X) {
                $dest[$X + 10] = self::$_signs[$to][$X];
            }
        } else {
            for ($X = 0; $X < 10; ++$X) {
                $dest[$X + 10] = $X;
            }
        }

        return preg_replace($source, $dest, $input);
    }

    /**
     * Returns the first found number from an string
     * Parsing depends on given locale (grouping and decimal)
     * 
     * Examples for input:
     * '  2345.4356,1234' = 23455456.1234
     * '+23,3452.123' = 233452.123
     * ' 12343 ' = 12343
     * '-9456km' = -9456
     * '0' = 0
     * '(-){0,1}(\d+(\.){0,1})*(\,){0,1})\d+'
     * '١١٠ Tests' = 110  call: getNumber($string, 'Arab');
     * 
     * @param  string         $input    Input string to parse for numbers
     * @param  array          $options  Options: locale, precision. See {@link setOptions()} for details.
     * @return integer|string Returns the extracted number
     */
    public static function getNumber($input, array $options = array())
    {
        $options = array_merge(self::$_Options, $options);
        if (!is_string($input)) {
            return $input;
        }

        if (Zend_Locale::isLocale($options['precision'])) {
            $options['locale'] = $options['precision'];
            $options['precision'] = null;
        }

        // Get correct signs for this locale
        $symbols = Zend_Locale_Data::getContent($options['locale'],'numbersymbols');

        // Parse input locale aware
        $regex = '/(' . $symbols['minus'] . '){0,1}(\d+(\\' . $symbols['group'] . '){0,1})*(\\' .
                        $symbols['decimal'] . '){0,1}\d+/';
        preg_match($regex, $input, $found);
        if (!isset($found[0]))
            throw new Zend_Locale_Exception('No value in ' . $input . ' found');
        $found = $found[0];

        // Change locale input to be standard number
        if ($symbols['minus'] != "-")
            $found = strtr($found,$symbols['minus'],'-');
        $found = str_replace($symbols['group'],'', $found);

        // Do precision
        if (strpos($found, $symbols['decimal']) !== false) {
            if ($symbols['decimal'] != '.') {
                $found = str_replace($symbols['decimal'], ".", $found);
            }

            $pre = substr($found, strpos($found, '.') + 1);
            if ($options['precision'] === null) {
                $options['precision'] = strlen($pre);
            }

            if (strlen($pre) >= $options['precision']) {
                $found = substr($found, 0, strlen($found) - strlen($pre) + $options['precision']);
            }
        }

        return $found;
    }

    /**
     * Returns a self formatted number
     * The seperation and fraction sign is used from the set locale
     * ##0.#  -> 12345.12345 -> 12345.12345
     * ##0.00 -> 12345.12345 -> 12345.12
     * ##,##0.00 -> 12345.12345 -> 12,345.12
     * 
     * @param   string  $value    Number to localize
     * @param   array   $options  Options: locale, precision. See {@link setOptions()} for details.
     * @return  string            Locale formatted number
     */
    public static function toNumber($value, array $options = array())
    {
        $options = array_merge(self::$_Options, $options);
        $format  = Zend_Locale_Data::getContent($options['locale'], 'decimalnumberformat');
        $options['format']  = $format['default'];

        // seperate negative format pattern when avaiable 
        if (iconv_strpos($options['format'], ';') !== false) {
            if (call_user_func(Zend_Locale_Math::$comp, $value, 0) < 0) {
                $options['format'] = iconv_substr($options['format'], iconv_strpos($options['format'], ';') + 1);
            } else {
                $options['format'] = iconv_substr($options['format'], 0, iconv_strpos($options['format'], ';'));
            }
        }

        if (is_int($options['precision'])) {
            $rest   = substr(substr($options['format'], strpos($options['format'], '.') + 1), -1, 1);
            $options['format'] = substr($options['format'], 0, strpos($options['format'], '.'));
            if ((int) $options['precision'] > 0) {
                $options['format'] .= ".";
                $options['format'] = str_pad($options['format'], strlen($options['format']) + $options['precision'], "0");
                $value = round($value, $options['precision']);
            }
            if (($rest != '0') and ($rest != '#')) {
                $options['format'] .= $rest;
            }
            if ($options['precision'] == -1) {
                $value = round($value);
            }
        }
        
        return self::toNumberFormat($value, $options);
    }
        
    /**
     * Returns a self formatted number
     * The seperation and fraction sign is used from the set locale
     * ##0.#  -> 12345.12345 -> 12345.12345
     * ##0.00 -> 12345.12345 -> 12345.12
     * ##,##0.00 -> 12345.12345 -> 12,345.12
     * 
     * @param   string  $input    Localized number string
     * @param   array   $options  Options: locale, precision. See {@link setOptions()} for details.
     * @return  string  locale formatted number
     */
    public static function toNumberFormat($value, array $options = array())
    {
        $options = array_merge(self::$_Options, $options);
        if ($options['locale'] instanceof Zend_Locale) {
            $options['locale'] = $options['locale']->toString();
        }

        // Get correct signs for this locale
        $symbols = Zend_Locale_Data::getContent($options['locale'], 'numbersymbols');
        iconv_set_encoding('internal_encoding', 'UTF-8');

        // Get format
        if ($options['format'] === null) {
            $format  = Zend_Locale_Data::getContent($options['locale'], 'decimalnumberformat');
            $options['format']  = $format['default'];
            $options['precision'] = null;
        } else {
            if (strpos($options['format'], '.')) {
                $options['precision'] = substr($options['format'], strpos($options['format'], '.') + 1);
                if (is_numeric($options['precision'])) {
                    $options['precision'] = strlen($options['precision']);
                    $options['format'] = substr($options['format'], 0, strpos($options['format'], '.') + 1);
                    $options['format'] .= '###';
                    $value = round($value, $options['precision']);
                } else {
                    $options['precision'] = null;
                }
            } else {
                $value = round($value);
                $options['precision'] = 0;
            }
        }
        
        // seperate negative format pattern when avaiable 
        if (iconv_strpos($options['format'], ';') !== false) {
            if (call_user_func(Zend_Locale_Math::$comp, $value, 0) < 0) {
                $options['format'] = iconv_substr($options['format'], iconv_strpos($options['format'], ';') + 1);
            } else {
                $options['format'] = iconv_substr($options['format'], 0, iconv_strpos($options['format'], ';'));
            }
        }

        // set negative sign
        if (call_user_func(Zend_Locale_Math::$comp, $value, 0) < 0) {
            if (iconv_strpos($options['format'], '-') === false) {
                $options['format'] = $symbols['minus'] . $options['format'];
            } else {
                $options['format'] = str_replace('-', $symbols['minus'], $options['format']);
            }
        }

        // get number parts
        if (iconv_strpos($value, '.') !== false) {
            if ($options['precision'] === null) {
                $precstr = iconv_substr($value, iconv_strpos($value, '.') + 1);
            } else {
                $precstr = iconv_substr($value, iconv_strpos($value, '.') + 1, $options['precision']);
                if (iconv_strlen($precstr) < $options['precision']) {
                    $precstr = $precstr . str_pad("0", ($options['precision'] - iconv_strlen($precstr)), "0");
                }
            }
        } else {
            if ($options['precision'] > 0) {
                $precstr = str_pad("0", ($options['precision']), "0");
            }
        }
        if ($options['precision'] === null) {
            if (isset($precstr)) {
                $options['precision'] = iconv_strlen($precstr);
            } else {
                $options['precision'] = 0;
            }
        }

        // get fraction and format lengths
        $preg = call_user_func(Zend_Locale_Math::$sub, $value, '0', 0);
        $prec = call_user_func(Zend_Locale_Math::$sub, $value, $preg, $options['precision']);
        if (iconv_strpos($prec, '-') !== false) {
            $prec = iconv_substr($prec, 1);
        }
        $number = call_user_func(Zend_Locale_Math::$sub, $value, 0, 0);
        if (iconv_strpos($number, '-') !== false) {
            $number = iconv_substr($number, 1);
        }
        $group  = iconv_strrpos($options['format'], ',');
        $group2 = iconv_strpos ($options['format'], ',');
        $point  = iconv_strpos ($options['format'], '0');
        // Add fraction
        if ($options['precision'] == '0') {
            $options['format'] = iconv_substr($options['format'], 0, $point)
                               . iconv_substr($options['format'], iconv_strrpos($options['format'], '#') + 2);
        } else {
            $options['format'] = iconv_substr($options['format'], 0, $point) . $symbols['decimal']
                              . iconv_substr($prec, 2) . iconv_substr($options['format']
                              , iconv_strrpos($options['format'], '#') + 1);
        }
        // Add seperation
        if ($group == 0) {
            // no seperation
            $options['format'] = $number . iconv_substr($options['format'], $point);

        } else if ($group == $group2) {
            // only 1 seperation
            $seperation = ($point - $group);
            for ($x = iconv_strlen($number); $x > $seperation; $x -= $seperation) {
                if (iconv_substr($number, 0, $x - $seperation) !== "") {
                    $number = iconv_substr($number, 0, $x - $seperation) . $symbols['group']
                             . iconv_substr($number, $x - $seperation);
                }
            }
            $options['format'] = iconv_substr($options['format'], 0, iconv_strpos($options['format'], '#'))
                               . $number . iconv_substr($options['format'], $point);

        } else {

            // 2 seperations
            if (iconv_strlen($number) > ($point - $group)) { 
                $seperation = ($point - $group);
                $number = iconv_substr($number, 0, iconv_strlen($number) - $seperation) . $symbols['group']
                        . iconv_substr($number, iconv_strlen($number) - $seperation);

                if ((iconv_strlen($number) - 1) > ($point - $group + 1)) {
                    $seperation2 = ($group - $group2 - 1);
                    
                    for ($x = iconv_strlen($number) - $seperation2 - 2; $x > $seperation2; $x -= $seperation2) {
                         $number = iconv_substr($number, 0, $x - $seperation2) . $symbols['group']
                                 . iconv_substr($number, $x - $seperation2);
                    }
                }

            }
            $options['format'] = iconv_substr($options['format'], 0, iconv_strpos($options['format'], '#'))
                               . $number . iconv_substr($options['format'], $point);

        }

        return (string) $options['format'];        
    }


    /**
     * Checks if the input contains a normalized or localized number
     * 
     * @param   string  $input    Localized number string
     * @param   array   $options  Options: locale. See {@link setOptions()} for details.
     * @return  boolean           Returns true if a number was found
     */
    public static function isNumber($input, array $options = array())
    {
        // Get correct signs for this locale
        $symbols = Zend_Locale_Data::getContent($options['locale'],'numbersymbols');

        // Parse input locale aware
        $regex = '/^('.$symbols['minus'].'){0,1}(\d+(\\'.$symbols['group'].'){0,1})*(\\'.$symbols['decimal'].'){0,1}\d+$/';
        preg_match($regex, $input, $found);

        if (!isset($found[0]))
            return false;
        return true;
    }


    /**
     * Alias for getNumber
     * 
     * @param   string  $value    Number to localize
     * @param   array   $options  Options: locale, precision. See {@link setOptions()} for details.
     * @return  float
     */
    public static function getFloat($input, array $options = array())
    {
        return floatval(self::getNumber($input, $options));
    }


    /**
     * Returns a locale formatted integer number
     * Alias for toNumber()
     * 
     * @param   string  $value    Number to normalize
     * @param   array   $options  Options: locale, precision. See {@link setOptions()} for details.
     * @return  string  Locale formatted number
     */
    public static function toFloat($value, array $options = array())
    {
        return self::toNumber($value, $options);
    }


    /**
     * Returns if a float was found
     * Alias for isNumber()
     * 
     * @param   string  $input    Localized number string
     * @param   array   $options  Options: locale. See {@link setOptions()} for details.
     * @return  boolean           Returns true if a number was found
     */
    public static function isFloat($value, array $options = array())
    {
        return self::isNumber($value, $options);
    }


    /**
     * Returns the first found integer from an string
     * Parsing depends on given locale (grouping and decimal)
     *
     * Examples for input:
     * '  2345.4356,1234' = 23455456
     * '+23,3452.123' = 233452
     * ' 12343 ' = 12343
     * '-9456km' = -9456
     * '0' = 0
     * '(-){0,1}(\d+(\.){0,1})*(\,){0,1})\d+'
     * 
     * @param   string   $input    Input string to parse for numbers
     * @param   array    $options  Options: locale. See {@link setOptions()} for details.
     * @return  integer            Returns the extracted number
     */
    public static function getInteger($input, array $options = array())
    {
        $options['precision'] = 0;
        return intval(self::getFloat($input, $options));
    }


    /**
     * Returns a localized number
     * 
     * @param   string  $value    Number to normalize
     * @param   array   $options  Options: locale. See {@link setOptions()} for details.
     * @return  string            Locale formatted number
     */
    public static function toInteger($value, array $options = array())
    {
        // round the output with precision -1
        $options['precision'] = -1;
        return self::toNumber($value, $options);
    }


    /**
     * Returns if a integer was found
     * 
     * @param   string  $input    Localized number string
     * @param   array   $options  Options: locale. See {@link setOptions()} for details.
     * @return  boolean           Returns true if a integer was found
     */
    public static function isInteger($value, array $options = array())
    {
        $options['precision'] = 0;
        return self::isNumber($value, $options);
    }


    /**
     * Converts a format string from PHP's date format to ISO format
     * Remember that Zend Date always returns localized string, so a month name which returns the english
     * month in php's date() will return the translated month name with this function... use 'en' as locale
     * if you are in need of the original english names
     * 
     * The conversion has the following restrictions:
     * 'a', 'A' - Meridiem is not explicit upper/lowercase, you have to upper/lowercase the translated value yourself
     * 
     * @param  string  $format  Format string in PHP's date format
     * @return string           Format string in ISO format
     */
    public static function convertPhpToIsoFormat($format)
    {
        $convert = array('d' => 'dd'  , 'D' => 'EEE' , 'j' => 'd'   , 'l' => 'EEEE', 'N' => 'e'   , 'S' => 'SS'  ,
                         'w' => 'eee' , 'z' => 'D'   , 'W' => 'w'   , 'F' => 'MMMM', 'm' => 'MM'  , 'M' => 'MMM' ,
                         'n' => 'M'   , 't' => 'ddd' , 'L' => 'l'   , 'o' => 'YYYY', 'Y' => 'yyyy', 'y' => 'yy'  ,
                         'a' => 'a'   , 'A' => 'a'   , 'B' => 'B'   , 'g' => 'h'   , 'G' => 'H'   , 'h' => 'hh'  ,
                         'H' => 'HH'  , 'i' => 'mm'  , 's' => 'ss'  , 'e' => 'zzzz', 'I' => 'I'   , 'O' => 'Z'   ,
                         'P' => 'ZZZZ', 'T' => 'z'   , 'Z' => 'X'   , 'c' => 'yyyy-MM-ddTHH:mm:ssZZZZ',
                         'r' => 'r'   , 'U' => 'U');
        $values = str_split($format);
        foreach ($values as $key => $value) {
            if (array_key_exists($value, $convert)) {
                $values[$key] = $convert[$value];
            }
        }
        return join($values);
    }


    /**
     * Parse date and split in named array fields
     *
     * @param   string  $date     Date string to parse
     * @param   array   $options  Options: type, fixdate, locale, format. See {@link setOptions()} for details.
     * @return  array             Possible array members: day, month, year, hour, minute, second, fixed, format
     */
    private static function _parseDate($date, $options)
    {
        $options = array_merge(self::$_Options, $options); // 'format' is not optional, need a default
        $test = array('h', 'H', 'm', 's', 'y', 'Y', 'M', 'd', 'D', 'E', 'S', 'l', 'B', 'I', 
                       'X', 'r', 'U', 'G', 'w', 'e', 'a', 'A', 'Z', 'z');
        foreach (str_split($options['format']) as $splitted) {
            if ((!in_array($splitted, $test)) and (ctype_alpha($splitted))) {
                throw new Zend_Locale_Exception("Unable to parse format string '" . $options['format'] . "' at letter '$splitted'");
            }
        }
        $number = $date; // working copy
        $result['format'] = $options['format']; // save the format used to normalize $number (convenience)
        $result['locale'] = $options['locale']; // save the locale used to normalize $number (convenience)

        $day   = iconv_strpos($options['format'], 'd');
        $month = iconv_strpos($options['format'], 'M');
        $year  = iconv_strpos($options['format'], 'y');
        $hour  = iconv_strpos($options['format'], 'H');
        $min   = iconv_strpos($options['format'], 'm');
        $sec   = iconv_strpos($options['format'], 's');
        $am    = null;
        if ($hour === false) {
            $hour = iconv_strpos($options['format'], 'h');
        }
        if ($year === false) {
            $year = iconv_strpos($options['format'], 'Y');
        }
        if ($day === false) {
            $day = iconv_strpos($options['format'], 'E');
            if ($day === false) {
                $day = iconv_strpos($options['format'], 'D');
            }
        }

        if ($day !== false) {
            $parse[$day]   = 'd';
            if (!empty($options['locale']) && ($options['locale'] !== 'root') && 
                (!is_object($options['locale']) || ($options['locale']->toString() !== 'root'))) {
                // erase day string
                $daylist = Zend_Locale_Data::getContent($options['locale'], 'daylist', array('gregorian', 'format', 'wide'));
                foreach($daylist as $key => $name) {
                    if (iconv_strpos($number, $name) !== false) {
                        $number = str_replace($name, "EEEE", $number);
                        break;
                    }
                }
            }
        }
        $position = false;

        if ($month !== false) {
            $parse[$month] = 'M';
            if (!empty($options['locale']) && ($options['locale'] !== 'root') && 
                (!is_object($options['locale']) || ($options['locale']->toString() !== 'root'))) {
                // prepare to convert month name to their numeric equivalents, if requested, and we have a $options['locale']
                $position = self::_replaceMonth($number, Zend_Locale_Data::getContent($options['locale'], 'monthlist', array('gregorian', 'format', 'wide')));
                if ($position === false) {
                    $position = self::_replaceMonth($number, Zend_Locale_Data::getContent($options['locale'], 'monthlist', array('gregorian', 'format', 'abbreviated')));
                }
            }
        }
        if ($year !== false) {
            $parse[$year]  = 'y';
        }
        if ($hour !== false) {
            $parse[$hour] = 'H';
        }
        if ($min !== false) {
            $parse[$min] = 'm';
        }
        if ($sec !== false) {
            $parse[$sec] = 's';
        }

        if (empty($parse)) {
            throw new Zend_Locale_Exception("unknown format, neither date nor time in '" . $options['format'] . "' found");
        }
        ksort($parse);

        // get daytime
        if (iconv_strpos($options['format'], 'a') !== false) {
            $daytime = Zend_Locale_Data::getContent($options['locale'], 'daytime', 'gregorian');
            if (iconv_strpos(strtoupper($number), strtoupper($daytime['am']))) {
                $am = true;
            } else if (iconv_strpos(strtoupper($number), strtoupper($daytime['pm']))) {
                $am = false;
            }
        }

        // split number parts 
        $split = false;
        preg_match_all('/\d+/u', $number, $splitted);

        if (count($splitted[0]) == 0) {
            throw new Zend_Locale_Exception("No date part in '$date' found.");
        }

        if (count($splitted[0]) == 1) {
            $split = 0;
        }
        $cnt = 0;
        foreach($parse as $key => $value) {

            switch($value) {
                case 'd':
                    if ($split === false) {
                        if (count($splitted[0]) > $cnt) {
                            $result['day']    = (int) $splitted[0][$cnt];
                        }
                    } else {
                        $result['day']    = (int) iconv_substr($splitted[0][0], $split, 2);
                        $split += 2;
                    }
                    ++$cnt;
                    break;
                case 'M':
                    if ($split === false) {
                        if (count($splitted[0]) > $cnt) {
                            $result['month']  = (int) $splitted[0][$cnt];
                        }
                    } else {
                        $result['month']  = (int) iconv_substr($splitted[0][0], $split, 2);
                        $split += 2;
                    }
                    ++$cnt;
                    break;
                case 'y':
                    $length = 2;
                    if ((iconv_substr($options['format'], $year, 4) == 'yyyy') || (iconv_substr($options['format'], $year, 4) == 'YYYY')) {
                        $length = 4;
                    }
                    if ($split === false) {
                        if (count($splitted[0]) > $cnt) {
                            $result['year']   = (int) $splitted[0][$cnt];
                        }
                    } else {
                        $result['year']   = (int) iconv_substr($splitted[0][0], $split, $length);
                        $split += $length;
                    }
                    ++$cnt;
                    break;
                case 'H':
                    if ($split === false) {
                        if (count($splitted[0]) > $cnt) {
                            $result['hour']   = (int) $splitted[0][$cnt];
                        }
                    } else {
                        $result['hour']   = (int) iconv_substr($splitted[0][0], $split, 2);
                        $split += 2;
                    }
                    ++$cnt;
                    break;
                case 'm':
                    if ($split === false) {
                        if (count($splitted[0]) > $cnt) {
                            $result['minute'] = (int) $splitted[0][$cnt];
                        }
                    } else {
                        $result['minute'] = (int) iconv_substr($splitted[0][0], $split, 2);
                        $split += 2;
                    }
                    ++$cnt;
                    break;
                case 's':
                    if ($split === false) {
                        if (count($splitted[0]) > $cnt) {
                            $result['second'] = (int) $splitted[0][$cnt];
                        }
                    } else {
                        $result['second'] = (int) iconv_substr($splitted[0][0], $split, 2);
                        $split += 2;
                    }
                    ++$cnt;
                    break;
            }
        }

        // AM/PM correction
        if ($hour !== false) {
            if (($am === true) and ($result['hour'] == 12)){
                $result['hour'] = 0;
            } else if (($am === false) and ($result['hour'] != 12)) {
                $result['hour'] += 12;
            }
        }

        if ($options['fixdate'] === true) {
            $result['fixed'] = 0; // nothing has been "fixed" by swapping date parts around (yet)
        }
        if ($day !== false) {
            // fix false month
            if (isset($result['day']) and isset($result['month'])) {
                if (($position !== false) && ($position != $month)) {
                    if ($options['fixdate'] !== true) {
                        throw new Zend_Locale_Exception("unable to parse date '$date' using '" . $options['format'] . "' (false month, $position, $month)");
                    }
                    $temp = $result['day'];
                    $result['day']   = $result['month'];
                    $result['month'] = $temp;
                    $result['fixed'] = 1;
                }
            }

            // fix switched values d <> y
            if (isset($result['day']) and isset($result['year'])) {
                if ($result['day'] > 31) {
                    if ($options['fixdate'] !== true) {
                        throw new Zend_Locale_Exception("unable to parse date '$date' using '" . $options['format'] . "' (d <> y)");
                    }
                    $temp = $result['year'];
                    $result['year'] = $result['day'];
                    $result['day']  = $temp;
                    $result['fixed'] = 2;
                }
            }

            // fix switched values M <> y
            if (isset($result['month']) and isset($result['year'])) {
                if ($result['month'] > 31) {
                    if ($options['fixdate'] !== true) {
                        throw new Zend_Locale_Exception("unable to parse date '$date' using '" . $options['format'] . "' (M <> y)");
                    }
                    $temp = $result['year'];
                    $result['year']  = $result['month'];
                    $result['month'] = $temp;
                    $result['fixed'] = 3;
                }
            }

            // fix switched values M <> d
            if (isset($result['month']) and isset($result['day'])) {
                if ($result['month'] > 12) {
                    if ($options['fixdate'] !== true || $result['month'] > 31) {
                        throw new Zend_Locale_Exception("unable to parse date '$date' using '" . $options['format'] . "' (M <> d)");
                    }
                    $temp = $result['day'];
                    $result['day']   = $result['month'];
                    $result['month'] = $temp;
                    $result['fixed'] = 4;
                }
            }
        }
        return $result;
    }


    /**
     * Search $number for a month name found in $monthlist, and replace if found.
     *
     * @param  string  $number     Date string (modified)
     * @param  array   $monthlist  List of month names
     *
     * @return int|false           Position of replaced string (false if nothing replaced) 
     */
    static protected function _replaceMonth(&$number, $monthlist)
    {
        // If $locale was invalid, $monthlist will default to a "root" identity
        // mapping for each month number from 1 to 12.
        // If no $locale was given, or $locale was invalid, do not use this identity mapping to normalize.
        // Otherwise, translate locale aware month names in $number to their numeric equivalents.
        $position = false;
        if ($monthlist && $monthlist[1] != 1) {
            foreach($monthlist as $key => $name) {
                if (($position = iconv_strpos($number, $name)) !== false) {
                    if ($key < 10) {
                        $key = "0" . $key;
                    }
                    $number   = str_replace($name, $key, $number);
                    return $position;
                }
            }
        }
        return false;
    }


    /**
     * Returns the default date format for $locale.
     *
     * @param  string|Zend_Locale  $locale  OPTIONAL Locale of $number, possibly in string form (e.g. 'de_AT')
     * @return string  format
     */
    public static function getDateFormat($locale = null)
    {
        $format = Zend_Locale_Data::getContent($locale, 'defdateformat', 'gregorian');
        $format = $format['default'];

        $format = Zend_Locale_Data::getContent($locale, 'dateformat', array('gregorian', $format));
        return $format['pattern'];
    }


    /**
     * Returns an array with the normalized date from an locale date
     * a input of 10.01.2006 without a $locale would return:
     * array ('day' => 10, 'month' => 1, 'year' => 2006)
     * The 'locale' option is only used to convert human readable day
     * and month names to their numeric equivalents.
     * The 'format' option allows specification of self-defined date formats,
     * when not using the default format for the 'locale'.
     *
     * @param   string  $date     Date string
     * @param   array   $options  Options: type, fixdate, locale, format. See {@link setOptions()} for details.
     * @return  array             Possible array members: day, month, year, hour, minute, second, fixed, format
     */
    public static function getDate($date, array $options = array())
    {
        $options = array_merge(self::$_Options, $options);
        if (empty($options['format'])) {
            $options['format'] = self::getDateFormat($options['locale']);
        } else if ($options['type'] == 'php') {
            $options['format'] = self::convertPhpToIsoFormat($options['format']);
        }

        return self::_parseDate($date, $options);
    }


    /**
     * Returns if the given string is a date
     *
     * @param   string  $date     Date string
     * @param   array   $options  Options: type, fixdate, locale, format. See {@link setOptions()} for details.
     * @return  boolean
     */
    public static function isDate($date, array $options = array())
    {
        try {
            $date = self::getDate($date, $options);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }


    /**
     * Returns the default time format for $locale.
     *
     * @param  string|Zend_Locale  $locale  OPTIONAL Locale of $number, possibly in string form (e.g. 'de_AT')
     * @return string  format
     */
    public static function getTimeFormat($locale = null)
    {
        $format = Zend_Locale_Data::getContent($locale, 'deftimeformat', 'gregorian');
        $format = $format['default'];

        $format = Zend_Locale_Data::getContent($locale, 'timeformat', array('gregorian', $format));
        return $format['pattern'];
    }


    /**
     * Returns an array with 'hour', 'minute', and 'second' elements extracted from $time
     * according to the order described in $format.  For a format of 'H:m:s', and
     * an input of 11:20:55, getTime() would return:
     * array ('hour' => 11, 'minute' => 20, 'second' => 55)
     * The optional $locale parameter may be used to help extract times from strings
     * containing both a time and a day or month name.
     *
     * @param   string  $time     Time string
     * @param   array   $options  Options: type, fixdate, locale, format. See {@link setOptions()} for details.
     * @return  array             Possible array members: day, month, year, hour, minute, second, fixed, format
     */
    public static function getTime($time, array $options = array())
    {
        $options = array_merge(self::$_Options, $options);
        if (empty($options['format'])) {
            $options['format'] = self::getTimeFormat($options['locale']);
        } else if ($options['type'] == 'php') {
            $options['format'] = self::convertPhpToIsoFormat($options['format']);
        }

        return self::_parseDate($time, $options);
    }


    /**
     * Returns is the given string is a time
     *
     * @param   string  $time     Time string
     * @param   array   $options  Options: type, fixdate, locale, format. See {@link setOptions()} for details.
     * @return  boolean
     */
    public static function isTime($time, array $options = array())
    {
        try {
            $date = self::getTime($time, $options);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
}
