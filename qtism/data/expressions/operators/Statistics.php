<?php

namespace qtism\data\expressions\operators;

use qtism\common\enums\Enumeration;

class Statistics implements Enumeration {
	
	/**
	 * From IMS QTI:
	 * 
	 * The arithmetic mean of the argument, which must be a container of numerical 
	 * base type, which contains a sample of observations.
	 * 
	 * @var integer
	 */
	const MEAN = 0;
	
	/**
	 * From IMS QTI:
	 * 
	 * The variance of the argument, which must be a container of numerical base type, 
	 * with containerSize greater than 1, containing a sample of observations.
	 * 
	 * @var integer
	 */
	const SAMPLE_VARIANCE = 1;
	
	/**
	 * From IMS QTI:
	 * 
	 * The standard deviation of the argument, which must be a container of numerical 
	 * base type, with containerSize greater than 1, containing a sample of observations.
	 * 
	 * @var integer
	 */
	const SAMPLE_SD = 2;
	
	/**
	 * From IMS QTI:
	 * 
	 * The variance of the argument, which must be a container of numerical base type 
	 * with containerSize greater than 1.
	 * 
	 * @var integer
	 */
	const POP_VARIANCE = 3;
	
	/**
	 * From IMS QTI:
	 * 
	 * The standard deviation of the argument, which must be a container of numerical base 
	 * type with containerSize greater than 1.
	 * 
	 * @var integer
	 */
	const POP_SD = 4;
	
	public static function asArray() {
		return array(
			'MEAN' => self::MEAN,
			'SAMPLE_VARIANCE' => self::SAMPLE_VARIANCE,
			'SAMPLE_SD' => self::SAMPLE_SD,
			'POP_VARIANCE' => self::POP_VARIANCE,
			'POP_SD' => self::POP_SD		
		);
	}
	
	public static function getConstantByName($name) {
		switch (strtolower($name)) {
			case 'mean':
				return self::MEAN;
			break;
			
			case 'samplevariance':
				return self::SAMPLE_VARIANCE;
			break;
			
			case 'samplesd':
				return self::SAMPLE_SD;
			break;
			
			case 'popvariance':
				return self::POP_VARIANCE;
			break;
			
			case 'popsd':
				return self::POP_SD;
			break;
			
			default:
				return false;
			break;
		}
	}
	
	public static function getNameByConstant($constant) {
		switch ($constant) {
			case self::MEAN:
				return 'mean';
			break;
			
			case self::SAMPLE_VARIANCE:
				return 'sampleVariance';
			break;
			
			case self::SAMPLE_SD:
				return 'sampleSD';
			break;
			
			case self::POP_VARIANCE:
				return 'popVariance';
			break;
			
			case self::POP_SD:
				return 'popSD';
			break;
			
			default:
				return false;
			break;
		}
	}
}