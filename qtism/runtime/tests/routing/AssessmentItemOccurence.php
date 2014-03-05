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
use qtism\data\ItemSessionControl;
use qtism\data\AssessmentItemRef;

/**
 * Represents an occurence of AssessmentItemRef in a Step.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class AssessmentItemOccurence implements StructuralComponent {
    
    /**
     * The AssessmentItemRef object composing the AssessmentItemOccurence.
     * 
     * @var AssessmentItemRef
     */
    private $assessmentItemRef;
    
    /**
     * The occurence number of this AssessmentItemOccurence.
     * 
     * @var integer
     */
    private $occurence;
    
    private $itemSessionControl;
    
    private $timeLimits;
    
    /**
     * Create a new AssessmentItemOccurence object.
     * 
     * @param AssessmentItemRef $assessmentItemRef The AssessmentItemRef object composing the item occurence.
     * @param integer $occurence An occurence number.
     */
    public function __construct(AssessmentItemRef $assessmentItemRef, $occurence = 0) {
        $this->setAssessmentItemRef($assessmentItemRef);
        $this->setOccurence($occurence);
    }
    
    /**
     * Get the identifier of the item occurence. The identifier is built from
     * the assessmentItemRef's identifier + the occurence number e.g. "Q01.0" for
     * the first occurence of the item reference "Q01".
     * 
     * @return string
     */
    public function getIdentifier() {
        return $this->getAssessmentItemRef()->getIdentifier() . '.' . $this->getOccurence();
    }
    
    /**
     * Set the occurence number of this item occurence.
     * 
     * @param integer $occurence An occurence number.
     */
    public function setOccurence($occurence) {
        $this->occurence = $occurence;
    }
    
    /**
     * Get the occurence number of this item occurence.
     * 
     * @return number An occurence number.
     */
    public function getOccurence() {
        return $this->occurence;
    }
    
    /**
     * Get the AssessmentItemRef object composing this item occurence.
     * 
     * @return AssessmentItemRef An AssessmentItemRef object.
     */
    public function getAssessmentItemRef() {
        return $this->assessmentItemRef;
    }
    
    /**
     * Set the AssessmentItemRef object composing this item occurence.
     * 
     * @param AssessmentItemRef $assessmentItemRef An AssessmentItemRef object.
     */
    public function setAssessmentItemRef(AssessmentItemRef $assessmentItemRef) {
        $this->assessmentItemRef = $assessmentItemRef;
    }
    
    public function setItemSessionControl(ItemSessionControl $itemSessionControl = null) {
        $this->getAssessmentItemRef()->setItemSessionControl($itemSessionControl);
    }
    
    public function getItemSessionControl() {
        return $this->getAssessmentItemRef()->getItemSessionControl();
    }
    
    public function setTimeLimits(TimeLimits $timeLimtis = null) {
        $this->getAssessmentItemRef()->setTimeLimits($timeLimtis);
    }
    
    public function getTimeLimits() {
        return $this->getAssessmentItemRef()->getTimeLimits();
    }
    
    /**
     * Actually returns the identifier of the item occurence e.g. "Q01.0".
     * 
     * @return string
     */
    public function __toString() {
        return $this->getIdentifier();
    }
}