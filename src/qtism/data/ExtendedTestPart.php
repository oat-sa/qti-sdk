<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * Copyright (c) 2013-2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data;

/**
 * The ExtendedTestPart class is an extended representation of the QTI
 * testPart class. It gathers together the testPart + additional references
 * to testFeedback components.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ExtendedTestPart extends TestPart
{
    /**
     * Create a new ExtendedTestPart object.
     * 
     * @param string $identifier An identifier.
     * @param \qtism\data\AssessmentSectionCollection $assessmentSections A collection of AssessmentSection objects.
     * @param integer $navigationMode A value from the NavigationMode enumeration.
     * @param integer $submissionMode A value from the SubmissionMode enumeration.
     * @throws \InvalidArgumentException If any of the arguments is invalid.
     */
    public function __construct($identifier, AssessmentSectionCollection $assessmentSections, $navigationMode = NavigationMode::LINEAR, $submissionMode = SubmissionMode::INDIVIDUAL)
    {
        parent::__construct($identifier, $assessmentSections, $navigationMode, $submissionMode);
        $this->setTestFeedbackRefs(new TestFeedbackRefCollection());
    }
    
    /**
     * A collection of TestFeedbackRef objects.
     * 
     * @var \qtism\data\Test
     * @qtism-bean-property
     */
    private $testFeedbackRefs;
    
    /**
     * Set the collection of TestFeedbackRef objects.
     * 
     * @param \qtism\data\TestFeedbackRefCollection $testFeedbackRefs
     */
    public function setTestFeedbackRefs(TestFeedbackCollection $testFeedbackRefs)
    {
        $this->testFeedbackRefs = $testFeedbackRefs;
    }
    
    /**
     * Get the collection of TestFeedbackRef objects.
     * 
     * @return \qtism\data\TestFeedbackRefCollection
     */
    public function getTestFeedbackRefs()
    {
        return $this->testFeedbackRefs;
    }
    
    /**
     * Add a TestFeedbackRef to the ExtendedTestPart.
     * 
     * @param \qtism\data\TestFeedbackRef $testFeedbackRef
     */
    public function addTestFeedbackRef(TestFeedbackRef $testFeedbackRef)
    {
        $this->getTestFeedbackRefs()->attach($testFeedbackRef);
    }
    
    /**
     * Remove a TestFeedbackRef from the ExtendedTestPart.
     * 
     * @param \qtism\data\TestFeedbackRef $testFeedbackRef
     */
    public function removeTestFeedbackRef(TestFeedbackRef $testFeedbackRef)
    {
        $this->getTestFeedbackRefs()->detach($testFeedbackRef);
    }
    
    /**
     * Create a new ExtendedTestPart object from another TestPart object.
     * 
     * @param \qtism\data\TestPart $testPart
     */
    static public function createFromTestPart(TestPart $testPart)
    {
        $ref = new TestPart(
            $testPart->getIdentifier(), 
            $testPart->getAssessmentSections(),
            $testPart->getNavigationMode(),
            $testPart->setSubmissionMode()
        );
        
        $ref->setPreConditions($testPart->getPreConditions());
        $ref->setBranchRules($testPart->getBranchRules());
        $ref->setItemSessionControl($testPart->getItemSessionControl());
        $ref->setTestFeedbacks($testPart->getTestFeedbacks());
    }
    
    /**
     * @see \qtism\data\TestPart::getComponents()
     */
    public function getComponents()
    {
        $components = array_merge(
            parent::getComponents()->getArrayCopy(),
            $this->getTestFeedbackRefs()->getArrayCopy()                
        );
        
        return new QtiComponentCollection($components);
    }
}
