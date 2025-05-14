<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2013-2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\common\utils;

use DateInterval;
use Exception;
use qtism\common\utils\data\CharacterMap;
use ValueError;

/**
 * A utility class focusing on string format checks.
 */
class Format
{
    /**
     * Perl XML Ideographic regular expression.
     *
     * @var string A perl regular expression.
     * @link http://cpansearch.perl.org/src/TJMATHER/XML-RegExp-0.04/lib/XML/RegExp.pm
     */
    private static $perlXmlIdeographic = '(?:\xE3\x80[\x87\xA1-\xA9]|\xE4(?:[\xB8-\xBF][\x80-\xBF])|\xE5(?:[\x80-\xBF][\x80-\xBF])|\xE6(?:[\x80-\xBF][\x80-\xBF])|\xE7(?:[\x80-\xBF][\x80-\xBF])|\xE8(?:[\x80-\xBF][\x80-\xBF])|\xE9(?:[\x80-\xBD][\x80-\xBF]|\xBE[\x80-\xA5]))';

    /**
     * Perl XML Extender regular expression.
     *
     * @var string A perl regular expression.
     * @link http://cpansearch.perl.org/src/TJMATHER/XML-RegExp-0.04/lib/XML/RegExp.pm
     */
    private static $perlXmlExtender = '(?:\xC2\xB7|\xCB[\x90\x91]|\xCE\x87|\xD9\x80|\xE0(?:\xB9\x86|\xBB\x86)|\xE3(?:\x80[\x85\xB1-\xB5]|\x82[\x9D\x9E]|\x83[\xBC-\xBE]))';

