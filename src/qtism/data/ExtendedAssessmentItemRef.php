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

use qtism\data\content\ModalFeedbackRuleCollection;
use qtism\data\content\ModalFeedbackRule;
use qtism\data\state\ResponseDeclaration;
use qtism\data\state\OutcomeDeclaration;
use qtism\data\state\ResponseDeclarationCollection;
use qtism\data\state\OutcomeDeclarationCollection;
use qtism\data\state\TemplateDeclaration;
use qtism\data\state\TemplateDeclarationCollection;
use qtism\data\processing\ResponseProcessing;
use qtism\common\collections\IdentifierCollection;
use \InvalidArgumentException;

/**
 * The ExtendedAssessmentItemRef class is an extended representation of the QTI
 * assessmentItemRef class. It gathers together the assessmentItemRef + the
 * outcome/responseDeclarations of the referenced item in a single component.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ExtendedAssessmentItemRef extends AssessmentItemRef implements IAssessmentItem
{
    /**
     * The outcomeDeclarations found in the referenced assessmentItem.
     *
     * @var \qtism\data\state\OutcomeDeclarationCollection
     * @qtism-bean-property
     */
    private $outcomeDeclarations;

    /**
     * The responseDeclarations found in the referenced assessmentItem.
     *
     * @var \qtism\data\state\ResponseDeclarationCollection
     * @qtism-bean-property
     */
    private $responseDeclarations;
    
    /**
     * The templateDeclarations found in the referenced assessmentItem.
     * 
     * @var \qtism\data\state\TemplateDeclarationCollection
     * @qtism-bean-property
     */
    private $templateDeclarations;

    /**
     * The responseProcessing found in the referenced assessmentItem
     *
     * @var \qtism\data\processing\ResponseProcessing
     * @qtism-bean-property
     */
    private $responseProcessing = null;

    /**
     * The adaptive attribute found in the referenced assessmentItem.
     *
     * @var boolean
     * @qtism-bean-property
     */
    private $adaptive = false;

    /**
     * The timeDependent attribute found in the referenced assessmentItem.
     *
     * @var boolean
     * @qtism-bean-property
     */
    private $timeDependent = false;
    
    /**
     * The modalFeedbackRules found in the referenced assessmentItem.
     * 
     * @var \qtism\data\content\ModalFeedbackRuleCollection
     * @qtism-bean-property
     */
    private $modalFeedbackRules;

    /**
     * Create a new instance of CompactAssessmentItem
     *
     * @param string $identifier A QTI Identifier.
     * @param string $href The URL to locate the referenced assessmentItem.
     * @param \qtism\common\collections\IdentifierCollection $categories Optional categories.
     * @throws \InvalidArgumentException if $identifier is not a valid QTI Identifier or $href is not a valid URI.
     */
    public function __construct($identifier, $href, IdentifierCollection $categories = null)
    {
        parent::__construct($identifier, $href, $categories);

        $this->setOutcomeDeclarations(new OutcomeDeclarationCollection());
        $this->setResponseDeclarations(new ResponseDeclarationCollection());
        $this->setTemplateDeclarations(new TemplateDeclarationCollection());
        $this->setModalFeedbackRules(new ModalFeedbackRuleCollection());
    }

    /**
     * Set the outcomeDeclarations found in the referenced assessmentItem.
     *
     * @param \qtism\data\state\OutcomeDeclarationCollection $outcomeDeclarations A collection of OutcomeDeclaration objects.
     */
    public function setOutcomeDeclarations(OutcomeDeclarationCollection $outcomeDeclarations)
    {
        $this->outcomeDeclarations = $outcomeDeclarations;
    }

    /**
     * Get the outcomeDeclarations found in the referenced assessmentItem.
     *
     * @return \qtism\data\state\OutcomeDeclarationCollection A collection of OutcomeDeclaration objects.
     */
    public function getOutcomeDeclarations()
    {
        return $this->outcomeDeclarations;
    }

    /**
     * Set the responseProcessing found in the referenced assessmentItem.
     *
     * @param \qtism\data\processing\ResponseProcessing $responseProcessing A ResponseProcessing object or null if no response processing described.
     */
    public function setResponseProcessing(ResponseProcessing $responseProcessing = null)
    {
        $this->responseProcessing = $responseProcessing;
    }

    /**
     * Get the responseProcessing found in the referenced assessmentItem.
     *
     * @return \qtism\data\processing\ResponseProcessing A ResponseProcessing object or null if no response processing described.
     */
    public function getResponseProcessing()
    {
        return $this->responseProcessing;
    }

    /**
     * Whether the referenced assessmentITem has a responseProcessing entry.
     *
     * @return boolean
     */
    public function hasResponseProcessing()
    {
        return $this->getResponseProcessing() !== null;
    }

    /**
     * Add an OutcomeDeclaration object.
     *
     * @param \qtism\data\state\OutcomeDeclaration $outcomeDeclaration An OutcomeDeclaration object.
     */
    public function addOutcomeDeclaration(OutcomeDeclaration $outcomeDeclaration)
    {
        $this->getOutcomeDeclarations()->attach($outcomeDeclaration);
    }

    /**
     * Remove an OutcomeDeclaration object from the current one.
     *
     * @param \qtism\data\state\OutcomeDeclaration $outcomeDeclaration An OutcomeDeclaration object.
     */
    public function removeOutcomeDeclaration(OutcomeDeclaration $outcomeDeclaration)
    {
        $this->getOutcomeDeclarations()->detach($outcomeDeclaration);
    }

    /**
     * Set the responseDeclarations found in the referenced assessmentItem.
     *
     * @param \qtism\data\state\ResponseDeclarationCollection $responseDeclarations A collection of ResponseDeclaration objects.
     */
    public function setResponseDeclarations(ResponseDeclarationCollection $responseDeclarations)
    {
        $this->responseDeclarations = $responseDeclarations;
    }

    /**
     * Get the responseDeclarations found in the referenced assessmentItem.
     *
     * @return \qtism\data\state\ResponseDeclarationCollection A collection of ResponseDeclaration objects.
     */
    public function getResponseDeclarations()
    {
        return $this->responseDeclarations;
    }

    /**
     * Add a ResponseDeclaration object.
     *
     * @param \qtism\data\state\ResponseDeclaration $responseDeclaration A ResponseDeclaration object.
     */
    public function addResponseDeclaration(ResponseDeclaration $responseDeclaration)
    {
        $this->getResponseDeclarations()->attach($responseDeclaration);
    }

    /**
     * Remove a ResponseDeclaration object.
     *
     * @param \qtism\data\state\ResponseDeclaration $responseDeclaration A ResponseDeclaration object.
     */
    public function removeResponseDeclaration(ResponseDeclaration $responseDeclaration)
    {
        $this->getResponseDeclarations()->detach($responseDeclaration);
    }
    
    /**
     * Set the templateDeclarations found in the referenced item.
     * 
     * @param \qtism\data\state\TemplateDeclarationCollection $templateDeclarations A collection of TemplateDeclaration objects.
     */
    public function setTemplateDeclarations(TemplateDeclarationCollection $templateDeclarations)
    {
        $this->templateDeclarations = $templateDeclarations;
    }
    
    /**
     * Get the templateDeclarations found in the referenced item.
     * 
     * @return \qtism\data\state\TemplateDeclarationCollection
     */
    public function getTemplateDeclarations()
    {
        return $this->templateDeclarations;
    }
    
    /**
     * Add a TemplateDeclaration object.
     * 
     * @param \qtism\data\state\TemplateDeclaration $templateDeclaration
     */
    public function addTemplateDeclaration(TemplateDeclaration $templateDeclaration)
    {
        $this->templateDeclarations->attach($templateDeclaration);
    }
    
    /**
     * Remove a TemplateDeclaration object.
     * 
     * @param \qtism\data\state\TemplateDeclaration $templateDeclaration
     */
    public function removeTemplateDeclaration(TemplateDeclaration $templateDeclaration)
    {
        $this->templateDeclarations->detach($templateDeclaration);
    }
    
    /**
     * Set the collection of ModalFeedbackRule objects.
     * 
     * @param \qtism\data\content\ModalFeedbackRuleCollection $modalFeedbackRules
     */
    public function setModalFeedbackRules(ModalFeedbackRuleCollection $modalFeedbackRules)
    {
        $this->modalFeedbackRules = $modalFeedbackRules;
    }
    
    /**
     * Get the collection of ModalFeedbackRule objects.
     * 
     * @return \qtism\data\content\ModalFeedbackRuleCollection
     */
    public function getModalFeedbackRules()
    {
        return $this->modalFeedbackRules;
    }
    
    /**
     * Add a ModalFeedbackRule object to this AssessmentItemRef.
     * 
     * @param ModalFeedbackRule $modalFeedbackRule
     */
    public function addModalFeedbackRule(ModalFeedbackRule $modalFeedbackRule)
    {
        $this->getModalFeedbackRules()->attach($modalFeedbackRule);
    }
    
    /**
     * Remove a given $modalFeedbackRule from the AssessmentItemRef.
     * 
     * @param ModalFeedbackRule $modalFeedbackRule
     */
    public function removeModalFeedbackRule(ModalFeedbackRule $modalFeedbackRule)
    {
        $this->getModalFeedbackRules()->detach($modalFeedbackRule);
    }

    /**
     * Whether the referenced Item is adaptive.
     *
     * @return boolean
     */
    public function isAdaptive()
    {
        return $this->adaptive;
    }

    /**
     * Set if the referenced Item is considered to be adaptive or not.
     *
     * @param boolean $adaptive Whether the referenced Item is adaptive.
     * @throws \InvalidArgumentException If $adaptive is not a boolean value.
     */
    public function setAdaptive($adaptive)
    {
        if (gettype($adaptive) === 'boolean') {
            $this->adaptive = $adaptive;
        } else {
            $msg = "The adaptive argument must be a boolean value, '" . gettype($adaptive) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Set if the referenced Item is considered to be time dependent or not.
     *
     * @param boolean $timeDependent Whether the referenced item is time dependent.
     * @throws \InvalidArgumentException If $timeDependent is not a boolean value.
     */
    public function setTimeDependent($timeDependent)
    {
        if (gettype($timeDependent) === 'boolean') {
            $this->timeDependent = $timeDependent;
        } else {
            $msg = "The timeDependent argument must be a boolean value, '" . gettype($timeDependent) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Whether the referenced Item is considered to be time dependent.
     *
     * @return boolean
     */
    public function isTimeDependent()
    {
        return $this->timeDependent;
    }

    /**
     * Create a new ExtendedAssessmentItemRef object from an AssessmentItemRef object.
     *
     * @param \qtism\data\AssessmentItemRef $assessmentItemRef An AssessmentItemRef object.
     * @return \qtism\data\ExtendedAssessmentItemRef An ExtendedAssessmentItemRef object.
     */
    public static function createFromAssessmentItemRef(AssessmentItemRef $assessmentItemRef)
    {
        $identifier = $assessmentItemRef->getIdentifier();
        $href = $assessmentItemRef->getHref();
        $compactRef = new static($identifier, $href);
        $compactRef->setBranchRules($assessmentItemRef->getBranchRules());
        $compactRef->setCategories($assessmentItemRef->getCategories());
        $compactRef->setFixed($assessmentItemRef->isFixed());
        $compactRef->setItemSessionControl($assessmentItemRef->getItemSessionControl());
        $compactRef->setTimeLimits($assessmentItemRef->getTimeLimits());
        $compactRef->setPreConditions($assessmentItemRef->getPreConditions());
        $compactRef->setRequired($assessmentItemRef->isRequired());
        $compactRef->setTemplateDefaults($assessmentItemRef->getTemplateDefaults());

        return $compactRef;
    }

    /**
     * @see \qtism\data\AssessmentItemRef::getComponents()
     */
    public function getComponents()
    {
        $components = array_merge(
            parent::getComponents()->getArrayCopy(),
            $this->getResponseDeclarations()->getArrayCopy(),
            $this->getOutcomeDeclarations()->getArrayCopy(),
            $this->getModalFeedbackRules()->getArrayCopy()
        );

        if ($this->hasResponseProcessing() === true) {
            $components[] = $this->getResponseProcessing();
        }

        return new QtiComponentCollection($components);
    }
}
