<?php

namespace qtism\common\enums;

interface Enumeration {
	
	/**
	 * Return the possible values of the enumeration as an array.
	 * 
	 * @return An associative array where keys are constant names and values are constant values.
	 */
	public static function asArray();
	
	public static function getConstantByName($name);
	
	public static function getNameByConstant($constant);
}