    /**
     * Perl XML BaseChar regular expression.
     *
     * @var string A perl regular expression.
     * @link http://cpansearch.perl.org/src/TJMATHER/XML-RegExp-0.04/lib/XML/RegExp.pm
     */
    private static $perlXmlBaseChar = '(?:[a-zA-Z]|\xC3[\x80-\x96\x98-\xB6\xB8-\xBF]|\xC4[\x80-\xB1\xB4-\xBE]|\xC5[\x81-\x88\x8A-\xBE]|\xC6[\x80-\xBF]|\xC7[\x80-\x83\x8D-\xB0\xB4\xB5\xBA-\xBF]|\xC8[\x80-\x97]|\xC9[\x90-\xBF]|\xCA[\x80-\xA8\xBB-\xBF]|\xCB[\x80\x81]|\xCE[\x86\x88-\x8A\x8C\x8E-\xA1\xA3-\xBF]|\xCF[\x80-\x8E\x90-\x96\x9A\x9C\x9E\xA0\xA2-\xB3]|\xD0[\x81-\x8C\x8E-\xBF]|\xD1[\x80-\x8F\x91-\x9C\x9E-\xBF]|\xD2[\x80\x81\x90-\xBF]|\xD3[\x80-\x84\x87\x88\x8B\x8C\x90-\xAB\xAE-\xB5\xB8\xB9]|\xD4[\xB1-\xBF]|\xD5[\x80-\x96\x99\xA1-\xBF]|\xD6[\x80-\x86]|\xD7[\x90-\xAA\xB0-\xB2]|\xD8[\xA1-\xBA]|\xD9[\x81-\x8A\xB1-\xBF]|\xDA[\x80-\xB7\xBA-\xBE]|\xDB[\x80-\x8E\x90-\x93\x95\xA5\xA6]|\xE0(?:\xA4[\x85-\xB9\xBD]|\xA5[\x98-\xA1]|\xA6[\x85-\x8C\x8F\x90\x93-\xA8\xAA-\xB0\xB2\xB6-\xB9]|\xA7[\x9C\x9D\x9F-\xA1\xB0\xB1]|\xA8[\x85-\x8A\x8F\x90\x93-\xA8\xAA-\xB0\xB2\xB3\xB5\xB6\xB8\xB9]|\xA9[\x99-\x9C\x9E\xB2-\xB4]|\xAA[\x85-\x8B\x8D\x8F-\x91\x93-\xA8\xAA-\xB0\xB2\xB3\xB5-\xB9\xBD]|\xAB\xA0|\xAC[\x85-\x8C\x8F\x90\x93-\xA8\xAA-\xB0\xB2\xB3\xB6-\xB9\xBD]|\xAD[\x9C\x9D\x9F-\xA1]|\xAE[\x85-\x8A\x8E-\x90\x92-\x95\x99\x9A\x9C\x9E\x9F\xA3\xA4\xA8-\xAA\xAE-\xB5\xB7-\xB9]|\xB0[\x85-\x8C\x8E-\x90\x92-\xA8\xAA-\xB3\xB5-\xB9]|\xB1[\xA0\xA1]|\xB2[\x85-\x8C\x8E-\x90\x92-\xA8\xAA-\xB3\xB5-\xB9]|\xB3[\x9E\xA0\xA1]|\xB4[\x85-\x8C\x8E-\x90\x92-\xA8\xAA-\xB9]|\xB5[\xA0\xA1]|\xB8[\x81-\xAE\xB0\xB2\xB3]|\xB9[\x80-\x85]|\xBA[\x81\x82\x84\x87\x88\x8A\x8D\x94-\x97\x99-\x9F\xA1-\xA3\xA5\xA7\xAA\xAB\xAD\xAE\xB0\xB2\xB3\xBD]|\xBB[\x80-\x84]|\xBD[\x80-\x87\x89-\xA9])|\xE1(?:\x82[\xA0-\xBF]|\x83[\x80-\x85\x90-\xB6]|\x84[\x80\x82\x83\x85-\x87\x89\x8B\x8C\x8E-\x92\xBC\xBE]|\x85[\x80\x8C\x8E\x90\x94\x95\x99\x9F-\xA1\xA3\xA5\xA7\xA9\xAD\xAE\xB2\xB3\xB5]|\x86[\x9E\xA8\xAB\xAE\xAF\xB7\xB8\xBA\xBC-\xBF]|\x87[\x80-\x82\xAB\xB0\xB9]|[\xB8\xB9][\x80-\xBF]|\xBA[\x80-\x9B\xA0-\xBF]|\xBB[\x80-\xB9]|\xBC[\x80-\x95\x98-\x9D\xA0-\xBF]|\xBD[\x80-\x85\x88-\x8D\x90-\x97\x99\x9B\x9D\x9F-\xBD]|\xBE[\x80-\xB4\xB6-\xBC\xBE]|\xBF[\x82-\x84\x86-\x8C\x90-\x93\x96-\x9B\xA0-\xAC\xB2-\xB4\xB6-\xBC])|\xE2(?:\x84[\xA6\xAA\xAB\xAE]|\x86[\x80-\x82])|\xE3(?:\x81[\x81-\xBF]|\x82[\x80-\x94\xA1-\xBF]|\x83[\x80-\xBA]|\x84[\x85-\xAC])|\xEA(?:[\xB0-\xBF][\x80-\xBF])|\xEB(?:[\x80-\xBF][\x80-\xBF])|\xEC(?:[\x80-\xBF][\x80-\xBF])|\xED(?:[\x80-\x9D][\x80-\xBF]|\x9E[\x80-\xA3]))';

    /**
     * Perl XML Digit regular expression.
     *
     * @var string A perl regular expression.
     * @link http://cpansearch.perl.org/src/TJMATHER/XML-RegExp-0.04/lib/XML/RegExp.pm
     */
    private static $perlXmlDigit = '(?:[0-9]|\xD9[\xA0-\xA9]|\xDB[\xB0-\xB9]|\xE0(?:\xA5[\xA6-\xAF]|\xA7[\xA6-\xAF]|\xA9[\xA6-\xAF]|\xAB[\xA6-\xAF]|\xAD[\xA6-\xAF]|\xAF[\xA7-\xAF]|\xB1[\xA6-\xAF]|\xB3[\xA6-\xAF]|\xB5[\xA6-\xAF]|\xB9[\x90-\x99]|\xBB[\x90-\x99]|\xBC[\xA0-\xA9]))';

