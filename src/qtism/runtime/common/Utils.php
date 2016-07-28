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
 * Copyright (c) 2013-2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 *
 */

namespace qtism\runtime\common;

use qtism\common\collections\Container;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiIntOrIdentifier;
use qtism\common\datatypes\QtiUri;
use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiDatatype;
use qtism\common\enums\Cardinality;
use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiPoint;
use qtism\common\enums\BaseType;
use \InvalidArgumentException;


/**
 * Utility class gathering utility methods for the \qtism\runtime\common
 * namespace.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Utils
{
    /**
     * Whether a given $value is compliant with the QTI runtime model. In other words,
     * if it is null or a QTI Scalar Datatype.
     *
     * @param mixed $value A value you want to check the compatibility with the QTI runtime model.
     * @return boolean
     */
    static public function isQtiScalarDatatypeCompliant($value)
    {
        if ($value === null) {
            return true;
        } elseif ($value instanceof QtiDatatype) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Whether a given $value is compliant with a given $baseType.
     *
     * @param int $baseType A value from the BaseType enumeration.
     * @param mixed $value A value.
     * @return boolean
     */
    static public function isBaseTypeCompliant($baseType, $value)
    {
        if ($value === null) {
            return true; // A value can always be null.
        } elseif ($value instanceof QtiDatatype && $baseType === $value->getBaseType()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Whether a given $cardinality is compliant with a given $value.
     *
     * @param integer $cardinality
     * @param mixed $value
     * @return boolean
     */
    static public function isCardinalityCompliant($cardinality, $value)
    {
        if ($value === null) {
            return true;
        } elseif ($value instanceof QtiDatatype && $cardinality === $value->getCardinality()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Throw an InvalidArgumentException depending on a PHP in-memory value.
     *
     * @param mixed $value A given PHP primitive value.
     * @throws \InvalidArgumentException In any case.
     */
    static public function throwTypingError($value)
    {
        $givenValue = (gettype($value) == 'object') ? get_class($value) : gettype($value);
        $acceptedTypes = array('boolean', 'integer', 'float', 'double', 'string', 'Duration', 'Pair', 'DirectedPair', 'Point');
        $acceptedTypes = implode(", ", $acceptedTypes);
        $msg = "A value is not compliant with the QTI runtime model datatypes: ${acceptedTypes} . '${givenValue}' given.";
        throw new InvalidArgumentException($msg);
    }

    /**
     * Throw an InvalidArgumentException depending on a given qti:baseType
     * and an in-memory PHP value.
     *
     * @param int $baseType A value from the BaseType enumeration.
     * @param mixed $value A given PHP primitive value.
     * @throws \InvalidArgumentException In any case.
     */
    static public function throwBaseTypeTypingError($baseType, $value)
    {
        $givenValue = (gettype($value) == 'object') ? get_class($value) : gettype($value) . ':' . $value;
        $acceptedTypes = BaseType::getNameByConstant($baseType);
        $msg = "The value '${givenValue}' is not compliant with the '${acceptedTypes}' baseType.";
        throw new InvalidArgumentException($msg);
    }

    /**
     * Infer the QTI baseType of a given $value.
     *
     * @param mixed $value A value you want to know the QTI baseType.
     * @return integer|false A value from the BaseType enumeration or false if the baseType could not be infered.
     */
    static public function inferBaseType($value)
    {
        if ($value === null) {
            return false;
        } elseif ($value instanceof RecordContainer) {
            return false;
        } elseif ($value instanceof QtiDatatype) {
            return $value->getBaseType();
        } else {
            return false;
        }
    }

    /**
     * Infer the cardinality of a given $value.
     *
     * Please note that:
     *
     * * A RecordContainer has no cardinality, thus it always returns false for such a container.
     * * The null value has no cardinality, this it always returns false for such a value.
     *
     * @param mixed $value A value you want to infer the cardinality.
     * @return integer|boolean A value from the Cardinality enumeration or false if it could not be infered.
     */
    static public function inferCardinality($value)
    {
        if ($value === null) {
            return false;
        } elseif ($value instanceof QtiDatatype) {
            return $value->getCardinality();
        } else {
            return false;
        }
    }

    /**
     * Whether a given $string is a valid variable identifier.
     *
     * Q01			-> Valid
     * Q_01			-> Valid
     * 1_Q01		-> Invalid
     * Q01.SCORE	-> Valid
     * Q-01.1.Score	-> Valid
     * Q*01.2.Score	-> Invalid
     *
     * @param string $string A string value.
     * @return boolean Whether the given $string is a valid variable identifier.
     */
    static public function isValidVariableIdentifier($string)
    {
        if (gettype($string) !== 'string' || empty($string)) {
            return false;
        }

        $pattern = '/^[a-z][a-z0-9_\-]*(?:(?:\.[1-9][0-9]*){0,1}(?:\.[a-z][a-z0-9_\-]*){0,1}){0,1}$/iu';

        return preg_match($pattern, $string) === 1;
    }

    /**
     * Transforms the content of float array to an integer array.
     *
     * @param array $floatArray An array containing float values.
     * @return array An array containing integer values.
     */
    static public function floatArrayToInteger($floatArray)
    {
        $integerArray = array();
        foreach ($floatArray as $f) {
            $integerArray[] = (is_null($f) === false) ? intval($f) : null;
        }

        return $integerArray;
    }

    /**
     * Transforms the content of an integer array to a float array.
     *
     * @param array $integerArray An array containing integer values.
     * @return array An array containing float values.
     */
    static public function integerArrayToFloat($integerArray)
    {
        $floatArray = array();
        foreach ($integerArray as $i) {
            $floatArray[] = (is_null($i) === false) ? floatval($i) : null;
        }

        return $floatArray;
    }

    /**
     * Transform a given PHP scalar value to a QtiScalar equivalent object.
     *
     * @param mixed|null $v
     * @param integer $baseType A value from the BaseType enumeration.
     * @return \qtism\common\datatypes\QtiScalar
     */
    static public function valueToRuntime($v, $baseType)
    {
        if ($v !== null) {

            if (is_int($v) === true) {

                if ($baseType === -1 || $baseType === BaseType::INTEGER) {
                    return new QtiInteger($v);
                } elseif ($baseType === BaseType::INT_OR_IDENTIFIER) {
                    return new QtiIntOrIdentifier($v);
                }
            } elseif (is_string($v) === true) {

                if ($baseType === BaseType::IDENTIFIER) {
                    return new QtiIdentifier($v);
                }
                if ($baseType === -1 || $baseType === BaseType::STRING) {
                    return new QtiString($v);
                } elseif ($baseType === BaseType::URI) {
                    return new QtiUri($v);
                } elseif ($baseType === BaseType::INT_OR_IDENTIFIER) {
                    return new QtiIntOrIdentifier($v);
                }
            } elseif (is_float($v) === true) {
                return new QtiFloat($v);
            } elseif (is_bool($v) === true) {
                return new QtiBoolean($v);
            }

        }

        return $v;
    }
    
    /**
     * Whether or not a QtiDatatype is considered to be null.
     * 
     * As per the QTI specification, the NULL value, empty strings and empty containers
     * are always treated as NULL values.
     * 
     * @param \qtism\common\datatypes\QtiDatatype $value
     * @return boolean
     */
    static public function isNull(QtiDatatype $value = null)
    {
        return is_null($value) === true || ($value instanceof QtiString && $value->getValue() === '') || ($value instanceof Container && count($value) === 0);
    }
    
    /**
     * Whether or not two QtiDatatype instances are equals. 
     * 
     * Because the runtime model
     * also deals with null values, this utility method helps to determine equality
     * easily, without testing specifically if one or both values are null prior
     * to perform QtiDatatype::equals().
     * 
     * @param QtiDatatype $a
     * @param QtiDatatype $b
     * @return boolean
     */
    static public function equals(QtiDatatype $a = null, QtiDatatype $b = null)
    {
        return (is_null($a) ? is_null($b) : $a->equals($b));
    }
}
