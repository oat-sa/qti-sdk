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
 * Copyright (c) 2018-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Moyon Camille <camille@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\marshalling;

use DOMElement;
use InvalidArgumentException;
use qtism\data\QtiComponent;
use qtism\data\results\AssessmentResult;
use qtism\data\results\Context;
use qtism\data\results\ItemResultCollection;

/**
 * Class AssessmentResultMarshaller
 *
 * The marshaller to manage serialization between QTI component and DOM Element
 */
class AssessmentResultMarshaller extends Marshaller
{
    /**
     * Marshall a QtiComponent object into its QTI-XML equivalent.
     *
     * @param QtiComponent|AssessmentResult $component A QtiComponent object to marshall.
     * @return DOMElement A DOMElement object.
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component)
    {
        $element = self::getDOMCradle()->createElement($this->getExpectedQtiClassName());

        $context = $component->getContext();
        $element->appendChild($this->getMarshallerFactory()->createMarshaller($context)->marshall($context));

        if ($component->hasTestResult()) {
            $testResult = $component->getTestResult();
            $element->appendChild($this->getMarshallerFactory()->createMarshaller($testResult)->marshall($testResult));
        }

        if ($component->hasItemResults()) {
            foreach ($component->getItemResults() as $itemResult) {
                $element->appendChild($this->getMarshallerFactory()->createMarshaller($itemResult)->marshall($itemResult));
            }
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI sessionIdentifier element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A QtiComponent object.
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element)
    {
        try {
            /** @var Context $context */
            $contextElements = self::getChildElementsByTagName($element, 'context');
            $contextElement = array_shift($contextElements);
            $context = $this->getMarshallerFactory()->createMarshaller($contextElement)->unmarshall($contextElement);
        } catch (InvalidArgumentException $e) {
            $msg = "An 'assessmentResult' element must contain one 'context' element, none given.";
            throw new UnmarshallingException($msg, $element, $e);
        }

        $assessmentResult = new AssessmentResult($context);

        try {
            $testResultElements = $element->getElementsByTagName('testResult');
            if ($testResultElements->length > 0) {
                $testResultElement = $testResultElements->item(0);
                $assessmentResult->setTestResult($this->getMarshallerFactory()->createMarshaller($testResultElement)->unmarshall($testResultElement));
            }

            $itemResultElements = $element->getElementsByTagName('itemResult');
            if ($itemResultElements->length > 0) {
                $itemResults = [];
                foreach ($itemResultElements as $itemResultElement) {
                    $itemResults[] = $this->getMarshallerFactory()
                        ->createMarshaller($itemResultElement)
                        ->unmarshall($itemResultElement);
                }
                $assessmentResult->setItemResults(new ItemResultCollection($itemResults));
            }
        } catch (InvalidArgumentException $e) {
            throw new UnmarshallingException('Error has occurred during unmarshalling of AssessmentResult element : ' . $e->getMessage(), $element, $e);
        }

        return $assessmentResult;
    }

    /**
     * Get the class name/tag name of the QtiComponent/DOMElement which can be handled
     * by the Marshaller's implementation.
     *
     * Return an empty string if the marshaller implementation does not expect a particular
     * QTI class name.
     *
     * @return string A QTI class name or an empty string.
     */
    public function getExpectedQtiClassName()
    {
        return 'assessmentResult';
    }
}
