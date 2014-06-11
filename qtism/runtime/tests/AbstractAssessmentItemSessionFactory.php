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
 * Copyright (c) 2013-2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 * @license GPLv2
 * @package qtism
 * 
 *
 */
namespace qtism\runtime\tests;

use qtism\data\SubmissionMode;
use qtism\data\NavigationMode;
use qtism\data\IAssessmentItem;

/**
 * A bed for AssessmentItemSession objects creation.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
abstract class AbstractAssessmentItemSessionFactory {
    
    /**
     * Whether or not sessions built in the factory
     * will consider minimum time constraints.
     * 
     * @var boolean
     */
    private $considerMinTime;
    
    /**
     * Create a new AbstractAssessmentItemSessionFactory object.
     * 
     * @param boolean $considerMinTime Whether or not sessions built by the factory will consider minimum time constraints.
     */
    public function __construct($considerMinTime = true) {
        $this->setConsiderMinTime($considerMinTime);
    }
    
    /**
     * Set whether or not sessions created by this factory will consider minimum time constraints.
     * 
     * @param boolean $considerMinTime
     */
    public function setConsiderMinTime($considerMinTime) {
        $this->considerMinTime = $considerMinTime;
    }
    
    /**
     * Whether or not sessions created by this factory will consider minimum time constraints.
     * 
     * @return boolean
     */
    public function mustConsiderMinTime() {
        return $this->considerMinTime;
    }
    
    /**
     * Create a pristine AssessmentItemSession object
     * 
     * @param IAssessmentItem $assessmentItem The IAssessmentItem object representing the item to be run.
     * @param integer $navigationMode A value from the NavigationMode enumeration.
     * @param integer $submissionMode A value from the SubmissionMode enumeration.
     * @return AssessmentItemSession A pristine AssessmentItemSession object.
     */
    public abstract function createAssessmentItemSession(IAssessmentItem $assessmentItem, $navigationMode = NavigationMode::LINEAR, $submissionMode = SubmissionMode::INDIVIDUAL);
    
}