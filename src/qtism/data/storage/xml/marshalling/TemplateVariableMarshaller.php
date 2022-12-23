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
use qtism\data\results\ResultTemplateVariable;
use qtism\data\state\Value;
use qtism\data\state\ValueCollection;

/**
 * Class TemplateVariableMarshaller
 *
 * The marshaller to manage serialization between QTI component and DOM Element
 */
class TemplateVariableMarshaller extends Marshaller
{
    /**
     * Marshall a QtiComponent object into its QTI-XML equivalent.
     *
     * @param QtiComponent|ResultTemplateVariable $component A QtiComponent object to marshall.
     * @return DOMElement A DOMElement object.
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        $element = $this->createElement($component);
        $element->setAttribute('identifier', (string)$component->getIdentifier());
        $element->setAttribute('cardinality', Cardinality::getNameByConstant($component->getCardinality()));

        if ($component->hasBaseType()) {
            $element->setAttribute('baseType', BaseType::getNameByConstant($component->getBaseType()));
        }

        if ($component->hasValues()) {
            /** @var Value $value */
            foreach ($component->getValues() as $value) {
                $valueElement = $this->getMarshallerFactory()
                    ->createMarshaller($value)
                    ->marshall($value);
                $element->appendChild($valueElement);
            }
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI sessionIdentifier element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return ResultTemplateVariable A QtiComponent object.
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element): ResultTemplateVariable
    {
        if (!$element->hasAttribute('identifier')) {
            throw new UnmarshallingException('TemplateVariable element must have identifier attribute', $element);
        }

        if (!$element->hasAttribute('cardinality')) {
            throw new UnmarshallingException('TemplateVariable element must have cardinality attribute', $element);
        }

        $identifier = new QtiIdentifier($element->getAttribute('identifier'));
        $cardinality = Cardinality::getConstantByName($element->getAttribute('cardinality'));

        $baseType = $element->hasAttribute('baseType')
            ? BaseType::getConstantByName($element->getAttribute('baseType'))
            : null;

        $valuesElements = $this->getChildElementsByTagName($element, 'value');
        if (!empty($valuesElements)) {
            $values = [];
            foreach ($valuesElements as $valuesElement) {
                $values[] = $this->getMarshallerFactory()
                    ->createMarshaller($valuesElement)
                    ->unmarshall($valuesElement);
            }
            $valueCollection = new ValueCollection($values);
        } else {
            $valueCollection = null;
        }

        return new ResultTemplateVariable($identifier, $cardinality, $baseType, $valueCollection);
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
        return 'templateVariable';
    }
}
