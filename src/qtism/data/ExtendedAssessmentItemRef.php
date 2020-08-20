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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data;

use InvalidArgumentException;
use qtism\common\collections\IdentifierCollection;
use qtism\common\utils\Format;
use qtism\data\content\ModalFeedbackRule;
use qtism\data\content\ModalFeedbackRuleCollection;
use qtism\data\processing\ResponseProcessing;
use qtism\data\processing\TemplateProcessing;
use qtism\data\state\OutcomeDeclaration;
use qtism\data\state\OutcomeDeclarationCollection;
use qtism\data\state\ResponseDeclaration;
use qtism\data\state\ResponseDeclarationCollection;
use qtism\data\state\ResponseValidityConstraint;
use qtism\data\state\ResponseValidityConstraintCollection;
use qtism\data\state\Shuffling;
use qtism\data\state\ShufflingCollection;
use qtism\data\state\TemplateDeclaration;
use qtism\data\state\TemplateDeclarationCollection;

/**
 * The ExtendedAssessmentItemRef class is an extended representation of the QTI assessmentItemRef class.
 *
 * It gathers together the assessmentItemRef + the outcome/responseDeclarations of the referenced item
 * in a single component.
 */
class ExtendedAssessmentItemRef extends AssessmentItemRef implements IAssessmentItem
{
    /**
     * The outcomeDeclarations found in the referenced assessmentItem.
     *
     * @var OutcomeDeclarationCollection
     * @qtism-bean-property
     */
    private $outcomeDeclarations;

    /**
     * The responseDeclarations found in the referenced assessmentItem.
     *
     * @var ResponseDeclarationCollection
     * @qtism-bean-property
     */
    private $responseDeclarations;

    /**
     * The templateDeclarations found in the referenced assessmentItem.
     *
     * @var TemplateDeclarationCollection
     * @qtism-bean-property
     */
    private $templateDeclarations;

    /**
     * The responseProcessing found in the referenced assessmentItem.
     *
     * @var ResponseProcessing
     * @qtism-bean-property
     */
    private $responseProcessing = null;

    /**
     * The adaptive attribute found in the referenced assessmentItem.
     *
     * @var bool
     * @qtism-bean-property
     */
    private $adaptive = false;

    /**
     * The timeDependent attribute found in the referenced assessmentItem.
     *
     * @var bool
     * @qtism-bean-property
     */
    private $timeDependent = false;

    /**
     * A collection of QTI identifiers identifying response variables bound to endAttemptInteractions contained in the item content.
     *
     * @var IdentifierCollection
     * @qtism-bean-property
     */
    private $endAttemptIdentifiers = null;

    /**
     * The modalFeedbackRules found in the referenced assessmentItem.
     *
     * @var ModalFeedbackRuleCollection
     * @qtism-bean-property
     */
    private $modalFeedbackRules;

    /**
     * The template processing found in the referenced assessmentItem.
     *
     * @var TemplateProcessing
     * @qtism-bean-property
     */
    private $templateProcessing = null;

    /**
     * The Shuffling components.
     *
     * The Shuffling components indicate what are the identifiers involved in interaction's choice shuffling.
     *
     * @var ShufflingCollection
     * @qtism-bean-property
     */
    private $shufflings;

    /**
     * The response validity constraints related to the item content.
     *
     * @var ResponseValidityConstraintCollection
     * @qtism-bean-property
     */
    private $responseValidityConstraints;

    /**
     * The title found in the referenced assessmentItem.
     *
     * @var string
     * @qtism-bean-property
     */
    private $title = '';

    /**
     * The label found (if any) in the referenced assessmentItem.
     *
     * @var string
     * @qtism-bean-property
     */
    private $label = '';

