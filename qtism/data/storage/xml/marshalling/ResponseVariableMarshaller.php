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
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\QtiComponent;
use qtism\data\results\ResultResponseVariable;

/**
 * Class ResponseVariableMarshaller
 *
 * The marshaller to manage serialization between QTI component and DOM Element
 */
class ResponseVariableMarshaller extends Marshaller
{
    /**
     * Marshall a QtiComponent object into its QTI-XML equivalent.
     *
     * @param QtiComponent|ResultResponseVariable $component A QtiComponent object to marshall.
     * @return DOMElement A DOMElement object.
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component)
    {
        $element = self::getDOMCradle()->createElement($this->getExpectedQtiClassName());
        $element->setAttribute('identifier', $component->getIdentifier());
        $element->setAttribute('cardinality', Cardinality::getNameByConstant($component->getCardinality()));

        if ($component->hasBaseType()) {
            $element->setAttribute('baseType', BaseType::getNameByConstant($component->getBaseType()));
        }

        if ($component->hasChoiceSequence()) {
            $element->setAttribute('choiceSequence', $component->getChoiceSequence());
        }

        if ($component->hasCorrectResponse()) {
            $correctResponse = $component->getCorrectResponse();
            $marshaller = $this->getMarshallerFactory()->createMarshaller($correctResponse);
            $element->appendChild($marshaller->marshall($correctResponse));
        }

        $candidateResponse = $component->getCandidateResponse();
        $marshaller = $this->getMarshallerFactory()->createMarshaller($candidateResponse);
        $element->appendChild($marshaller->marshall($candidateResponse));

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
        if (!$element->hasAttribute('identifier')) {
            throw new UnmarshallingException('ResponseVariable element must have identifier attribute', $element);
        }

        if (!$element->hasAttribute('cardinality')) {
            throw new UnmarshallingException('ResponseVariable element must have cardinality attribute', $element);
        }

        $candidateResponseElements = self::getChildElementsByTagName($element, 'candidateResponse');
        if (empty($candidateResponseElements)) {
            throw new UnmarshallingException('ResponseVariable element must have candidateResponse element', $element);
        }

        $candidateResponseElement = array_shift($candidateResponseElements);
        $candidateResponse = $this->getMarshallerFactory()
            ->createMarshaller($candidateResponseElement)
            ->unmarshall($candidateResponseElement);

        $identifier = new QtiIdentifier($element->getAttribute('identifier'));
        $cardinality = Cardinality::getConstantByName($element->getAttribute('cardinality'));

        $baseType = $element->hasAttribute('baseType')
            ? BaseType::getConstantByName($element->getAttribute('baseType'))
            : null;

        $choiceSequence = $element->hasAttribute('choiceSequence')
            ? new QtiIdentifier($element->getAttribute('choiceSequence'))
            : null;

        $correctResponseElements = self::getChildElementsByTagName($element, 'correctResponse');
        if (!empty($correctResponseElements)) {
            $correctResponseElement = array_shift($correctResponseElements);
            $correctResponse = $this->getMarshallerFactory()
                ->createMarshaller($correctResponseElement)
                ->unmarshall($correctResponseElement);
        } else {
            $correctResponse = null;
        }

        return new ResultResponseVariable($identifier, $cardinality, $candidateResponse, $baseType, $correctResponse, $choiceSequence);
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
        return 'responseVariable';
    }
}
