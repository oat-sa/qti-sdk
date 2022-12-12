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
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiUri;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\QtiComponent;
use qtism\data\results\ResultOutcomeVariable;
use qtism\data\state\ValueCollection;
use qtism\data\View;

/**
 * Class OutcomeVariableMarshaller
 *
 * The marshaller to manage serialization between QTI component and DOM Element
 */
class OutcomeVariableMarshaller extends Marshaller
{
    /**
     * Marshall a QtiComponent object into its QTI-XML equivalent.
     *
     * @param QtiComponent|ResultOutcomeVariable $component A QtiComponent object to marshall.
     * @return DOMElement A DOMElement object.
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        $element = $this->createElement($component);
        $element->setAttribute('identifier', (string)$component->getIdentifier());
        $element->setAttribute('cardinality', Cardinality::getNameByConstant($component->getCardinality()));
        $element->setAttribute('baseType', BaseType::getNameByConstant($component->getBaseType()));

        if ($component->hasView()) {
            $element->setAttribute('view', View::getNameByConstant($component->getView()));
        }

        if ($component->hasInterpretation()) {
            $element->setAttribute('interpretation', (string)$component->getInterpretation());
        }

        if ($component->hasLongInterpretation()) {
            $element->setAttribute('longInterpretation', (string)$component->getLongInterpretation());
        }

        if ($component->hasNormalMinimum()) {
            $element->setAttribute('normalMinimum', (string)$component->getNormalMinimum());
        }

        if ($component->hasNormalMaximum()) {
            $element->setAttribute('normalMaximum', (string)$component->getNormalMaximum());
        }

        if ($component->hasMasteryValue()) {
            $element->setAttribute('masteryValue', (string)$component->getMasteryValue());
        }

        if ($component->hasValues()) {
            foreach ($component->getValues() as $value) {
                $marshaller = $this->getMarshallerFactory()->createMarshaller($value);
                $element->appendChild($marshaller->marshall($value));
            }
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI sessionIdentifier element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return ResultOutcomeVariable A QtiComponent object.
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element): ResultOutcomeVariable
    {
        if (!$element->hasAttribute('identifier')) {
            throw new UnmarshallingException('OutcomeVariable element must have identifier attribute', $element);
        }

        if (!$element->hasAttribute('cardinality')) {
            throw new UnmarshallingException('OutcomeVariable element must have cardinality attribute', $element);
        }

        $identifier = new QtiIdentifier($element->getAttribute('identifier'));
        $cardinality = Cardinality::getConstantByName($element->getAttribute('cardinality'));

        $component = new ResultOutcomeVariable($identifier, $cardinality);
        if ($element->hasAttribute('baseType')) {
            $component->setBaseType(BaseType::getConstantByName($element->getAttribute('baseType')));
        }

        if ($element->hasAttribute('view')) {
            $component->setView(View::getConstantByName($element->getAttribute('view')));
        }

        if ($element->hasAttribute('interpretation')) {
            $component->setInterpretation(new QtiString($element->getAttribute('interpretation')));
        }

        if ($element->hasAttribute('longInterpretation')) {
            $component->setLongInterpretation(new QtiUri($element->getAttribute('longInterpretation')));
        }

        if ($element->hasAttribute('normalMinimum')) {
            $component->setNormalMinimum(new QtiFloat((float)$element->getAttribute('normalMinimum')));
        }

        if ($element->hasAttribute('normalMaximum')) {
            $component->setNormalMaximum(new QtiFloat((float)$element->getAttribute('normalMaximum')));
        }

        if ($element->hasAttribute('masteryValue')) {
            $component->setMasteryValue(new QtiFloat((float)$element->getAttribute('masteryValue')));
        }

        $valuesElements = $this->getChildElementsByTagName($element, 'value');
        if (!empty($valuesElements)) {
            $values = [];
            foreach ($valuesElements as $valuesElement) {
                $values[] = $this->getMarshallerFactory()
                    ->createMarshaller($valuesElement)
                    ->unmarshall($valuesElement);
            }
            $component->setValues(new ValueCollection($values));
        }

        return $component;
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
        return 'outcomeVariable';
    }
}