    /**
     * Create a new instance of CompactAssessmentItem
     *
     * @param string $identifier A QTI Identifier.
     * @param string $href The URL to locate the referenced assessmentItem.
     * @param IdentifierCollection $categories Optional categories.
     * @throws InvalidArgumentException if $identifier is not a valid QTI Identifier or $href is not a valid URI.
     */
    public function __construct($identifier, $href, IdentifierCollection $categories = null)
    {
        parent::__construct($identifier, $href, $categories);

        $this->setOutcomeDeclarations(new OutcomeDeclarationCollection());
        $this->setResponseDeclarations(new ResponseDeclarationCollection());
        $this->setTemplateDeclarations(new TemplateDeclarationCollection());
        $this->setModalFeedbackRules(new ModalFeedbackRuleCollection());
        $this->setEndAttemptIdentifiers(new IdentifierCollection());
        $this->setShufflings(new ShufflingCollection());
        $this->setResponseValidityConstraints(new ResponseValidityConstraintCollection());
    }

    /**
     * Set the outcomeDeclarations found in the referenced assessmentItem.
     *
     * @param OutcomeDeclarationCollection $outcomeDeclarations A collection of OutcomeDeclaration objects.
     */
    public function setOutcomeDeclarations(OutcomeDeclarationCollection $outcomeDeclarations)
    {
        $this->outcomeDeclarations = $outcomeDeclarations;
    }

    /**
     * Get the outcomeDeclarations found in the referenced assessmentItem.
     *
     * @return OutcomeDeclarationCollection A collection of OutcomeDeclaration objects.
     */
    public function getOutcomeDeclarations()
    {
        return $this->outcomeDeclarations;
    }

    /**
     * Set the responseProcessing found in the referenced assessmentItem.
     *
     * @param ResponseProcessing $responseProcessing A ResponseProcessing object or null if no response processing described.
     */
    public function setResponseProcessing(ResponseProcessing $responseProcessing = null)
    {
        $this->responseProcessing = $responseProcessing;
    }

    /**
     * Get the responseProcessing found in the referenced assessmentItem.
     *
     * @return ResponseProcessing A ResponseProcessing object or null if no response processing described.
     */
    public function getResponseProcessing()
    {
        return $this->responseProcessing;
    }

    /**
     * Whether the referenced assessmentItem has a responseProcessing entry.
     *
     * @return bool
     */
    public function hasResponseProcessing()
    {
        return $this->getResponseProcessing() !== null;
    }

    /**
     * Set the templateProcessing found in the referenced assessmentItem.
     *
     * @param TemplateProcessing $templateProcessing
     */
    public function setTemplateProcessing(TemplateProcessing $templateProcessing = null)
    {
        $this->templateProcessing = $templateProcessing;
    }

    /**
     * Get the templateProcessing found in the referenced assessmentItem.
     *
     * @return TemplateProcessing
     */
    public function getTemplateProcessing()
    {
        return $this->templateProcessing;
    }

    /**
     * Whether the referenced assessmentItem has a templateProcessing entry.
     *
     * @return bool
     */
    public function hasTemplateProcessing()
    {
        return $this->getTemplateProcessing() !== null;
    }

    /**
     * Add an OutcomeDeclaration object.
     *
     * @param OutcomeDeclaration $outcomeDeclaration An OutcomeDeclaration object.
     */
    public function addOutcomeDeclaration(OutcomeDeclaration $outcomeDeclaration)
    {
        $this->getOutcomeDeclarations()->attach($outcomeDeclaration);
    }

    /**
     * Remove an OutcomeDeclaration object from the current one.
     *
     * @param OutcomeDeclaration $outcomeDeclaration An OutcomeDeclaration object.
     */
    public function removeOutcomeDeclaration(OutcomeDeclaration $outcomeDeclaration)
    {
        $this->getOutcomeDeclarations()->detach($outcomeDeclaration);
    }

    /**
     * Set the responseDeclarations found in the referenced assessmentItem.
     *
     * @param ResponseDeclarationCollection $responseDeclarations A collection of ResponseDeclaration objects.
     */
    public function setResponseDeclarations(ResponseDeclarationCollection $responseDeclarations)
    {
        $this->responseDeclarations = $responseDeclarations;
    }

