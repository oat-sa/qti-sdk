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
use qtism\data\AssessmentTest;
use qtism\data\QtiComponent;
use qtism\data\state\OutcomeDeclarationCollection;
use qtism\data\TestFeedbackCollection;
use qtism\data\TestPartCollection;
use qtism\data\state\MetaData;
use qtism\data\state\CustomProperty;

/**
 * Marshalling/Unmarshalling implementation for assessmentTest.
 */
class AssessmentTestMarshaller extends SectionPartMarshaller
{
    /**
     * Marshall an AssessmentTest object into a DOMElement object.
     *
     * @param QtiComponent $component An AssessmentTest object.
     * @return DOMElement The according DOMElement object.
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        $element = $this->createElement($component);

        $this->setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
        $this->setDOMElementAttribute($element, 'title', $component->getTitle());

        $toolName = $component->getToolName();
        if (!empty($toolName)) {
            $this->setDOMElementAttribute($element, 'toolName', $component->getToolName());
        }

        $toolVersion = $component->getToolVersion();
        if (!empty($toolVersion)) {
            $this->setDOMElementAttribute($element, 'toolVersion', $component->getToolVersion());
        }

        foreach ($component->getOutcomeDeclarations() as $outcomeDeclaration) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($outcomeDeclaration);
            $element->appendChild($marshaller->marshall($outcomeDeclaration));
        }

        if ($component->hasTimeLimits() === true) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($component->getTimeLimits());
            $element->appendChild($marshaller->marshall($component->getTimeLimits()));
        }

        foreach ($component->getTestParts() as $part) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($part);
            $element->appendChild($marshaller->marshall($part));
        }

        $outcomeProcessing = $component->getOutcomeProcessing();
        if (!empty($outcomeProcessing)) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($outcomeProcessing);
            $element->appendChild($marshaller->marshall($outcomeProcessing));
        }

        foreach ($component->getTestFeedbacks() as $feedback) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($feedback);
            $element->appendChild($marshaller->marshall($feedback));
        }

        // Metadata
        if (method_exists($component, 'getMetaData')) {
            $metaData = $component->getMetaData();
            if ($metaData instanceof MetaData && count($metaData->getCustomProperties()) > 0) {
                $metadataElt = $element->ownerDocument->createElementNS($element->namespaceURI, 'metadata');
                foreach ($metaData->getCustomProperties() as $prop) {
                    $propElt = $element->ownerDocument->createElementNS($element->namespaceURI, 'customProperty');

                    $uriElt = $element->ownerDocument->createElementNS($element->namespaceURI, 'uri');
                    $uriElt->nodeValue = $prop->getUri();
                    $propElt->appendChild($uriElt);

                    if ($prop->getLabel() !== null) {
                        $labelElt = $element->ownerDocument->createElementNS($element->namespaceURI, 'label');
                        $labelElt->nodeValue = $prop->getLabel();
                        $propElt->appendChild($labelElt);
                    }
                    if ($prop->getDomain() !== null) {
                        $domainElt = $element->ownerDocument->createElementNS($element->namespaceURI, 'domain');
                        $domainElt->nodeValue = $prop->getDomain();
                        $propElt->appendChild($domainElt);
                    }
                    if ($prop->getChecksum() !== null) {
                        $checksumElt = $element->ownerDocument->createElementNS($element->namespaceURI, 'checksum');
                        $checksumElt->nodeValue = $prop->getChecksum();
                        $propElt->appendChild($checksumElt);
                    }
                    if ($prop->getWidget() !== null) {
                        $widgetElt = $element->ownerDocument->createElementNS($element->namespaceURI, 'widget');
                        $widgetElt->nodeValue = $prop->getWidget();
                        $propElt->appendChild($widgetElt);
                    }
                    if ($prop->getAlias() !== null) {
                        $aliasElt = $element->ownerDocument->createElementNS($element->namespaceURI, 'alias');
                        $aliasElt->nodeValue = $prop->getAlias();
                        $propElt->appendChild($aliasElt);
                    }
                    if ($prop->getMultiple() !== null) {
                        $multipleElt = $element->ownerDocument->createElementNS($element->namespaceURI, 'multiple');
                        $multipleElt->nodeValue = $prop->getMultiple();
                        $propElt->appendChild($multipleElt);
                    }
                    if ($prop->getScale() !== null) {
                        $scaleElt = $element->ownerDocument->createElementNS($element->namespaceURI, 'scale');
                        $scaleElt->nodeValue = $prop->getScale();
                        $propElt->appendChild($scaleElt);
                    }

                    $metadataElt->appendChild($propElt);
                }
                $element->appendChild($metadataElt);
            }
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI outcomeProcessing element.
     *
     * If $assessmentTest is provided, it will be decorated with the unmarshalled data and returned,
     * instead of creating a new AssessmentTest object.
     *
     * @param DOMElement $element A DOMElement object.
     * @param AssessmentTest|null $assessmentTest An AssessmentTest object to decorate.
     * @return AssessmentTest An OutcomeProcessing object.
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element, ?AssessmentTest $assessmentTest = null): AssessmentTest
    {
        if (($identifier = $this->getDOMElementAttributeAs($element, 'identifier')) !== null) {
            if (($title = $this->getDOMElementAttributeAs($element, 'title')) !== null) {
                if (empty($assessmentTest)) {
                    $object = new AssessmentTest($identifier, $title);
                } else {
                    $object = $assessmentTest;
                    $object->setIdentifier($identifier);
                    $object->setTitle($title);
                }

                // Get the test parts.
                $testPartsElts = $this->getChildElementsByTagName($element, 'testPart');

                if (count($testPartsElts) > 0) {
                    $testParts = new TestPartCollection();

                    foreach ($testPartsElts as $partElt) {
                        $marshaller = $this->getMarshallerFactory()->createMarshaller($partElt);
                        $testParts[] = $marshaller->unmarshall($partElt);
                    }

                    $object->setTestParts($testParts);

                    if (($toolName = $this->getDOMElementAttributeAs($element, 'toolName')) !== null) {
                        $object->setToolName($toolName);
                    }

                    if (($toolVersion = $this->getDOMElementAttributeAs($element, 'toolVersion')) !== null) {
                        $object->setToolVersion($toolVersion);
                    }

                    $testFeedbackElts = $this->getChildElementsByTagName($element, 'testFeedback');
                    if (count($testFeedbackElts) > 0) {
                        $testFeedbacks = new TestFeedbackCollection();

                        foreach ($testFeedbackElts as $feedbackElt) {
                            $marshaller = $this->getMarshallerFactory()->createMarshaller($feedbackElt);
                            $testFeedbacks[] = $marshaller->unmarshall($feedbackElt);
                        }

                        $object->setTestFeedbacks($testFeedbacks);
                    }

                    $outcomeDeclarationElts = $this->getChildElementsByTagName($element, 'outcomeDeclaration');
                    if (count($outcomeDeclarationElts) > 0) {
                        $outcomeDeclarations = new OutcomeDeclarationCollection();

                        foreach ($outcomeDeclarationElts as $outcomeDeclarationElt) {
                            $marshaller = $this->getMarshallerFactory()->createMarshaller($outcomeDeclarationElt);
                            $outcomeDeclarations[] = $marshaller->unmarshall($outcomeDeclarationElt);
                        }

                        $object->setOutcomeDeclarations($outcomeDeclarations);
                    }

                    $outcomeProcessingElts = $this->getChildElementsByTagName($element, 'outcomeProcessing');
                    if (isset($outcomeProcessingElts[0])) {
                        $marshaller = $this->getMarshallerFactory()->createMarshaller($outcomeProcessingElts[0]);
                        $object->setOutcomeProcessing($marshaller->unmarshall($outcomeProcessingElts[0]));
                    }

                    $timeLimitsElts = $this->getChildElementsByTagName($element, 'timeLimits');
                    if (isset($timeLimitsElts[0])) {
                        $marshaller = $this->getMarshallerFactory()->createMarshaller($timeLimitsElts[0]);
                        $object->setTimeLimits($marshaller->unmarshall($timeLimitsElts[0]));
                    }

                    // Metadata
                    $metadataElts = $this->getChildElementsByTagName($element, 'metadata');
                    if (isset($metadataElts[0])) {
                        $metadata = new MetaData();
                        $customPropertyElts = $this->getChildElementsByTagName($metadataElts[0], 'customProperty');
                        foreach ($customPropertyElts as $cpElt) {
                            $uriElts = $this->getChildElementsByTagName($cpElt, 'uri');
                            if (!isset($uriElts[0])) {
                                continue;
                            }
                            $prop = new CustomProperty($uriElts[0]->textContent);

                            $labelElts = $this->getChildElementsByTagName($cpElt, 'label');
                            if (isset($labelElts[0])) {
                                $prop->setLabel($labelElts[0]->textContent);
                            }
                            $domainElts = $this->getChildElementsByTagName($cpElt, 'domain');
                            if (isset($domainElts[0])) {
                                $prop->setDomain($domainElts[0]->textContent);
                            }
                            $checksumElts = $this->getChildElementsByTagName($cpElt, 'checksum');
                            if (isset($checksumElts[0])) {
                                $prop->setChecksum($checksumElts[0]->textContent);
                            }
                            $widgetElts = $this->getChildElementsByTagName($cpElt, 'widget');
                            if (isset($widgetElts[0])) {
                                $prop->setWidget($widgetElts[0]->textContent);
                            }
                            $aliasElts = $this->getChildElementsByTagName($cpElt, 'alias');
                            if (isset($aliasElts[0])) {
                                $prop->setAlias($aliasElts[0]->textContent);
                            }
                            $multipleElts = $this->getChildElementsByTagName($cpElt, 'multiple');
                            if (isset($multipleElts[0])) {
                                $prop->setMultiple($multipleElts[0]->textContent);
                            }
                            $scaleElts = $this->getChildElementsByTagName($cpElt, 'scale');
                            if (isset($scaleElts[0])) {
                                $prop->setScale($scaleElts[0]->textContent);
                            }

                            $metadata->addCustomProperty($prop);
                        }
                        $object->setMetaData($metadata);
                    }

                    return $object;
                } else {
                    $msg = "An 'assessmentTest' element must contain at least one 'testPart' child element. None found.";
                    throw new UnmarshallingException($msg, $element);
                }
            } else {
                $msg = "The mandatory attribute 'title' is missing from element 'assessmentTest'.";
                throw new UnmarshallingException($msg, $element);
            }
        } else {
            $msg = "The mandatory attribute 'identifier' is missing from element 'assessmentTest'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return 'assessmentTest';
    }
}