    /**
     * Perl XML CombiningChar regular expression.
     *
     * @var string A perl regular expression.
     * @link http://cpansearch.perl.org/src/TJMATHER/XML-RegExp-0.04/lib/XML/RegExp.pm
     */
    private static $perlXmlCombiningChar = '(?:\xCC[\x80-\xBF]|\xCD[\x80-\x85\xA0\xA1]|\xD2[\x83-\x86]|\xD6[\x91-\xA1\xA3-\xB9\xBB-\xBD\xBF]|\xD7[\x81\x82\x84]|\xD9[\x8B-\x92\xB0]|\xDB[\x96-\xA4\xA7\xA8\xAA-\xAD]|\xE0(?:\xA4[\x81-\x83\xBC\xBE\xBF]|\xA5[\x80-\x8D\x91-\x94\xA2\xA3]|\xA6[\x81-\x83\xBC\xBE\xBF]|\xA7[\x80-\x84\x87\x88\x8B-\x8D\x97\xA2\xA3]|\xA8[\x82\xBC\xBE\xBF]|\xA9[\x80-\x82\x87\x88\x8B-\x8D\xB0\xB1]|\xAA[\x81-\x83\xBC\xBE\xBF]|\xAB[\x80-\x85\x87-\x89\x8B-\x8D]|\xAC[\x81-\x83\xBC\xBE\xBF]|\xAD[\x80-\x83\x87\x88\x8B-\x8D\x96\x97]|\xAE[\x82\x83\xBE\xBF]|\xAF[\x80-\x82\x86-\x88\x8A-\x8D\x97]|\xB0[\x81-\x83\xBE\xBF]|\xB1[\x80-\x84\x86-\x88\x8A-\x8D\x95\x96]|\xB2[\x82\x83\xBE\xBF]|\xB3[\x80-\x84\x86-\x88\x8A-\x8D\x95\x96]|\xB4[\x82\x83\xBE\xBF]|\xB5[\x80-\x83\x86-\x88\x8A-\x8D\x97]|\xB8[\xB1\xB4-\xBA]|\xB9[\x87-\x8E]|\xBA[\xB1\xB4-\xB9\xBB\xBC]|\xBB[\x88-\x8D]|\xBC[\x98\x99\xB5\xB7\xB9\xBE\xBF]|\xBD[\xB1-\xBF]|\xBE[\x80-\x84\x86-\x8B\x90-\x95\x97\x99-\xAD\xB1-\xB7\xB9])|\xE2\x83[\x90-\x9C\xA1]|\xE3(?:\x80[\xAA-\xAF]|\x82[\x99\x9A]))';

    /**
     * Perl printf format specifier regular expression.
     *
     * @var string A perl regular expression.
     * @see http://www.cplusplus.com/reference/cstdio/printf/ C++ printf format.
     */
    private static $printfFormatSpecifier = '%(?:(?:-|\+| |#|0)*){0,1}(?:[0-9]+|\*){0,1}(?:\.[0-9]+|\*){0,1}(?:hh|h|ll|l|j|z|t|L){0,1}(?:d|i|u|o|x|X|f|F|e|E|g|G|a|A|c|s|p|n)';

    /**
     * Check if string is compliant with the identifier datatype of IMS QTI.
     *
     * IMS Global says :
     * Identifiers can contain the character classes Letter, Digit, Combining which are described in the
     * Extensible Markup Language (XML) 1.0 (Second Edition). Identifiers should have no more
     * than 32 characters for compatibility with version 1. They are always compared case-sensitively.
     *
     * @link http://www.w3.org/TR/2000/REC-xml-20001006
     * @param string $string
     * @param bool $strict
     * @return bool Whether $string is a valid identifier.
     */
    public static function isIdentifier(string $string, bool $strict = true): bool
    {
        if (!$strict) {
            return preg_match("/^[a-zA-Z_][a-zA-Z0-9_.-]*$/u", $string) === 1;
        }

        if (!isset($string[0]) || !isset(CharacterMap::$identifier_first[$string[0]])) {
            return false;
        }

        for ($i = strlen($string) - 1; $i > 0; $i--) {
            if (!isset(CharacterMap::$identifier_other[$string[$i]])) {
                return false;
            }
        }

        return true;
    }

