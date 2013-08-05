<?php

namespace qtism\runtime\tests;

use qtism\common\enums\Enumeration;

class AssessmentItemSessionState implements Enumeration {
	
	const INITIAL = 0;
	
	const INTERACTING = 1;
	
	const MODAL_FEEDBACK = 2;
	
	const SUSPENDED = 3;
	
	const CLOSED = 4;
	
	const SOLUTION = 5;
	
	const REVIEW = 6;
	
	public static function asArray() {
		return array(
			'INITIAL' => self::INITIAL,
			'INTERACTING' => self::INTERACTING,
			'MODAL_FEEDBACK' => self::MODAL_FEEDBACK,
			'SUSPENDED' => self::SUSPENDED,
			'CLOSED' => self::CLOSED,
			'SOLUTION' => self::SOLUTION,
			'REVIEW' => self::REVIEW
		);
	}
	
	public static function getConstantByName($name) {
		switch (strtolower($name)) {
			case 'initial':
				return self::INITIAL;
			break;
			
			case 'interacting':
				return self::INTERACTING;
			break;
			
			case 'modalfeedback':
				return self::MODAL_FEEDBACK;
			break;
			
			case 'suspended':
				return self::SUSPENDED;
			break;
			
			case 'closed':
				return self::CLOSED;
			break;
			
			case 'solution':
				return self::SOLUTION;
			break;
			
			case 'review':
				return self::REVIEW;
			break;
			
			default:
				return false;
			break;
		}
	}
	
	public static function getNameByConstant($constant) {
		switch ($constant) {
			case self::INITIAL:
				return 'initial';
			break;
			
			case self::INTERACTING:
				return 'interacting';
			break;
			
			case self::MODAL_FEEDBACK:
				return 'modalFeedback';
			break;
			
			case self::SUSPENDED:
				return 'suspended';
			break;
			
			case self::CLOSED:
				return 'closed';
			break;
			
			case self::SOLUTION:
				return 'solution';
			break;
			
			case self::REVIEW:
				return 'review';
			break;
			
			default:
				return false;
			break;
		}
	}
}