    /**
     * Get the responseDeclarations found in the referenced assessmentItem.
     *
     * @return ResponseDeclarationCollection A collection of ResponseDeclaration objects.
     */
    public function getResponseDeclarations()
    {
        return $this->responseDeclarations;
    }

    /**
     * Add a ResponseDeclaration object.
     *
     * @param ResponseDeclaration $responseDeclaration A ResponseDeclaration object.
     */
    public function addResponseDeclaration(ResponseDeclaration $responseDeclaration)
    {
        $this->getResponseDeclarations()->attach($responseDeclaration);
    }

    /**
     * Remove a ResponseDeclaration object.
     *
     * @param ResponseDeclaration $responseDeclaration A ResponseDeclaration object.
     */
    public function removeResponseDeclaration(ResponseDeclaration $responseDeclaration)
    {
        $this->getResponseDeclarations()->detach($responseDeclaration);
    }

    /**
     * Set the templateDeclarations found in the referenced item.
     *
     * @param TemplateDeclarationCollection $templateDeclarations A collection of TemplateDeclaration objects.
     */
    public function setTemplateDeclarations(TemplateDeclarationCollection $templateDeclarations)
    {
        $this->templateDeclarations = $templateDeclarations;
    }

    /**
     * Get the templateDeclarations found in the referenced item.
     *
     * @return TemplateDeclarationCollection
     */
    public function getTemplateDeclarations()
    {
        return $this->templateDeclarations;
    }

    /**
     * Add a TemplateDeclaration object.
     *
     * @param TemplateDeclaration $templateDeclaration
     */
    public function addTemplateDeclaration(TemplateDeclaration $templateDeclaration)
    {
        $this->templateDeclarations->attach($templateDeclaration);
    }

    /**
     * Remove a TemplateDeclaration object.
     *
     * @param TemplateDeclaration $templateDeclaration
     */
    public function removeTemplateDeclaration(TemplateDeclaration $templateDeclaration)
    {
        $this->templateDeclarations->detach($templateDeclaration);
    }

    /**
     * Set the collection of ModalFeedbackRule objects.
     *
     * @param ModalFeedbackRuleCollection $modalFeedbackRules
     */
    public function setModalFeedbackRules(ModalFeedbackRuleCollection $modalFeedbackRules)
    {
        $this->modalFeedbackRules = $modalFeedbackRules;
    }

    /**
     * Get the collection of ModalFeedbackRule objects.
     *
     * @return ModalFeedbackRuleCollection
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
     * Set the Shuffling components.
     *
     * @param ShufflingCollection $shufflings
     */
    public function setShufflings(ShufflingCollection $shufflings)
    {
        $this->shufflings = $shufflings;
    }

    /**
     * Get the Shuffling components.
     *
     * @return ShufflingCollection
     */
    public function getShufflings()
    {
        return $this->shufflings;
    }

    /**
     * Add a Shuffling component.
     *
     * @param Shuffling $shuffling
     */
    public function addShuffling(Shuffling $shuffling)
    {
        $this->getShufflings()->attach($shuffling);
    }

    /*
     * Remove a Shuffling component.
     *
     * @param \qtism\data\state\Shuffling $shuffling
     */
    public function removeShuffling(Shuffling $shuffling)
    {
        $this->getShufflings()->detach($shuffling);
    }

    /**
     * Whether the referenced Item is adaptive.
     *
     * @return bool
     */
    public function isAdaptive()
    {
        return $this->adaptive;
    }

    /**
     * Set if the referenced Item is considered to be adaptive or not.
     *
     * @param bool $adaptive Whether the referenced Item is adaptive.
     * @throws InvalidArgumentException If $adaptive is not a boolean value.
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
     * @param bool $timeDependent Whether the referenced item is time dependent.
     * @throws InvalidArgumentException If $timeDependent is not a boolean value.
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
     * @return bool
     */
    public function isTimeDependent()
    {
        return $this->timeDependent;
    }

