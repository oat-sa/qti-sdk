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

use qtism\data\content\RubricBlockRefCollection;
use qtism\data\rules\BranchRule;
use qtism\data\rules\PreCondition;
use qtism\data\rules\BranchRuleCollection;
use qtism\data\rules\PreConditionCollection;

/**
 * A step is the main composite object composing a Route.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Step {
    
    /**
     * The AssessmentTest bound to the Step.
     * 
     * @var AssessmentTest
     */
    private $assessmentTest;
    
    /**
     * The TestPart bound to the Step.
     * 
     * @var TestPart
     */
    private $testPart;
    
    /**
     * A collection of AssessmentSection objects bound to the Step.
     * 
     * @var AssessmentSectionCollection
     */
    private $assessmentSections;
    
    /**
     * The AssessmentItemOccurence bound to the Step.
     * 
     * @var AssessmentItemOccurence
     */
    private $assessmentItemOccurence;
    
    /**
     * A collection of PreCondition objects bound to the Step.
     * 
     * @var PreConditionCollection
     */
    private $preConditions;
    
    /**
     * A collection of BranchRule objects bound to the Step.
     * 
     * @var BranchRuleCollection
     */
    private $branchRules;
    
    /**
     * Create a new Step object.
     * 
     * @param AssessmentTest $assessmentTest
     * @param TestPart $testPart
     * @param AssessmentSectionCollection $assessmentSections
     * @param AssessmentItemOccurence $assessmentItemOccurence
     */
    public function __construct(AssessmentTest $assessmentTest, TestPart $testPart, AssessmentSectionCollection $assessmentSections, AssessmentItemOccurence $assessmentItemOccurence) {
        $this->setAssessmentTest($assessmentTest);
        $this->setTestPart($testPart);
        $this->setAssessmentSections($assessmentSections);
        $this->setAssessmentItemOccurence($assessmentItemOccurence);
        $this->setPreConditions(new PreConditionCollection());
        $this->setBranchRules(new BranchRuleCollection());
    }
    
    /**
     * Set the AssessmentTest object bound to the Step.
     * 
     * @param AssessmentTest $assessmentTest
     */
    public function setAssessmentTest(AssessmentTest $assessmentTest) {
        $this->assessmentTest = $assessmentTest;
    }
    
    /**
     * Get the AssessmentTest object bound to the Step.
     * 
     * @return AssessmentTest
     */
    public function getAssessmentTest() {
        return $this->assessmentTest;
    }
    
    /**
     * Set the TestPart object bound to the Step.
     * 
     * @param TestPart $testPart
     */
    public function setTestPart(TestPart $testPart) {
        $this->testPart = $testPart;
    }
    
    /**
     * Get the TestPart object bound to the Step.
     * 
     * @return TestPart
     */
    public function getTestPart() {
        return $this->testPart;
    }
    
    /**
     * Set the collection of AssessmentSection objects bound to the Step.
     * 
     * @param AssessmentSectionCollection $assessmentSections
     */
    public function setAssessmentSections(AssessmentSectionCollection $assessmentSections) {
        $this->assessmentSections = $assessmentSections;
    }
    
    /**
     * Get the collection of AssessmentSection objects bound to the Step.
     * 
     * @return AssessmentSectionCollection
     */
    public function getAssessmentSections() {
        return $this->assessmentSections;
    }
    
    /**
     * Get the unique AssessmentSection object bound to the Step. If the Step
     * is bound to multiple assessment sections, the nearest parent of the Step item's assessment section
     * will be returned.
     * 
     * @return AssessmentSection An AssessmentSection object.
     */
    public function getAssessmentSection() {
        $assessmentSections = $this->getAssessmentSections()->getArrayCopy();
        return $assessmentSections[count($assessmentSections) - 1];
    }
    
    /**
     * Set the AssesssmentItemOccurence object bound to the Step.
     * 
     * @param AssessmentItemOccurence $assessmentItemOccurence
     */
    public function setAssessmentItemOccurence(AssessmentItemOccurence $assessmentItemOccurence) {
        $this->assessmentItemOccurence = $assessmentItemOccurence;
    }
    
    /**
     * Get the AssessmentItemOccurence object bound to the Step.
     * 
     * @return AssessmentItemOccurence
     */
    public function getAssessmentItemOccurence() {
        return $this->assessmentItemOccurence;
    }
    
    /**
     * Set the collection of PreCondition objects bound to the Step.
     * 
     * @param PreConditionCollection $preConditions
     */
    protected function setPreConditions(PreConditionCollection $preConditions) {
        $this->preConditions = $preConditions;
    }
    
    /**
     * Get the collection of PreCondition objects bound to the Step.
     * 
     * @return PreConditionCollection A collection of PreCondition objects.
     */
    public function getPreConditions() {
        return $this->preConditions;
    }
    
    /**
     * Set the collection of PreCondition objects bound to the Step.
     * 
     * @param BranchRuleCollection $branchRules A collection of BranchRule objects.
     */
    protected function setBranchRules(BranchRuleCollection $branchRules) {
        $this->branchRules = $branchRules;
    }
    
    /**
     * Get the collection of BranchRule objects bound to the Step.
     * 
     * @return BranchRule A collection of BranchRule objects.
     */
    public function getBranchRules() {
        return $this->branchRules;
    }
    
    /**
     * Add a PreCondition to the Step.
     * 
     * @param Precondition $preCondition
     */
    public function addPrecondition(PreCondition $preCondition) {
        $this->preConditions[] = $preCondition;
    } 
    
    public function addPreconditions(PreConditionCollection $preConditions) {
        $this->preConditions->merge($preConditions);
    } 
    
    /**
     * Add a BranchRule to the Step.
     * 
     * @param BranchRule $branchRule
     */
    public function addBranchRule(BranchRule $branchRule) {
        $this->branchRules = $branchRule;
    }
    
    public function addBranchRules(BranchRuleCollection $branchRules) {
        $this->branchRules->merge($branchRules);
    }
    
    /**
     * Get the navigation mode in force for the Step.
     * 
     * @return integer A value from the NavigationMode enumeration.
     */
    public function getNavigationMode() {
        return $this->getTestPart()->getNavigationMode();
    }
    
    /**
     * Get the submission mode in force for the Step.
     * 
     * @return integer A value from the SubmissionMode enumeration.
     */
    public function getSubmissionMode() {
        return $this->getTestPart()->getsubmissionMode();
    }
    
    /**
     * Get the ItemSessionControl in force for the Step, if any.
     * 
     * @return StepItemSessionControl The ItemSessionControl in force or the null value if there is not control in force.
     */
    public function getItemSessionControl() {
        
        if (($owner = $this->getAssessmentItemOccurence()) && ($isc = $owner->getAssessmentItemRef()->getItemSessionControl()) !== null) {
            return new StepItemSessionControl($owner, $isc);
        }
        else {
            // Look in assessmentSections.
            foreach ($this->getAssessmentSections() as $section) {
                if (($isc = $section->getItemSessionControl()) !== null) {
                    return new StepItemSessionControl($section, $isc);
                }
            }
        
            // Nothing found in assessmentSections, look in testPart.
            if (($owner = $this->getTestPart()) && ($isc = $owner->getItemSessionControl()) !== null) {
                return new StepItemSessionControl($owner, $isc);
            }
            
            // Nothing found in test part, look in assessmentTest.
            if (($owner = $this->getAssessmentTest()) && ($isc = $owner->getItemSessionControl()) !== null) {
                return new StepItemSessionControl($owner, $isc);
            }
        
            return null;
        }
    }
    
    /**
     * Get the TimeLimits in force 
     * 
     * @param boolean $excludeItem Whether or not to exclude the time limits applied on items.
     * @return StepTimeLimitsCollection The collection of TimeLimits in force.
     */
    public function getTimeLimits($excludeItem = false) {
        $timeLimits = new StepTimeLimitsCollection();
        
        if (($owner = $this->getAssessmentTest()) && ($tl = $owner->getTimeLimits()) !== null) {
            $timeLimits[] = new StepTimeLimits($owner, $tl);
        }
        
        if (($owner = $this->getTestPart()) && ($tl = $owner->getTimeLimits()) !== null) {
            $timeLimits[] = new StepTimeLimits($owner, $tl);
        }
        
        foreach ($this->getAssessmentSections() as $section) {
            if (($tl = $section->getTimeLimits()) !== null) {
                $timeLimits[] = new StepTimeLimits($section, $tl);
            }
        }
        
        if ($excludeItem === false && ($owner = $this->getAssessmentItemOccurence()) && ($tl = $owner->getAssessmentItemRef()->getTimeLimits()) !== null) {
            $timeLimits[] = new StepTimeLimits($owner, $tl);
        }
        
        return $timeLimits;
    }
    
    /**
     * Get the collection of RubricBlockRef objects in the order they appear
     * in the test hierarchy.
     * 
     * @return RubricBlockRefCollection A collection of RubricBlockRef objects.
     */
    public function getRubricBlockRefs() {
         $rubrics = new RubricBlockRefCollection();
        
        foreach ($this->getAssessmentSections() as $section) {
            $rubrics->merge($section->getRubricBlockRefs());
        }
        
        return $rubrics;
    }
    
    public function __clone() {
        $newItemOcc = clone $this->getAssessmentItemOccurence();
        $this->setAssessmentItemOccurence($newItemOcc);
    }
}