<?php

namespace qtism\runtime\tests;

use qtism\data\rules\Selection;
use qtism\data\AssessmentSection;

/**
 * The AbstractSelector aims at implementing the behaviour described
 * by the QTI selection class.
 * 
 * From IMS QTI:
 * 
 * The selection class specifies the rules used to select the child elements of a 
 * section for each test session. If no selection rules are given we assume that 
 * all elements are to be selected.
 * 
 * @author Jérôme Bogaerts
 * @link http://www.imsglobal.org/question/qtiv2p1/imsqti_infov2p1.html#section10093 QTI Test Structure
 *
 */
abstract class AbstractSelection {
    
    /**
     * The AssessmentSection object on which the selection
     * must occur.
     * 
     * @var AssessmentSection
     */
    private $assessmentSection;
    
    /**
     * Create a new AbstractSelector object.
     * 
     * @param AssessmentSection $assessmentSection An AssessmentSection object.
     */
    public function __construct(AssessmentSection $assessmentSection) {
        $this->setAssessmentSection($assessmentSection);
    }
    
    /**
     * Get the AssessmentSection object on which the selection
     * will occur.
     * 
     * @return AssessmentSection An AssessmentSection object.
     */
    public function getAssessmentSection() {
        return $this->assessmentSection;
    }
    
    /**
     * Set the AssessmentSection object on which the selection will occur.
     * 
     * @param AssessmentSection $assessmentSection An AssessmentSection object.
     */
    public function setAssessmentSection(AssessmentSection $assessmentSection) {
        $this->assessmentSection = $assessmentSection;
    }
    
    /**
     * Select the direct children components of the AssessmentSection on which the selection must be applied. If the withReplacement attribute
     * of the Selection is set to (boolean) true, multiple occurences of children elements might occur. In this case, the identifiers of the
     * selected elements must be suffixed with a QTI sequence number. For instance, if assessmentItemRef 'Q01' is selected 3 times from 
     * assessmentSection 'S01', assessmentItemRef objects ['Q01.1','Q01.2','Q01.3', 'Q02', ...] will compose the 'S01' assessmentSection. The same rule
     * applies on children AssessmentSection objects.
     * 
     * @return AssessmentSection An AssessmentSection object on which the selection is applied.
     * @throws SelectionException
     */
    abstract public function select();
}