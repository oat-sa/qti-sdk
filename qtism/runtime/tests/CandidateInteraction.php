<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 * @license GPLv2
 * @package qtism
 * @subpackage
 *
 */
namespace qtism\runtime\tests;

use qtism\common\enums\Enumeration;

/**
 * An enumeration representing the possible candidate
 * interactions within a test session.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class CandidateInteraction implements Enumeration {
    
    const UNKNOWN = 0;
    
    const SKIP = 1;
    
    const BEGIN_ATTEMPT = 2;
    
    const END_ATTEMPT = 3;
    
    const JUMP_TO = 4;
    
    const MOVE_NEXT = 5;
    
    const MOVE_BACK = 6;
    
    static public function asArray() {
        return array(
            'UNKNOWN' => self::UNKNOWN,
            'SKIP' => self::SKIP,
            'BEGIN_ATTEMPT' => self::BEGIN_ATTEMPT,
            'END_ATTEMPT' => self::END_ATTEMPT,
            'JUMP_TO' => self::JUMP_TO,
            'MOVE_NEXT' => self::MOVE_NEXT,
            'MOVE_BACK' => self::MOVE_BACK
        );
    }
    
    static public function getConstantByName($name) {
        switch (strtolower($name)) {
            case 'unknown':
                return self::UNKNOWN;
            break;
            
            case 'skip':
                return self::SKIP;
            break;
            
            case 'beginAttempt':
                return self::BEGIN_ATTEMPT;
            break;
            
            case 'endAttempt':
                return self::END_ATTEMPT;
            break;
            
            case 'jumpTo':
                return self::JUMP_TO;
            break;
            
            case 'moveNext':
                return self::MOVE_NEXT;
            break;
            
            case 'moveBack':
                return self::MOVE_BACK;
            break;
            
            default:
                return false;
            break;
        }
    }
    
    static public function getNameByConstant($constant) {
        switch ($constant) {
            case self::UNKNOWN:
                return 'unknown';
            break;
            
            case self::SKIP:
                return 'skip';
            break;
            
            case self::BEGIN_ATTEMPT:
                return 'beginAttempt';
            break;
            
            case self::END_ATTEMPT:
                return 'endAttempt';
            break;
            
            case self::JUMP_TO:
                return 'jumpTo';
            break;
            
            case self::MOVE_NEXT:
                return 'moveNext';
            break;
            
            case self::MOVE_BACK:
                return 'moveBack';
            break;
            
            default:
                return false;
            break;
        }
    }
}