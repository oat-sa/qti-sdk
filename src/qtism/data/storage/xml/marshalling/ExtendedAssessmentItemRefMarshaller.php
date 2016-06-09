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

namespace qtism\data\storage\xml\marshalling;

use qtism\common\collections\IdentifierCollection;
use qtism\data\content\ModalFeedbackRuleCollection;
use qtism\data\state\OutcomeDeclarationCollection;
use qtism\data\state\ResponseDeclarationCollection;
use qtism\data\state\TemplateDeclarationCollection;
use qtism\data\state\ShufflingCollection;
use qtism\data\state\ResponseValidityConstraintCollection;
use qtism\data\ExtendedAssessmentItemRef;
use qtism\data\QtiComponent;
use \DOMElement;

/**
 * A Marshaller aiming at marshalling/unmarshalling ExtendedAssessmentItemRefs.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ExtendedAssessmentItemRefMarshaller extends AssessmentItemRefMarshaller
{
    /**
     * Marshall a ExtendedAssessmentItemRef object into its DOMElement representation.
     *
     * @param \qtism\data\QtiComponent
     * @return \DOMElement The according DOMElement object.
     */
    protected function marshall(QtiComponent $component)
    {
        $element = parent::marshall($component);

        foreach ($component->getResponseDeclarations() as $responseDeclaration) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($responseDeclaration);
            $element->appendChild($marshaller->marshall($responseDeclaration));
        }

        foreach ($component->getOutcomeDeclarations() as $outcomeDeclaration) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($outcomeDeclaration);
            $element->appendChild($marshaller->marshall($outcomeDeclaration));
        }
        
        foreach ($component->getTemplateDeclarations() as $templateDeclaration) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($templateDeclaration);
            $element->appendChild($marshaller->marshall($templateDeclaration));
        }
        
        if ($component->hasTemplateProcessing() === true) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($component->getTemplateProcessing());
            $templProcElt = $marshaller->marshall($component->getTemplateProcessing());
            $element->appendChild($templProcElt);
        }
        
        if ($component->hasResponseProcessing() === true) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($component->getResponseProcessing());
            $respProcElt = $marshaller->marshall($component->getResponseProcessing());
            $element->appendChild($respProcElt);
        }
        
        foreach ($component->getModalFeedbackRules() as $modalFeedbackRule) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($modalFeedbackRule);
            $element->appendChild($marshaller->marshall($modalFeedbackRule));
        }
        
        foreach ($component->getShufflings() as $shuffling) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($shuffling);
            $element->appendChild($marshaller->marshall($shuffling));
        }
        
        foreach ($component->getResponseValidityConstraints() as $responseValidityConstraint) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($responseValidityConstraint);
            $element->appendChild($marshaller->marshall($responseValidityConstraint));
        }
        
        self::setDOMElementAttribute($element, 'adaptive', $component->isAdaptive());
        self::setDOMElementAttribute($element, 'timeDependent', $component->isTimeDependent());
        
        $endAttemptIdentifiers = $component->getEndAttemptIdentifiers();
        if (count($endAttemptIdentifiers) > 0) {
            self::setDOMElementAttribute($element, 'endAttemptIdentifiers', implode("\x20", $endAttemptIdentifiers->getArrayCopy()));
        }

        return $element;
    }

    /**
     * Unmarshall an extended version of an assessmentItemRef DOMElement into
     * a ExtendedAssessmentItemRef object.
     *
     * @param \DOMElement $element
     * @return  \qtism\data\ExtendedAssessmentItemRef A ExtendedAssessmentItemRef object.
     */
    protected function unmarshall(DOMElement $element)
    {
        $baseComponent = parent::unmarshall($element);
        $identifier = $baseComponent->getIdentifier();
        $href = $baseComponent->getHref();

        $compactAssessmentItemRef = new ExtendedAssessmentItemRef($identifier, $href);
        $compactAssessmentItemRef->setRequired($baseComponent->isRequired());
        $compactAssessmentItemRef->setFixed($baseComponent->isFixed());
        $compactAssessmentItemRef->setPreConditions($baseComponent->getPreConditions());
        $compactAssessmentItemRef->setBranchRules($baseComponent->getBranchRules());
        $compactAssessmentItemRef->setItemSessionControl($baseComponent->getItemSessionControl());
        $compactAssessmentItemRef->setTimeLimits($baseComponent->getTimeLimits());
        $compactAssessmentItemRef->setTemplateDefaults($baseComponent->getTemplateDefaults());
        $compactAssessmentItemRef->setWeights($baseComponent->getWeights());
        $compactAssessmentItemRef->setVariableMappings($baseComponent->getVariableMappings());
        $compactAssessmentItemRef->setCategories($baseComponent->getCategories());

        // ResponseDeclarations.
        $responseDeclarationElts = self::getChildElementsByTagName($element, 'responseDeclaration');
        $responseDeclarations = new ResponseDeclarationCollection();
        foreach ($responseDeclarationElts as $responseDeclarationElt) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($responseDeclarationElt);
            $responseDeclarations[] = $marshaller->unmarshall($responseDeclarationElt);
        }
        $compactAssessmentItemRef->setResponseDeclarations($responseDeclarations);

        // OutcomeDeclarations.
        $outcomeDeclarationElts = self::getChildElementsByTagName($element, 'outcomeDeclaration');
        $outcomeDeclarations = new OutcomeDeclarationCollection();
        foreach ($outcomeDeclarationElts as $outcomeDeclarationElt) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($outcomeDeclarationElt);
            $outcomeDeclarations[] = $marshaller->unmarshall($outcomeDeclarationElt);
        }
        $compactAssessmentItemRef->setOutcomeDeclarations($outcomeDeclarations);
        
        // TemplateDeclarations.
        $templateDeclarationElts = self::getChildElementsByTagName($element, 'templateDeclaration');
        $templateDeclarations = new TemplateDeclarationCollection();
        foreach ($templateDeclarationElts as $templateDeclarationElt) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($templateDeclarationElt);
            $templateDeclarations[] = $marshaller->unmarshall($templateDeclarationElt);
        }
        $compactAssessmentItemRef->setTemplateDeclarations($templateDeclarations);
        
        // TemplateProcessing.
        $templateProcessingElts = self::getChildElementsByTagName($element, 'templateProcessing');
        if (count($templateProcessingElts) === 1) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($templateProcessingElts[0]);
            $compactAssessmentItemRef->setTemplateProcessing($marshaller->unmarshall($templateProcessingElts[0]));
        }
        
        // ResponseProcessing.
        $responseProcessingElts = self::getChildElementsByTagName($element, 'responseProcessing');
        if (count($responseProcessingElts) === 1) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($responseProcessingElts[0]);
            $compactAssessmentItemRef->setResponseProcessing($marshaller->unmarshall($responseProcessingElts[0]));
        }
        
        // ModalFeedbacks (transformed in ModalFeedbackRules).
        $modalFeedbackElts = self::getChildElementsByTagName($element, 'modalFeedbackRule');
        $modalFeedbackRules = new ModalFeedbackRuleCollection();
        foreach ($modalFeedbackElts as $modalFeedbackElt) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($modalFeedbackElt);
            $modalFeedbackRules[] = $marshaller->unmarshall($modalFeedbackElt);
        }
        $compactAssessmentItemRef->setModalFeedbackRules($modalFeedbackRules);
        
        // Shufflings.
        $shufflingElts = self::getChildElementsByTagName($element, 'shuffling');
        $shufflings = new ShufflingCollection();
        foreach ($shufflingElts as $shufflingElt) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($shufflingElt);
            $shufflings[] = $marshaller->unmarshall($shufflingElt);
        }
        $compactAssessmentItemRef->setShufflings($shufflings);
        
        // ResponseValidityConstraints.
        $responseValidityConstraintElts = self::getChildElementsByTagName($element, 'responseValidityConstraint');
        $responseValidityConstraints = new ResponseValidityConstraintCollection();
        foreach ($responseValidityConstraintElts as $responseValidityConstraintElt) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($responseValidityConstraintElt);
            $responseValidityConstraints[] = $marshaller->unmarshall($responseValidityConstraintElt);
        }
        $compactAssessmentItemRef->setResponseValidityConstraints($responseValidityConstraints);

        if (($adaptive = static::getDOMElementAttributeAs($element, 'adaptive', 'boolean')) !== null) {
            $compactAssessmentItemRef->setAdaptive($adaptive);
        }

        if (($timeDependent = static::getDOMElementAttributeAs($element, 'timeDependent', 'boolean')) !== null) {
            $compactAssessmentItemRef->setTimeDependent($timeDependent);
        } else {
            $msg = "The mandatory attribute 'timeDependent' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
        
        if (($endAttemptIdentifiers = self::getDOMElementAttributeAs($element, 'endAttemptIdentifiers')) !== null) {
            $identifiersArray = explode("\x20", $endAttemptIdentifiers);
            if (count($identifiersArray) > 0) {
                $compactAssessmentItemRef->setEndAttemptIdentifiers(new IdentifierCollection($identifiersArray));
            }
        }

        return $compactAssessmentItemRef;
    }
}
