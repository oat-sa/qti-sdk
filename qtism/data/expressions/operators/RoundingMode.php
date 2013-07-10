<?php

namespace qtism\data\expressions\operators;

use qtism\common\enums\Enumeration;

class RoundingMode implements Enumeration {
	
	const SIGNIFICANT_FIGURES = 0;
	
	const DECIMAL_PLACES = 1;
	
	public static function asArray() {
		return array(
			'SIGNIFICANT_FIGURES' => self::SIGNIFICANT_FIGURES,
			'DECIMAL_PLACES' => self::DECIMAL_PLACES
		);
	}
	
	public static function getConstantByName($name) {
		switch (strtolower($name)) {
			case 'significantfigures':
				return self::SIGNIFICANT_FIGURES;
			break;
			
			case 'decimalplaces':
				return self::DECIMAL_PLACES;
			break;
			
			default:
				return false;
			break;
		}
	}
	
	public static function getNameByConstant($constant) {
		switch ($constant) {
			case self::SIGNIFICANT_FIGURES:
				return 'significantFigures';
			break;
			
			case self::DECIMAL_PLACES:
				return 'decimalPlaces';
			break;
			
			default:
				return false;
			break;
		}
	}
}