<?php

namespace qtism\data;

use qtism\data\state\ResponseDeclarationCollection;
use qtism\data\state\OutcomeDeclarationCollection;
use qtism\data\processing\ResponseProcessing;
use \InvalidArgumentException;

/**
 * Any clas that claims to represent An AssessmentItem must implement
 * this interface.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
interface IAssessmentItem extends QtiIdentifiable {
    
    /**
     * Set whether the item is time dependent or not.
     *
     * @param boolean $timeDependent Time dependent or not.
     * @throws InvalidArgumentException If $timeDependent is not a boolean value.
     */
    public function setTimeDependent($timeDependent);
    
    /**
     * Wether the item is time dependent.
     *
     * @return boolean
     */
    public function isTimeDependent();
    
    /**
     * Set whether the item is adaptive.
     *
     * @param boolean $adaptive Adaptive or not.
     * @throws InvalidArgumentException If $adaptive is not a boolean value.
     */
    public function setAdaptive($adaptive);
    
    /**
     * Whether the item is adaptive.
     *
     * @return boolean
     */
    public function isAdaptive();
    
    /**
     * Get the response declarations.
     *
     * @return ResponseDeclarationCollection A collection of ResponseDeclaration objects.
     */
    public function getResponseDeclarations();
    
    /**
     * Set the response declarations.
     *
     * @param ResponseDeclarationCollection $responseDeclarations A collection of ResponseDeclaration objects
     */
    public function setResponseDeclarations(ResponseDeclarationCollection $responseDeclarations);
    
    /**
     * Get the outcome declarations.
     *
     * @return OutcomeDeclarationCollection A collection of OutcomeDeclaration objects.
     */
    public function getOutcomeDeclarations();
    
    /**
     * Set the outcome declarations.
     *
     * @param OutcomeDeclarationCollection $outcomeDeclarations A collection of OutcomeDeclaration objects.
     */
    public function setOutcomeDeclarations(OutcomeDeclarationCollection $outcomeDeclarations);
    
    /**
     * Get the associated ResponseProcessing object.
     *
     * @return ResponseProcessing A ResponseProcessing object or null if no associated response processing.
     */
    public function getResponseProcessing();
    
    /**
     * Set the associated ResponseProcessing object.
     *
     * @param ResponseProcessing $responseProcessing A ResponseProcessing object or null if no associated response processing.
     */
    public function setResponseProcessing(ResponseProcessing $responseProcessing = null);
}