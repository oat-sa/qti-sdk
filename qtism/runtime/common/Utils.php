<?php

namespace qtism\runtime\common;

use qtism\common\datatypes\Duration;
use qtism\common\datatypes\Pair;
use qtism\common\datatypes\DirectedPair;
use qtism\common\datatypes\Point;
use qtism\common\enums\BaseType;
use qtism\common\utils\Format;
use \InvalidArgumentException;
use \RuntimeException;

class Utils {
	
	/**
	 * Whether a given primitive $value is compliant with the QTI runtime model.
	 *
	 * Compliant primitive values are:
	 *
	 * * string (qti:string, qti:identifier)
	 * * integer
	 * * float
	 * * double (qti:float)
	 * * boolean
	 * * Duration
	 * * Point
	 * * Pair
	 * * DirectedPair
	 * * NULL
	 *
	 * @param mixed $value A value you want to check the compatibility with the QTI runtime model.
	 * @return boolean
	 */
	public static function isRuntimeCompliant($value) {
		$primitiveTypes = array('integer', 'float', 'double', 'string', 'boolean');
	
		if ($value === null || in_array(gettype($value), $primitiveTypes)) {
			return true;
		}
		else if (gettype($value) == 'object') {
			if ($value instanceof Duration ||
					$value instanceof Pair ||
					$value instanceof Point) {
				return true;
			}
		}
	
		return false;
	}
	
	/**
	 * Whether a given $value is compliant with a given $baseType.
	 * 
	 * @param int $baseType A value from the BaseType enumeration.
	 * @param mixed $value A value.
	 * @throws InvalidArgumentException If $baseType is not a value from the BaseType enumeration.
	 * @return boolean
	 */
	public static function isBaseTypeCompliant($baseType, $value) {
		
		if ($value === null) {
			return true; // A value can always be null.
		}
		
		switch ($baseType) {
			case BaseType::BOOLEAN:
				return is_bool($value);
			break;
					
			case BaseType::DIRECTED_PAIR:
				return $value instanceof DirectedPair;
			break;
					
			case BaseType::DURATION:
				return $value instanceof Duration;
			break;
					
			case BaseType::FILE:
				return Format::isFile($value);
			break;
					
			case BaseType::FLOAT:
				return is_float($value) || is_double($value);
			break;
					
			case BaseType::IDENTIFIER:
				return Format::isIdentifier($value);
			break;
					
			case BaseType::INT_OR_IDENTIFIER:
				return Format::isIdentifier($value) || is_int($value);
			break;
					
			case BaseType::INTEGER:
				return is_int($value);
			break;
					
			case BaseType::PAIR:
				return $value instanceof Pair;
			break;
					
			case BaseType::POINT:
				return $value instanceof Point;
			break;
					
			case BaseType::STRING:
				return is_string($value);
			break;
					
			case BaseType::URI:
				return Format::isUri($value);
			break;
			
			default:
				$msg = "Unknown baseType '" . $baseType . "'.";
				throw new InvalidArgumentException($msg);
			break;
		}
	}
	
	/**
	 * Throw an InvalidArgumentException depending on a PHP in-memory value.
	 *
	 * @param mixed $value A given PHP primitive value.
	 * @throws InvalidArgumentException In any case.
	 */
	public static function throwTypingError($value) {
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
	 * @throws InvalidArgumentException In any case.
	 */
	public static function throwBaseTypeTypingError($baseType, $value) {
		$givenValue = (gettype($value) == 'object') ? get_class($value) : gettype($value);
		$acceptedTypes = BaseType::getNameByConstant($baseType);
		$msg = "The value '${givenValue}' is not compliant with the '${acceptedTypes}' baseType.";
		throw new InvalidArgumentException($msg);
	}
}