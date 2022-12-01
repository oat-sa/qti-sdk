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
use qtism\data\NavigationMode;
use qtism\data\QtiComponent;
use qtism\data\rules\BranchRuleCollection;
use qtism\data\rules\PreConditionCollection;
use qtism\data\SectionPartCollection;
use qtism\data\SubmissionMode;
use qtism\data\TestFeedbackCollection;
use qtism\data\TestPart;

/**
 * Marshalling/Unmarshalling implementation for TestPart.
 */
class TestPartMarshaller extends Marshaller
{
    /**
     * @param QtiComponent $component
     * @return DOMElement
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        $element = $this->createElement($component);

        $this->setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
        $this->setDOMElementAttribute($element, 'navigationMode', NavigationMode::getNameByConstant($component->getNavigationMode()));
        $this->setDOMElementAttribute($element, 'submissionMode', SubmissionMode::getNameByConstant($component->getSubmissionMode()));

        foreach ($component->getPreConditions() as $preCondition) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($preCondition);
            $element->appendChild($marshaller->marshall($preCondition));
        }

        foreach ($component->getBranchRules() as $branchRule) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($branchRule);
            $element->appendChild($marshaller->marshall($branchRule));
        }

        $itemSessionControl = $component->getItemSessionControl();
        if (!empty($itemSessionControl)) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($itemSessionControl);
            $element->appendChild($marshaller->marshall($itemSessionControl));
        }

        $timeLimits = $component->getTimeLimits();
        if (!empty($timeLimits)) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($timeLimits);
            $element->appendChild($marshaller->marshall($timeLimits));
        }

        foreach ($component->getAssessmentSections() as $assessmentSection) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($assessmentSection);
            $element->appendChild($marshaller->marshall($assessmentSection));
        }

        foreach ($component->getTestFeedbacks() as $testFeedback) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($testFeedback);
            $element->appendChild($marshaller->marshall($testFeedback));
        }

        return $element;
    }

    /**
     * @param DOMElement $element
     * @return TestPart
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     */
    #[\ReturnTypeWillChange]
    protected function unmarshall(DOMElement $element): TestPart
    {
        if (($identifier = $this->getDOMElementAttributeAs($element, 'identifier')) !== null) {
            if (($navigationMode = $this->getDOMElementAttributeAs($element, 'navigationMode')) !== null) {
                if (($submissionMode = $this->getDOMElementAttributeAs($element, 'submissionMode')) !== null) {
                    // We do not use the regular DOMElement::getElementsByTagName method
                    // because it is recursive. We only want the first level elements with
                    // tagname = 'assessmentSection'.
                    $assessmentSectionElts = $this->getChildElementsByTagName($element, ['assessmentSection', 'assessmentSectionRef']);
                    $assessmentSections = new SectionPartCollection();
                    foreach ($assessmentSectionElts as $sectElt) {
                        $marshaller = $this->getMarshallerFactory()->createMarshaller($sectElt);
                        $assessmentSections[] = $marshaller->unmarshall($sectElt);
                    }

                    if (count($assessmentSections) > 0) {
                        // We can instantiate because all mandatory attributes/elements were found.
                        $navigationMode = NavigationMode::getConstantByName($navigationMode);
                        $submissionMode = SubmissionMode::getConstantByName($submissionMode);
                        $object = new TestPart($identifier, $assessmentSections, $navigationMode, $submissionMode);

                        // preConditions
                        $preConditionElts = $this->getChildElementsByTagName($element, 'preCondition');
                        $preConditions = new PreConditionCollection();
                        foreach ($preConditionElts as $preConditionElt) {
                            $marshaller = $this->getMarshallerFactory()->createMarshaller($preConditionElt);
                            $preConditions[] = $marshaller->unmarshall($preConditionElt);
                        }
                        $object->setPreConditions($preConditions);

                        // branchRules
                        $branchRuleElts = $this->getChildElementsByTagName($element, 'branchRule');
                        $branchRules = new BranchRuleCollection();
                        foreach ($branchRuleElts as $branchRuleElt) {
                            $marshaller = $this->getMarshallerFactory()->createMarshaller($branchRuleElt);
                            $branchRules[] = $marshaller->unmarshall($branchRuleElt);
                        }
                        $object->setBranchRules($branchRules);

                        // itemSessionControl
                        $itemSessionControlElts = $this->getChildElementsByTagName($element, 'itemSessionControl');
                        if (count($itemSessionControlElts) === 1) {
                            $marshaller = $this->getMarshallerFactory()->createMarshaller($itemSessionControlElts[0]);
                            $itemSessionControl = $marshaller->unmarshall($itemSessionControlElts[0]);
                            $object->setItemSessionControl($itemSessionControl);
                        }

                        // timeLimits
                        $timeLimitsElts = $this->getChildElementsByTagName($element, 'timeLimits');
                        if (count($timeLimitsElts) === 1) {
                            $marshaller = $this->getMarshallerFactory()->createMarshaller($timeLimitsElts[0]);
                            $timeLimits = $marshaller->unmarshall($timeLimitsElts[0]);
                            $object->setTimeLimits($timeLimits);
                        }

                        // testFeedbacks
                        $testFeedbackElts = $this->getChildElementsByTagName($element, 'testFeedback');
                        $testFeedbacks = new TestFeedbackCollection();
                        foreach ($testFeedbackElts as $testFeedbackElt) {
                            $marshaller = $this->getMarshallerFactory()->createMarshaller($testFeedbackElt);
                            $testFeedbacks[] = $marshaller->unmarshall($testFeedbackElt);
                        }
                        $object->setTestFeedbacks($testFeedbacks);

                        return $object;
                    } else {
                        $msg = 'A testPart element must contain at least one assessmentSection.';
                        throw new UnmarshallingException($msg, $element);
                    }
                } else {
                    $msg = "The mandatory attribute 'submissionMode' is missing from element '" . $element->localName . "'.";
                    throw new UnmarshallingException($msg, $element);
                }
            } else {
                $msg = "The mandatory attribute 'navigationMode' is missing from element '" . $element->localName . "'.";
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
    public function getExpectedQtiClassName(): string
    {
        return 'testPart';
    }
}
