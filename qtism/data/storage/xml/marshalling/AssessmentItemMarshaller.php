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
use qtism\data\AssessmentItem;
use qtism\data\content\ModalFeedbackCollection;
use qtism\data\content\StylesheetCollection;
use qtism\data\QtiComponent;
use qtism\data\state\OutcomeDeclarationCollection;
use qtism\data\state\ResponseDeclarationCollection;
use qtism\data\state\TemplateDeclarationCollection;

/**
 * Marshalling/Unmarshalling implementation for AssessmentItem.
 */
class AssessmentItemMarshaller extends Marshaller
{
    /**
     * Marshall an AssessmentItem object into a DOMElement object.
     *
     * @param QtiComponent $component An AssessmentItem object.
     * @return DOMElement The according DOMElement object.
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component)
    {
        $element = static::getDOMCradle()->createElement($component->getQtiClassName());

        $this->setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
        $this->setDOMElementAttribute($element, 'title', $component->getTitle());
        $this->setDOMElementAttribute($element, 'timeDependent', $component->isTimeDependent());
        $this->setDOMElementAttribute($element, 'adaptive', $component->isAdaptive());

        if ($component->hasLang() === true) {
            $this->setDOMElementAttribute($element, 'lang', $component->getLang());
        }

        if ($component->hasLabel() === true) {
            $this->setDOMElementAttribute($element, 'label', $component->getLabel());
        }

        if ($component->hasToolName() === true) {
            $this->setDOMElementAttribute($element, 'toolName', $component->getToolName());
        }

        if ($component->hasToolVersion() === true) {
            $this->setDOMElementAttribute($element, 'toolVersion', $component->getToolVersion());
        }

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
            $element->appendChild($marshaller->marshall($component->getTemplateProcessing()));
        }

        foreach ($component->getStylesheets() as $stylesheet) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($stylesheet);
            $element->appendChild($marshaller->marshall($stylesheet));
        }

        if ($component->hasItemBody() === true) {
            $itemBody = $component->getItemBody();
            $marshaller = $this->getMarshallerFactory()->createMarshaller($itemBody);
            $element->appendChild($marshaller->marshall($itemBody));
        }

        if ($component->hasResponseProcessing() === true) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($component->getResponseProcessing());
            $element->appendChild($marshaller->marshall($component->getResponseProcessing()));
        }

        foreach ($component->getModalFeedbacks() as $modalFeedback) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($modalFeedback);
            $element->appendChild($marshaller->marshall($modalFeedback));
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI assessmentItem element.
     *
     * If $assessmentItem is provided, it will be used as the unmarshalled component instead of creating
     * a new one.
     *
     * @param DOMElement $element A DOMElement object.
     * @param AssessmentItem $assessmentItem An optional AssessmentItem object to be decorated.
     * @return QtiComponent An AssessmentItem object.
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element, AssessmentItem $assessmentItem = null)
    {
        if (($identifier = $this->getDOMElementAttributeAs($element, 'identifier')) !== null) {
            if (($timeDependent = $this->getDOMElementAttributeAs($element, 'timeDependent', 'boolean')) !== null) {
                if (($title = $this->getDOMElementAttributeAs($element, 'title')) !== null) {
                    if (empty($assessmentItem)) {
                        $object = new AssessmentItem($identifier, $title, $timeDependent);
                    } else {
                        $object = $assessmentItem;
                        $object->setIdentifier($identifier);
                        $object->setTimeDependent($timeDependent);
                    }

                    if (($lang = $this->getDOMElementAttributeAs($element, 'lang')) !== null) {
                        $object->setLang($lang);
                    }

                    if (($label = $this->getDOMElementAttributeAs($element, 'label')) !== null) {
                        $object->setLabel($label);
                    }

                    if (($adaptive = $this->getDOMElementAttributeAs($element, 'adaptive', 'boolean')) !== null) {
                        $object->setAdaptive($adaptive);
                    }

                    if (($toolName = $this->getDOMElementAttributeAs($element, 'toolName')) !== null) {
                        $object->setToolName($toolName);
                    }

                    if (($toolVersion = $this->getDOMElementAttributeAs($element, 'toolVersion')) !== null) {
                        $object->setToolVersion($toolVersion);
                    }

                    $responseDeclarationElts = $this->getChildElementsByTagName($element, 'responseDeclaration');
                    if (!empty($responseDeclarationElts)) {
                        $responseDeclarations = new ResponseDeclarationCollection();

                        foreach ($responseDeclarationElts as $responseDeclarationElt) {
                            $marshaller = $this->getMarshallerFactory()->createMarshaller($responseDeclarationElt);
                            $responseDeclarations[] = $marshaller->unmarshall($responseDeclarationElt);
                        }

                        $object->setResponseDeclarations($responseDeclarations);
                    }

                    $outcomeDeclarationElts = $this->getChildElementsByTagName($element, 'outcomeDeclaration');
                    if (!empty($outcomeDeclarationElts)) {
                        $outcomeDeclarations = new OutcomeDeclarationCollection();

                        foreach ($outcomeDeclarationElts as $outcomeDeclarationElt) {
                            $marshaller = $this->getMarshallerFactory()->createMarshaller($outcomeDeclarationElt);
                            $outcomeDeclarations[] = $marshaller->unmarshall($outcomeDeclarationElt);
                        }

                        $object->setOutcomeDeclarations($outcomeDeclarations);
                    }

                    $templateDeclarationElts = $this->getChildElementsByTagName($element, 'templateDeclaration');
                    if (!empty($templateDeclarationElts)) {
                        $templateDeclarations = new TemplateDeclarationCollection();

                        foreach ($templateDeclarationElts as $templateDeclarationElt) {
                            $marshaller = $this->getMarshallerFactory()->createMarshaller($templateDeclarationElt);
                            $templateDeclarations[] = $marshaller->unmarshall($templateDeclarationElt);
                        }

                        $object->setTemplateDeclarations($templateDeclarations);
                    }

                    $templateProcessingElts = $this->getChildElementsByTagName($element, 'templateProcessing');
                    if (!empty($templateProcessingElts)) {
                        $marshaller = $this->getMarshallerFactory()->createMarshaller($templateProcessingElts[0]);
                        $object->setTemplateProcessing($marshaller->unmarshall($templateProcessingElts[0]));
                    }

                    $stylesheetElts = $this->getChildElementsByTagName($element, 'stylesheet');
                    if (!empty($stylesheetElts)) {
                        $stylesheets = new StylesheetCollection();

                        foreach ($stylesheetElts as $stylesheetElt) {
                            $marshaller = $this->getMarshallerFactory()->createMarshaller($stylesheetElt);
                            $stylesheets[] = $marshaller->unmarshall($stylesheetElt);
                        }

                        $object->setStylesheets($stylesheets);
                    }

                    $itemBodyElts = $this->getChildElementsByTagName($element, 'itemBody');
                    if (count($itemBodyElts) > 0) {
                        $marshaller = $this->getMarshallerFactory()->createMarshaller($itemBodyElts[0]);
                        $object->setItemBody($marshaller->unmarshall($itemBodyElts[0]));
                    }

                    $responseProcessingElts = $this->getChildElementsByTagName($element, 'responseProcessing');
                    if (!empty($responseProcessingElts)) {
                        $marshaller = $this->getMarshallerFactory()->createMarshaller($responseProcessingElts[0]);
                        $object->setResponseProcessing($marshaller->unmarshall($responseProcessingElts[0]));
                    }

                    $modalFeedbackElts = $this->getChildElementsByTagName($element, 'modalFeedback');
                    if (!empty($modalFeedbackElts)) {
                        $modalFeedbacks = new ModalFeedbackCollection();

                        foreach ($modalFeedbackElts as $modalFeedbackElt) {
                            $marshaller = $this->getMarshallerFactory()->createMarshaller($modalFeedbackElt);
                            $modalFeedbacks[] = $marshaller->unmarshall($modalFeedbackElt);
                        }

                        $object->setModalFeedbacks($modalFeedbacks);
                    }

                    return $object;
                } else {
                    $msg = "The mandatory attribute 'title' is missing from element '" . $element->localName . "'.";
                    throw new UnmarshallingException($msg, $element);
                }
            } else {
                $msg = "The mandatory attribute 'timeDependent' is missing from element '" . $element->localName . "'.";
                throw new UnmarshallingException($msg, $element);
            }
        } else {
            $msg = "The mandatory attribute 'identifier' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName()
    {
        return 'assessmentItem';
    }
}
