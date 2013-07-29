<?php

namespace qtism\runtime\expressions\processing\operators;

/**
 * A utility class for all sub-classes of the OperatorProcessor class.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Utils {
	
	/**
	 * Compute the GCD (Greatest Common Divider) of $a and $b.
	 * 
	 * If either $a or $b is negative, its absolute value will be used
	 * instead.
	 * 
	 * @param integer $a A positive integer
	 * @param integer $b A positive integer
	 * @return integer The GCD of $a and $b.
	 */
	public static function gcd($a, $b) {
		$a = abs($a);
		$b = abs($b);
		
		$k = max($a, $b);
		$m = min($a, $b);
		
		while ($m !== 0) {
			$r = $k % $m;
			$k = $m;
			$m = $r;
		}
		
		return $k;
	}
	
	/**
	 * Compute LCM (Least Common Multiple) of $a and $b.
	 * 
	 * @param integer $a
	 * @param integer $b
	 * @return integer the LCM of $a and $b.
	 */
	public static function lcm($a, $b) {
		$a = abs($a);
		$b = abs($b);
		
		if ($a === 0 || $b === 0) {
			return 0;
		}
		
		$a = $a / self::gcd($a, $b);
		return $a * $b; 
	}
	
	/**
	 * Add an appropriate delimiter (/) to a regular expression that has no delimiters. This
	 * method is multi-byte safe safe.
	 *
	 * @return string|boolean The delimited string or false if no appropriate delimiters can be found.
	 */
	public static function pregAddDelimiter($string) {
		
		return '/' . static::escapeSymbols($string, '/') . '/';
	}
	
	/**
	 * Get the amout of backslash (\) characters in $string that precede $offset.
	 *
	 * @param string $string
	 * @param integer $offset
	 * @return integer
	 */
	public static function getPrecedingBackslashesCount($string, $offset) {
		$count = 0;
	
		if ($offset < strlen($string)) {
			for ($i = $offset; $i > 0; $i--) {
				if ($string[$i - 1] === '\\') {
					$count++;
				}
				else {
					break;
				}
			}
		}
	
		return $count;
	}
	
	/**
	 * Escape with a backslash (\) the $symbols in $string.
	 * 
	 * @param string $string
	 * @param array|string $symbols An array of symbols or a single symbol.
	 * @return string The escaped string.
	 */
	public static function escapeSymbols($string, $symbols) {
		
		if (!is_array($symbols)) {
			$symbols = array($symbols);
		}
		
		$len = mb_strlen($string, 'UTF-8');
		$returnValue = '';
		
		for ($i = 0; $i < $len; $i++) {
			$char = mb_substr($string, $i, 1); // get a multi-byte char.
			if (in_array($char, $symbols) === true) {
				
				// Check escaping.
				// If the amount of preceding backslashes is odd, it is escaped.
				// If the amount of preceding backslashes is even, it is not escaped.
				if (static::getPrecedingBackslashesCount($string, $i) % 2 === 0) {
					// It is not escaped, so ecape it.
					$returnValue .= '\\';
				}
			}
		
			$returnValue .= $char;
		}
		
		return $returnValue;
	}
}