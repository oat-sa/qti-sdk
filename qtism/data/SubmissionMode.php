<?php

namespace qtism\data;

use qtism\common\enums\Enumeration;

/**
 * Submission Mode enumeration.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class SubmissionMode implements Enumeration {
	
	const INDIVIDUAL = 0;
	
	const SIMULTANEOUS = 1;
	
	public static function asArray() {
		return array(
			'INDIVIDUAL' => self::INDIVIDUAL,
			'SIMULTANEOUS' => self::SIMULTANEOUS
		);
	}
	
	public static function getConstantByName($name) {
		switch (strtolower($name)) {
			case 'individual':
				return self::INDIVIDUAL;
			break;
			
			case 'simultaneous':
				return self::SIMULTANEOUS;
			break;
			
			default:
				return false;
			break;
		}
	}
	
	public static function getNameByConstant($constant) {
		switch ($constant) {
			case self::INDIVIDUAL:
				return 'individual';
			break;
			
			case self::SIMULTANEOUS:
				return 'simultaneous';
			break;
			
			default:
				return false;
			break;
		}
	}
}