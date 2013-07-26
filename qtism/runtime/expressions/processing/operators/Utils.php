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
}