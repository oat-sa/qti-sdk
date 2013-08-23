<?php

namespace qtism\runtime\tests;

use qtism\common\enums\Enumeration;

/**
 * The AssessmentTestSessionState enumeration describe the possible state
 * a test session can get during its lifecycle.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class AssessmentTestSessionState implements Enumeration {
    
    const INITIAL = 0;
    
    const INTERACTING = 1;
    
    const MODAL_FEEDBACK = 2;
    
    const SUSPENDED = 3;
    
    const CLOSED = 4;
    
    public static function asArray() {
        return array(
            'INITIAL' => self::INITIAL,
            'INTERACTING' => self::INTERACTING,
            'MODAL_FEEDBACK' => self::MODAL_FEEDBACK,
            'SUSPENDED' => self::SUSPENDED,
            'CLOSED' => self::CLOSED
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
	            	
	        default:
	            return false;
	        break;
	    }
	}
}