    /**
     * Set the response identifiers related to endAttemptInteractions in the item content.
     *
     * @param IdentifierCollection $endAttemptIdentifiers
     */
    public function setEndAttemptIdentifiers(IdentifierCollection $endAttemptIdentifiers)
    {
        $this->endAttemptIdentifiers = $endAttemptIdentifiers;
    }

    /**
     * Get the response identifiers related to endAttemptInteractions in the item content.
     *
     * @return IdentifierCollection
     */
    public function getEndAttemptIdentifiers()
    {
        return $this->endAttemptIdentifiers;
    }

    /**
     * Set the response validity constraints related to the item content.
     *
     * @param ResponseValidityConstraintCollection $responseValidityConstraints
     */
    public function setResponseValidityConstraints(ResponseValidityConstraintCollection $responseValidityConstraints)
    {
        $this->responseValidityConstraints = $responseValidityConstraints;
    }

    /**
     * Get the response validity constraints related to the item content.
     *
     * @return ResponseValidityConstraintCollection
     */
    public function getResponseValidityConstraints()
    {
        return $this->responseValidityConstraints;
    }

    /**
     * Add a response validity constraint related to item content.
     *
     * @param ResponseValidityConstraint $responseValidityConstraint
     */
    public function addResponseValidityConstraint(ResponseValidityConstraint $responseValidityConstraint)
    {
        $this->getResponseValidityConstraints()->attach($responseValidityConstraint);
    }

    /**
     * Remove a response validity constraint related to item content.
     *
     * @param ResponseValidityConstraint $responseValidityConstraint
     */
    public function removeResponseValidityConstraint(ResponseValidityConstraint $responseValidityConstraint)
    {
        $this->getResponseValidityConstraints()->detach($responseValidityConstraint);
    }

    /**
     * Create a new ExtendedAssessmentItemRef object from an AssessmentItemRef object.
     *
     * @param AssessmentItemRef $assessmentItemRef An AssessmentItemRef object.
     * @return ExtendedAssessmentItemRef An ExtendedAssessmentItemRef object.
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
        $compactRef->setWeights($assessmentItemRef->getWeights());

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
            $this->getTemplateDeclarations()->getArrayCopy()
        );

        if ($this->hasTemplateProcessing() === true) {
            $components[] = $this->getTemplateProcessing();
        }

        if ($this->hasResponseProcessing() === true) {
            $components[] = $this->getResponseProcessing();
        }

        $components = array_merge(
            $components,
            $this->getModalFeedbackRules()->getArrayCopy(),
            $this->getShufflings()->getArrayCopy(),
            $this->getResponseValidityConstraints()->getArrayCopy()
        );

        return new QtiComponentCollection($components);
    }

    /**
     * Set the title.
     *
     * Set the title found in the referenced assessmentItem.
     *
     * @param $title
     * @throws InvalidArgumentException
     */
    public function setTitle($title)
    {
        if (gettype($title) === 'string') {
            $this->title = $title;
        } else {
            throw new InvalidArgumentException("The title argument must be a string, '" . gettype($title) . "' given.");
        }
    }

    /**
     * Get the title.
     *
     * Get the title found in the referenced assessmentItem.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Has a title.
     *
     * Whether or not a title was found in the referenced assessmentItem.
     *
     * @return bool
     */
    public function hasTitle()
    {
        return !empty($this->getTitle());
    }

    /**
     * Set the label.
     *
     * Set the label found in the referenced assessmentItem.
     *
     * @param $label
     * @throws InvalidArgumentException
     */
    public function setLabel($label)
    {
        if (Format::isString256($label)) {
            $this->label = $label;
        } else {
            throw new InvalidArgumentException("The label argument must be a string with at most 256 characters.'");
        }
    }

    /**
     * Get the label.
     *
     * Get the label (if any) found in the referenced assessmentItem.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Has a label.
     *
     * Whether or not a label was found in the referenced assessmentItem.
     *
     * @return bool
     */
    public function hasLabel()
    {
        return !empty($this->getLabel());
    }
}
