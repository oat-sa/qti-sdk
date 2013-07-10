<?php

namespace qtism\common\datatypes;

use qtism\common\enums\Enumeration;

/**
 * From IMS QTI:
 * 
 * A value of a shape is alway accompanied by coordinates (see coords and an associated
 * image which provides a context for interpreting them.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Shape implements Enumeration {
	
	/**
	 * Note: Corresponds to QTI shape::default. Unfortunalty, 'default' is a reserved
	 * token in PHP.
	 * 
	 * From IMS QTI:
	 * 
	 * The default shape refers to the entire area of the associated image.
	 * 
	 * @var int
	 */
	const DEF = 0;
	
	/**
	 * From IMS QTI:
	 * 
	 * A rectangular region.
	 * 
	 * @var int
	 */
	const RECT = 1;
	
	/**
	 * From IMS QTI:
	 * 
	 * A circular region.
	 * 
	 * @var int
	 */
	const CIRCLE = 2;
	
	/**
	 * From IMS QTI:
	 * 
	 * An arbitrary polygonal region.
	 * 
	 * @var int
	 */
	const POLY = 3;
	
	/**
	 * From IMS QTI:
	 * 
	 * This value is deprecated, but is included for compatibility with version 
	 * of 1 of the QTI specification. Systems should use circle or poly shapes instead.
	 * 
	 * @var int
	 * @deprecated
	 */
	const ELLIPSE = 4;
	
	public static function asArray() {
		return array(
			'DEF' => self::DEF,
			'RECT' => self::RECT,
			'CIRCLE' => self::CIRCLE,
			'POLY' => self::POLY,
			'ELLIPSE' => self::ELLIPSE	
		);
	}
	
	public static function getConstantByName($name) {
		switch (strtolower($name)) {
			case 'default':
				return self::DEF;
			break;
			
			case 'rect':
				return self::RECT;
			break;
			
			case 'circle':
				return self::CIRCLE;
			break;
			
			case 'poly':
				return self::POLY;
			break;
			
			case 'ellipse':
				return self::ELLIPSE;
			break;
			
			default:
				return false;
			break;
		}
	}
	
	public static function getNameByConstant($constant) {
		switch ($constant) {
			case self::DEF:
				return 'default';
			break;
			
			case self::RECT:
				return 'rect';
			break;
			
			case self::CIRCLE:
				return 'circle';
			break;
			
			case self::POLY:
				return 'poly';
			break;
			
			case self::ELLIPSE:
				return 'ellipse';
			break;
			
			default:
				return false;
			break;
		}
	}
}