    /**
     * "Sanitizes" an identifier : removes any non-valid character that does not
     * correspond to the criteria written below, and returns a valid qti identifier.
     * If after sanitizing, the identifier is stil not valid, a random identifier is generated.
     *
     * IMS Global says :
     * Identifiers can contain the character classes Letter, Digit, Combining which are described in the
     * Extensible Markup Language (XML) 1.0 (Second Edition). Identifiers should have no more
     * than 32 characters for compatibility with version 1. They are always compared case-sensitively.
     *
     * @param string $dirtyIdentifier The string of the identifier to sanitize.
     *
     * @return string A valid  qti-identifier representation of the $identifier set as paramter
     * @link http://www.w3.org/TR/2000/REC-xml-20001006
     */
    public static function sanitizeIdentifier(string $dirtyIdentifier): string
    {
        if (preg_match("/^[a-zA-Z_][a-zA-Z0-9_.-]*$/u", $dirtyIdentifier)) {
            return $dirtyIdentifier;
        }

        $cleanIdentifier = preg_replace('/^[^a-zA-Z_]+/u', '', $dirtyIdentifier); // Cleaning start
        $cleanIdentifier = preg_replace("/[^a-zA-Z0-9_.-]+/u", '', $cleanIdentifier); // Cleaning content

        if (preg_match("/^[a-zA-Z_][a-zA-Z0-9_.-]*$/u", $cleanIdentifier)) {
            return $cleanIdentifier;
        }

        return self::generateIdentifier();
    }

    /**
     * Generates a pseudo-random identifier, containing 8 characters in CAPS
     * randomly between A and Z, in a uniform manner.
     *
     * @return string The pseudo-random identifier generated
     */
    private static function generateIdentifier(): string
    {
        $rID = '';
        for ($i = 0; $i < 8; $i++) {
            $rID .= chr(rand(65, 90));
        }

        return $rID;
    }

    /**
     * Whether a given $string is a URI.
     *
     * @param string $string A string value.
     * @return bool Whether $string is a valid URI.
     * @link http://en.wikipedia.org/wiki/Uniform_Resource_Identifier
     */
    public static function isUri(string $string): bool
    {
        // @todo find the ultimate URI validation rule.
        return $string !== '';

        // Thanks to Wizard04.
        $pattern = "<^([a-z0-9+.-]+):(?://(?:((?:[a-z0-9-._~!$&'\(\)*+,;=:]|%[0-9A-F]{2})*)@)?((?:[a-z0-9-._~!$&'()*+,;=]|%[0-9A-F]{2})*)(?::(\d*))?(/(?:[a-z0-9-._~!$&'()*+,;=:@/]|%[0-9A-F]{2})*)?|(/?(?:[a-z0-9-._~!$&'()*+,;=:@]|%[0-9A-F]{2})+(?:[a-z0-9-._~!$&'()*+,;=:@/]|%[0-9A-F]{2})*)?)(?:\?((?:[a-z0-9-._~!$&'()*+,;=:/?@]|%[0-9A-F]{2})*))?(?:#((?:[a-z0-9-._~!$&'()*+,;=:/?@]|%[0-9A-F]{2})*))?$>i";
        return preg_match($pattern, $string) === 1;
    }

