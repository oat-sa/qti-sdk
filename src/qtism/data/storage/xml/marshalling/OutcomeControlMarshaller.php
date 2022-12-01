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
use DOMNode;
use qtism\common\utils\Reflection;
use qtism\data\expressions\Expression;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\rules\ExitTest;
use qtism\data\rules\LookupOutcomeValue;
use qtism\data\rules\OutcomeElseIf;
use qtism\data\rules\OutcomeIf;
use qtism\data\rules\OutcomeRuleCollection;
use qtism\data\rules\SetOutcomeValue;
use ReflectionClass;
use ReflectionException;

/**
 * Marshalling/Unmarshalling implementation for the abstract OutcomeControl QTI
 * component.
 */
class OutcomeControlMarshaller extends RecursiveMarshaller
{
    /**
     * @param DOMElement $element
     * @param QtiComponentCollection $children
     * @return mixed
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     * @throws ReflectionException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children): QtiComponent
    {
        $expressionElts = $this->getChildElementsByTagName($element, Expression::getExpressionClassNames());

        if (count($expressionElts) > 0) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($expressionElts[0]);
            $expression = $marshaller->unmarshall($expressionElts[0]);
        } elseif (($element->localName == 'outcomeIf' || $element->localName == 'outcomeElseIf') && count($expressionElts) == 0) {
            $msg = "An '" . $element->localName . "' must contain an 'expression' element. None found at line " . $element->getLineNo() . "'.";
            throw new UnmarshallingException($msg, $element);
        }

        if ($element->localName == 'outcomeIf' || $element->localName == 'outcomeElseIf') {
            $className = 'qtism\\data\\rules\\' . ucfirst($element->localName);
            $class = new ReflectionClass($className);
            $object = Reflection::newInstance($class, [$expression, $children]);
        } else {
            $className = 'qtism\\data\\rules\\' . ucfirst($element->localName);
            $class = new ReflectionClass($className);
            $object = Reflection::newInstance($class, [$children]);
        }

        return $object;
    }

    /**
     * @param QtiComponent $component
     * @param array $elements
     * @return DOMElement
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements): DOMElement
    {
        $element = $this->createElement($component);

        if ($component instanceof OutcomeIf || $component instanceof OutcomeElseIf) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($component->getExpression());
            $element->appendChild($marshaller->marshall($component->getExpression()));
        }

        foreach ($elements as $elt) {
            $element->appendChild($elt);
        }

        return $element;
    }

    /**
     * @param DOMNode $element
     * @return bool
     */
    protected function isElementFinal(DOMNode $element): bool
    {
        return in_array($element->localName, array_merge([
            'exitTest',
            'lookupOutcomeValue',
            'setOutcomeValue',
        ]));
    }

    /**
     * @param QtiComponent $component
     * @return bool
     */
    protected function isComponentFinal(QtiComponent $component): bool
    {
        return ($component instanceof ExitTest ||
            $component instanceof LookupOutcomeValue ||
            $component instanceof SetOutcomeValue);
    }

    /**
     * @param DOMElement $element
     * @return array
     */
    protected function getChildrenElements(DOMElement $element): array
    {
        return $this->getChildElementsByTagName($element, [
            'exitTest',
            'lookupOutcomeValue',
            'setOutcomeValue',
            'outcomeCondition',
        ]);
    }

    /**
     * @param QtiComponent $component
     * @return array
     */
    protected function getChildrenComponents(QtiComponent $component): array
    {
        return $component->getOutcomeRules()->getArrayCopy();
    }

    /**
     * @param DOMElement $currentNode
     * @return OutcomeRuleCollection
     */
    protected function createCollection(DOMElement $currentNode): OutcomeRuleCollection
    {
        return new OutcomeRuleCollection();
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return '';
    }
}
