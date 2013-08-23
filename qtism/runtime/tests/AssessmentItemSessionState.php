<?php

namespace qtism\runtime\tests;

use qtism\common\enums\Enumeration;

/**
 * The AssessmentItemSessionState enumeration describes the possible
 * states an item session can get during its lifecycle.
 * 
 * @author Jérôme Bogaerts
 *
 */
class AssessmentItemSessionState extends AssessmentTestSessionState {
	
	const SOLUTION = 5;
	
	const REVIEW = 6;
	
	public static function asArray() {
		return array_merge(AssessmentTestSessionState::asArray(), array(
			'SOLUTION' => self::SOLUTION,
			'REVIEW' => self::REVIEW
		));
	}
	
	public static function getConstantByName($name) {
		switch (strtolower($name)) {
			case 'solution':
				return self::SOLUTION;
			break;
			
			case 'review':
				return self::REVIEW;
			break;
			
			default:
				return AssessmentTestSessionState::getConstantByName($name);
			break;
		}
	}
	
	public static function getNameByConstant($constant) {
		switch ($constant) {
			case self::SOLUTION:
				return 'solution';
			break;
			
			case self::REVIEW:
				return 'review';
			break;
			
			default:
				return AssessmentTestSessionState::getConstantByName($name);
			break;
		}
	}
}