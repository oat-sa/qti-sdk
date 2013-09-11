<?php

namespace qtism\runtime\tests;

use qtism\runtime\tests\AbstractAssessmentTestSessionFactory;

/**
 * An AssessmentTestSessionFactory implementation that creates basic
 * AssessmentTestSession objects from a given AssessmentTest definition
 * and a Route to be taken.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class AssessmentTestSessionFactory extends AbstractAssessmentTestSessionFactory {
    
    /**
     * Create a new AssessmentTestSession object that will be filled with the content of
     * the factory, in other words, the Route to be taken and the AssessmentTest definition.
     * 
     * @throws RuntimeException If no Route has been given to the factory yet.
     */
    public function createAssessmentTestSesion() {
        parent::createAssessmentTestSesion();
        
        return new AssessmentTestSession($this->getAssessmentTest(), $this->getRoute());
    }
    
}