    /**
     * Whether a given $string is a valid BCP 47 language code.
     *
     * @param string $string A string value.
     * @return bool Whether $string is a valid BCP 47 language code.
     *
     * @see https://en.wikipedia.org/wiki/IETF_language_tag
     * @see https://stackoverflow.com/questions/7035825/regular-expression-for-a-language-tag-as-defined-by-bcp47#7036171 for the regex
     */
    public static function isBCP47Lang(string $string): bool
    {
        $pattern = "/^(?<grandfathered>(?:en-GB-oed|i-(?:ami|bnn|default|enochian|hak|klingon|lux|mingo|navajo|pwn|t(?:a[oy]|su))|sgn-(?:BE-(?:FR|NL)|CH-DE))|(?:art-lojban|cel-gaulish|no-(?:bok|nyn)|zh-(?:guoyu|hakka|min(?:-nan)?|xiang)))|(?:(?<language>(?:[A-Za-z]{2,3}(?:-(?<extlang>[A-Za-z]{3}(?:-[A-Za-z]{3}){0,2}))?)|[A-Za-z]{4}|[A-Za-z]{5,8})(?:-(?<script>[A-Za-z]{4}))?(?:-(?<region>[A-Za-z]{2}|[0-9]{3}))?(?:-(?<variant>[A-Za-z0-9]{5,8}|[0-9][A-Za-z0-9]{3}))*(?:-(?<extension>[0-9A-WY-Za-wy-z](?:-[A-Za-z0-9]{2,8})+))*)(?:-(?<privateUse>x(?:-[A-Za-z0-9]{1,8})+))?$/Di";
        return preg_match($pattern, trim($string)) === 1;
    }

    /**
     * Whether a given $string can be cast into an integer value.
     *
     * @param string $string A string value.
     * @return bool Whether $string can be cast into an integer value.
     */
    public static function isInteger(string $string): bool
    {
        $pattern = '/^(?:\\-|\\+){0,1}[0-9]+$/';
        return preg_match($pattern, trim($string)) === 1;
    }

    /**
     * Whether a given $string can be cast into a float value.
     *
     * @param string $string A string value e.g. '27.111'.
     * @return bool Whether $string can be converted to a float.
     */
    public static function isFloat(string $string): bool
    {
        $pattern = '/^(?:(?:\\-|\\+){0,1}[0-9]+)$|^(?:(?:\\-|\\+){0,1}[0-9]+\\.[0-9]+)+/';
        return preg_match($pattern, trim($string)) === 1;
    }

    /**
     * Whether a given $string can be cast into a pair.
     *
     * @param string $string A string value.
     * @return bool Whether $string can be converted to a pair.
     */
    public static function isPair(string $string): bool
    {
        $pair = explode("\x20", $string);
        return count($pair) === 2
            && self::isIdentifier(trim($pair[0]))
            && self::isIdentifier(trim($pair[1]));
    }

    /**
     * Whether a given $string can be cast into a directed pair.
     *
     * @param string $string A string value.
     * @return bool Whether $string can be converted to a directed pair.
     */
    public static function isDirectedPair(string $string): bool
    {
        return self::isPair($string);
    }

