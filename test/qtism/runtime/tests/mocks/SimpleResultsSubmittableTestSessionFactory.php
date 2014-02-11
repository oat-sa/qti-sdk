<?php

use qtism\runtime\tests\AbstractAssessmentTestSessionFactory;
use qtism\runtime\tests\TestResultsSubmission;

class SimpleResultsSubmittableTestSessionFactory extends AbstractAssessmentTestSessionFactory {
    
    public function createAssessmentTestSession() {
        return new SimpleResultsSubmittableTestSession($this->getAssessmentTest(), $this->getRoute());
    }
}