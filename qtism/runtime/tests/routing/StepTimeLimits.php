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
namespace qtism\runtime\tests\routing;

use qtism\data\TimeLimits;

/**
 * Represents the composition of a TimeLimits object
 * and its owner object.
 * 
 * @author <jerome@taotesting.com>
 *
 */
class StepTimeLimits extends OwnedObject {
    
    /**
     * The TimeLimits object.
     * 
     * @var TimeLimits
     */
    private $timeLimits;
    
    /**
     * Create a new StepTimeLimits object.
     * 
     * @param StructuralTestComponent $owner The owner object.
     * @param TimeLimits $timeLimits A TimeLimits object.
     */
    public function __construct(StructuralComponent $owner, TimeLimits $timeLimits) {
        parent::__construct($owner);
        $this->setTimeLimits($timeLimits);
    }
    
    /**
     * Get the TimeLimits object.
     * 
     * @return TimeLimits
     */
    public function getTimeLimits() {
        return $this->timeLimits;
    }
    
    /**
     * Set the TimeLimits object.
     * 
     * @param TimeLimits $timeLimits
     */
    public function setTimeLimits(TimeLimits $timeLimits) {
        $this->timeLimits = $timeLimits;
    }
}