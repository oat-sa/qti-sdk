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

namespace qtism\data\storage\xml\marshalling;

use DOMElement;
use qtism\data\ExtendedAssessmentItemRef;
use qtism\data\QtiComponent;
use qtism\data\state\OutcomeDeclarationCollection;
use qtism\data\state\ResponseDeclarationCollection;

/**
 * A Marshaller aiming at marshalling/unmarshalling ExtendedAssessmentItemRefs.
 */
class ExtendedAssessmentItemRefMarshaller extends AssessmentItemRefMarshaller
{
    /**
     * Marshall a ExtendedAssessmentItemRef object into its DOMElement representation.
     *
     * @param QtiComponent $component
     * @return DOMElement The according DOMElement object.
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
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

        if ($component->hasResponseProcessing() === true) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($component->getResponseProcessing());
            $respProcElt = $marshaller->marshall($component->getResponseProcessing());
            $element->appendChild($respProcElt);
        }

        $this->setDOMElementAttribute($element, 'adaptive', $component->isAdaptive());
        $this->setDOMElementAttribute($element, 'timeDependent', $component->isTimeDependent());

        return $element;
    }

    /**
     * Unmarshall an extended version of an assessmentItemRef DOMElement into
     * a ExtendedAssessmentItemRef object.
     *
     * @param DOMElement $element
     * @return ExtendedAssessmentItemRef A ExtendedAssessmentItemRef object.
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
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
        $responseDeclarationElts = $this->getChildElementsByTagName($element, 'responseDeclaration');
        $responseDeclarations = new ResponseDeclarationCollection();
        foreach ($responseDeclarationElts as $responseDeclarationElt) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($responseDeclarationElt);
            $responseDeclarations[] = $marshaller->unmarshall($responseDeclarationElt);
        }
        $compactAssessmentItemRef->setResponseDeclarations($responseDeclarations);

        // OutcomeDeclarations.
        $outcomeDeclarationElts = $this->getChildElementsByTagName($element, 'outcomeDeclaration');
        $outcomeDeclarations = new OutcomeDeclarationCollection();
        foreach ($outcomeDeclarationElts as $outcomeDeclarationElt) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($outcomeDeclarationElt);
            $outcomeDeclarations[] = $marshaller->unmarshall($outcomeDeclarationElt);
        }
        $compactAssessmentItemRef->setOutcomeDeclarations($outcomeDeclarations);

        // ResponseProcessing.
        $responseProcessingElts = $this->getChildElementsByTagName($element, 'responseProcessing');
        if (count($responseProcessingElts) === 1) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($responseProcessingElts[0]);
            $compactAssessmentItemRef->setResponseProcessing($marshaller->unmarshall($responseProcessingElts[0]));
        }

        if (($adaptive = $this->getDOMElementAttributeAs($element, 'adaptive', 'boolean')) !== null) {
            $compactAssessmentItemRef->setAdaptive($adaptive);
        }

        if (($timeDependent = $this->getDOMElementAttributeAs($element, 'timeDependent', 'boolean')) !== null) {
            $compactAssessmentItemRef->setTimeDependent($timeDependent);
        } else {
            $msg = "The mandatory attribute 'timeDependent' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }

        return $compactAssessmentItemRef;
    }
}