    /**
     * Whether a given $string can be cast into a duration.
     *
     * @param string $string A string value.
     * @return bool Whether $string can be converted to a duration.
     */
    public static function isDuration(string $string): bool
    {
        try {
            new DateInterval($string);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Whether a given $string can be transformed into a boolean.
     *
     * @param string $string A string value.
     * @return bool Whether $string can be converted to a boolean.
     */
    public static function isBoolean(string $string): bool
    {
        $string = strtolower(trim($string));
        return $string === 'true' || $string === 'false';
    }

    /**
     * Whether a given $string can be cast into a Point datatype.
     *
     * @param string $string A string value.
     * @return bool Whether $string can be transformed to a Point datatype.
     */
    public static function isPoint(string $string): bool
    {
        $parts = explode("\x20", $string);
        return count($parts) === 2
            && self::isInteger($parts[0])
            && self::isInteger($parts[1]);
    }

    /**
     * Whether a given $string can be cast into the file baseType.
     *
     * @param string $string A string value.
     * @return bool
     */
    public static function isFile(string $string): bool
    {
        // @todo implement File baseType as a complex type. See QTI-PCI spec for redemption.
        return is_string($string);
    }

    /**
     * Whether a given string is a variable ref.
     * Valid example: '{myIdentifier1}', 'myIdentifier1'.
     *
     * @param string $string A given string.
     * @return bool Whether $string is a valid variable ref.
     */
    public static function isVariableRef(string $string): bool
    {
        if ($string === '') {
            return false;
        }

        if ($string[0] === '{' && substr($string, -1) === '}') {
            $string = substr($string, 1, -1);
        }
        return self::isIdentifier($string);
    }

    /**
     * Whether a given string is a coordinate collection.
     *
     * For compatibility reasons, float formatted numbers will also
     * be accepted as valid numbers to compose coordinates.
     *
     * For example '0, 20, 100, 20' is a valid coordinate collection to
     * describe a rectangle shape.
     *
     * @param string $string A given string.
     * @return bool Whether $string is a valid coordinate collection.
     */
    public static function isCoords(string $string): bool
    {
        $pattern = "/^-{0,1}[0-9]+(?:\.[0-9]+){0,1}(?:\s*,\s*-{0,1}[0-9]+(?:\.[0-9]+){0,1})*$/";
        return preg_match($pattern, $string) === 1;
    }

    /**
     * Whether a given $string is a string value with a maximum
     * of 256 characters.
     *
     * @param string $string A string value.
     * @return bool
     */
    public static function isString256(string $string): bool
    {
        return mb_strlen($string, 'UTF-8') <= 256;
    }

    /**
     * Whether a given $string is a valid value for a body element's class
     * attribute e.g. 'qti-label' or 'qti-label qti-component'.
     *
     * @param string $string A string value.
     * @return bool
     */
    public static function isClass(string $string): bool
    {
        $pattern = "/^\S+(?: +\S+)*$/";
        return preg_match($pattern, $string) === 1;
    }

    /**
     * Get a float variable into a Standard Form / Scientific notation e.g. 6.72 x 10⁴.
     *
     * * If no $precision is given, 6 significant numbers will be displayed after the decimal separator.
     * * If no $x is given, the 'x' character will be used as the 'times' operator.
     *
     * @param float $float
     * @param string $x The character to be used as the 'times' operator.
     * @param int $precision The number of requested significant numbers after the decimal separator.
     * @return string
     */
    public static function scale10(float $float, string $x = 'x', $precision = false): string
    {
        // 1. Transform in 'E' notation.
        $mask = ($precision === false) ? '%e' : "%.{$precision}e";
        $strFloat = sprintf($mask, $float);

        // 2. Transform the 'E' notation into 'x 10^n' notation.
        $parts = explode('e', $strFloat);
        $mantissa = $parts[1];
        $newMantissa = '';

        $exponents = [
            '0' => '"\u2070"',
            '1' => '"\u00b9"',
            '2' => '"\u00b2"',
            '3' => '"\u00b3"',
            '4' => '"\u2074"',
            '5' => '"\u2075"',
            '6' => '"\u2076"',
            '7' => '"\u2077"',
            '8' => '"\u2078"',
            '9' => '"\u2079"',
            '-' => '"\u207b"',
        ];
        for ($i = 0, $iMax = strlen($mantissa); $i < $iMax; $i++) {
            if (isset($exponents[$mantissa[$i]])) {
                $newMantissa .= json_decode($exponents[$mantissa[$i]], false);
            }
        }

        return $parts[0] . " {$x} 10" . $newMantissa;
    }

    /**
     * This method is not absolutely safe because of the 'volatile' nature of
     * the printf format. However, it can validate a lot of strings as a first
     * barrier for further code to be implemented.
     *
     * Examples:
     *
     * Format::isPrintfIsoFormat('%#x') => true
     * Format::isPrintfIsoFormat('%#llx') => true
     * Format::isPrintfIsoFormat('Octal %#x is Octal %#llx') => true
     * Format::isPrintfIsoFormat("%d\n") => true
     * Format::isPrintfIsoFormat("%3d\n") => true
     * Format::isPrintfIsoFormat("%03d\n") => true
     * Format::isPrintfIsoFormat("Characters: %c %c \n") => true
     * Format::isPrintfIsoFormat("Decimals: %d %ld\n") => true
     * Format::isPrintfIsoFormat("Preceding with blanks: %10d \n") => true
     * Format::isPrintfIsoFormat("Preceding with zeros: %010d \n") => true
     * Format::isPrintfIsoFormat("Some different radices: %d %x %o %#x %#o \n") => true
     * Format::isPrintfIsoFormat("floats: %4.2f %+.0e %E \n") => true
     * Format::isPrintfIsoFormat("Width trick: %*d \n") => true
     * Format::isPrintfIsoFormat("%s \n") => true
     * Format::isPrintfIsoFormat("%3d %06.3f\n") => true
     * Format::isPrintfIsoFormat("The color: %s\n") => true
     * Format::isPrintfIsoFormat("First number: %d\n") => true
     * Format::isPrintfIsoFormat("Second number: %04d\n") => true
     * Format::isPrintfIsoFormat("Third number: %i\n") => true
     * Format::isPrintfIsoFormat("Float number: %3.2f\n") => true
     * Format::isPrintfIsoFormat("Hexadecimal: %x\n") => true
     * Format::isPrintfIsoFormat("Octal: %o\n") => true
     * Format::isPrintfIsoFormat("Unsigned value: %u\n") => true
     * Format::isPrintfIsoFormat("Just print the percentage sign %%\n") => false // Do not contain valid specifier.
     * Format::isPrintfIsoFormat(":%s:\n") => true
     * Format::isPrintfIsoFormat(":%15s:\n") => true
     * Format::isPrintfIsoFormat(":%.10s:\n") => true
     * Format::isPrintfIsoFormat(":%-10s:\n") => true
     * Format::isPrintfIsoFormat(":%-15s:\n") => true
     * Format::isPrintfIsoFormat(":%.15s:\n") => true
     * Format::isPrintfIsoFormat(":%15.10s:\n") => true
     * Format::isPrintfIsoFormat(":%-15.10s:\n") => true
     * Format::isPrintfIsoFormat("This is an integer with padding %03d\n") => true
     * Format::isPrintfIsoFormat('This is an integer with padding...') => false
     * Format::isPrintfIsoFormat("Escape or not? %%s") => false
     * Format::isPrintfIsoFormat("Escape or not? %%%s") => true
     * Format::isPrintfIsoFormat("Escape or not? %%%%s") => false
     * Format::isPrintfIsoFormat("Escape or not? %%%%%s") => true
     * Format::isPrintfIsoFormat("%s bla %s and %%%s is %s and %%%%s") => true
     * Format::isPrintfIsoFormat("%%s bla %s and %%%s is %s and %%%%s") => true
     * Format::isPrintfIsoFormat("%%s bla %%s and %%%s is %s and %%%%s") => true
     * Format::isPrintfIsoFormat("%%s bla %%s and %%s is %s and %%%%s") => true
     * Format::isPrintfIsoFormat("%%s bla %%s and %%s is %%%%s and %%%%s") => false
     * Format::isPrintfIsoFormat("%s") => true
     * Format::isPrintfIsoFormat("%S") => false
     * Format::isPrintfIsoFormat("bla %S bli %s") => true
     *
     * @param string $isoFormat
     * @return bool
     */
    public static function isPrintfIsoFormat(string $isoFormat): bool
    {
        $subPattern = self::$printfFormatSpecifier;
        $pattern = '/(?:(?:[^%]|^)(?:%%)+(' . $subPattern . '))|(?:(?:[^%])(' . $subPattern . '))|(?:^(' . $subPattern . '))/u';

        $matches = [];
        preg_match_all($pattern, $isoFormat, $matches);

        return count($matches[1]) + count($matches[2]) + count($matches[3]) > 0;
    }

    /**
     * Transform an ISO number formatting into a format that can
     * be handled by PHP's printf/sprintf implementation.
     *
     * Please note that this method is not totally safe because
     * of the "funky" nature of ISO number formats especially when
     * used with C++. However, this method can be a good first attempt
     * of transformation for further instructions to be executed.
     *
     * @param string $isoFormat
     * @return string The transformed formatting.
     */
    public static function printfFormatIsoToPhp(string $isoFormat): string
    {
        // Valid format, do the modifications to be compliant with
        // PHP's printf.
        $pattern = '/' . self::$printfFormatSpecifier . '/u';
        $matches = [];
        preg_match_all($pattern, $isoFormat, $matches);

        foreach ($matches[0] as $m) {
            // Don't worry, str_replace is multibyte safe!
            $newM = str_replace(['#', 'h', 'l', 'j', 'z', 't', 'L'], '', $m);
            $newM = str_replace(['i', 'a', 'A', 'c', 'p', 'n', 'O'], ['d', 'x', 'X', 's', 'x', 'd', 'o'], $newM);
            $isoFormat = str_replace($m, $newM, $isoFormat);
        }

        return $isoFormat;
    }

    /**
     * Performs a failsafe sprintf with the same behavior in PHP 7 and 8.
     * In PHP 7, when encountering an invalid format specifier, sprintf just
     * returns an empty string and concatenates is with the rest of the format.
     * In PHP 8, when encountering an invalid format specifier, sprintf throws
     * a ValueError.
     * When run under PHP 8, this methods simulates PHP 7 behavior by removing
     * the invalid specifier from the format string and tries to perform the
     * sprintf call again.
     *
     * NB: this is probably overkill but kept to ensure backward compatibility
     * for any user-provided format.
     *
     * @param string $format
     * @param float $value
     * @return mixed|string
     */
    public static function permissiveSprintf(string $format, float $value)
    {
        $format = self::printfFormatIsoToPhp($format);
        try {
            // Php 7 only runs this line.
            return sprintf($format, $value);
        } catch (ValueError $exception) {
            // Php 8 runs this when invalid format is found.
            $result = false;
            while ($result === false) {
                if (!preg_match('/^Unknown format specifier "([A-Za-z])"$/', $exception->getMessage(), $matches)) {
                    throw $exception;
                }
                $format = preg_replace('/%(?:[0-9]+\$)?-?(?:\.[0-9]+)?' . $matches[1] . '/', '', $format);
                try {
                    $result = sprintf($format, $value);
                } catch (ValueError $exception) {
                }
            }
            return $result;
        }
    }

    /**
     * Whether or not a given $length is a compliant XHTML
     * length (e.g. "10%", 10, ...).
     *
     * @param mixed $length A length as a string or integer.
     * @return bool
     */
    public static function isXhtmlLength($length): bool
    {
        if (is_int($length)) {
            return $length >= 0;
        }

        if (is_string($length)) {
            return preg_match('/^[0-9]+%?$/', $length) === 1;
        }

        return false;
    }

    /**
     * Sanitize length to positive integer with optional percent sign or
     * special value for non-set length.
     *
     * Authorized values are:
     * * positive integers as strings or integers
     * * strings containing positive integer and "%" sign
     * * null and -1 (kept for backward compatibility) for a length non set.
     *
     * @param mixed $length length to sanitize
     * @param string $argumentName argument name for exception message
     * @return string|null length as string or null if not set
     * @throws \InvalidArgumentException when the length is not valid
     */
    public static function sanitizeXhtmlLength($length, string $argumentName): ?string
    {
        // Allows -1 as former null value still existing in compiled items.
        if ($length === null || $length === -1) {
            return null;
        }

        if (self::isXhtmlLength($length)) {
            return (string)$length;
        }

        $given = is_string($length) || is_int($length) || is_float($length)
            ? $length
            : gettype($length);
        throw new \InvalidArgumentException(
            sprintf(
                'The "%s" argument must be a positive integer with optional percent sign, "%s" given.',
                $argumentName,
                $given
            )
        );
    }

    public static function isAriaLevel(string $level): bool
    {
        return is_numeric($level) && (int)$level >= 1;
    }

    public static function isAriaIdRefs(string $ariaIdRefs): bool
    {
        $ariaValues = explode("\x20", $ariaIdRefs);
        foreach ($ariaValues as $ariaValue) {
            if (!self::isIdentifier($ariaValue, false)) {
                return false;
            }
        }

        return true;
    }

    public static function isMimeType(string $string): bool
    {
        return preg_match('/^[-a-z]+\/[-+.a-z0-9]+$/', $string) === 1;
    }

    /**
     * Is the given string a normalized string (no line break nor tabulation)?
     *
     * @param string $string
     * @return bool
     */
    public static function isNormalizedString(string $string): bool
    {
        return strpos($string, "\n") === false
            && strpos($string, "\r") === false
            && strpos($string, "\t") === false;
    }
}
