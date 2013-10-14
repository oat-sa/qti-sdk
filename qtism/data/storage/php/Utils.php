<?php

namespace qtism\data\storage\php;

/**
 * This class provides utility methods dedicated to PHP data storage.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Utils {
    
    /**
     * Whether a given $value is considered to be scalar.
     * 
     * A value will be considered scalar if it is a PHP scalar
     * value or the null value.
     * 
     * @return boolean
     */
    static public function isScalar($value) {
        return is_scalar($value) === true || is_null($value) === true;
    }
    
    /**
     * Whether a given $string represents a variable reference e.g. '$foobar'.
     * 
     * @return boolean
     */
    static public function isVariableReference($string) {
        return is_string($string) === true && mb_strpos($string, '$') === 0;
    }
}