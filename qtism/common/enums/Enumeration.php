<?php

namespace qtism\common\enums;

interface Enumeration {
	
	/**
	 * Return the possible values of the enumeration as an array.
	 * 
	 * @return An associative array where keys are constant names (as they appear in the code) and values are constant values.
	 */
	public static function asArray();
	
	/**
	 * Get a constant value by its name. If $name does not match any of the value
	 * of the enumeration, false is returned.
	 * 
	 * @param integer|false $name The value relevant to $name or false if not found.
	 */
	public static function getConstantByName($name);
	
	/**
	 * Get a constant name by its value. If $constant does not match any of the names
	 * of the enumeration, false is returned.
	 * 
	 * @param string|false $constant The relevant name or false if not found.
	 */
	public static function getNameByConstant($constant);
}