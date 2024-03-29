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

use DateTime;
use DateTimeZone;
use DOMElement;
use qtism\common\datatypes\QtiIdentifier;
use qtism\data\QtiComponent;
use qtism\data\results\ItemVariableCollection;
use qtism\data\results\TestResult;

/**
 * Class TestResultMarshaller
 *
 * The marshaller to manage serialization between QTI component and DOM Element
 */
class TestResultMarshaller extends Marshaller
{
    /**
     * Marshall a QtiComponent object into its QTI-XML equivalent.
     *
     * @param QtiComponent|TestResult $component A QtiComponent object to marshall.
     * @return DOMElement A DOMElement object.
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        $element = $this->createElement($component);

        $element->setAttribute('identifier', (string)$component->getIdentifier());

        $datestamp = $component->getDatestamp()->format('c'); // ISO 8601
        $element->setAttribute('datestamp', $datestamp);

        if ($component->hasItemVariables()) {
            foreach ($component->getItemVariables() as $variable) {
                $element->appendChild($this->getMarshallerFactory()
                    ->createMarshaller($variable)
                    ->marshall($variable));
            }
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI sessionIdentifier element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return TestResult A QtiComponent object.
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element): TestResult
    {
        if (!$element->hasAttribute('identifier')) {
            throw new UnmarshallingException('TestResult element must have identifier attribute', $element);
        }

        if (!$element->hasAttribute('datestamp')) {
            throw new UnmarshallingException('TestResult element must have datestamp attribute', $element);
        }

        $identifier = new QtiIdentifier($element->getAttribute('identifier'));
        $datestamp = new DateTime($element->getAttribute('datestamp'), new DateTimeZone('UTC'));

        $variableElements = array_merge(
            $this->getChildElementsByTagName($element, 'responseVariable'),
            $this->getChildElementsByTagName($element, 'outcomeVariable'),
            $this->getChildElementsByTagName($element, 'templateVariable')
        );

        if (!empty($variableElements)) {
            $variables = [];
            foreach ($variableElements as $variableElement) {
                $variables[] = $this->getMarshallerFactory()
                    ->createMarshaller($variableElement)
                    ->unmarshall($variableElement);
            }
            $variableCollection = new ItemVariableCollection($variables);
        } else {
            $variableCollection = null;
        }

        return new TestResult($identifier, $datestamp, $variableCollection);
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
    public function getExpectedQtiClassName(): string
    {
        return 'testResult';
    }
}
