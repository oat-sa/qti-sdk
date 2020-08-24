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
use qtism\data\rules\ExitTemplate;
use qtism\data\rules\SetCorrectResponse;
use qtism\data\rules\SetDefaultValue;
use qtism\data\rules\SetTemplateValue;
use qtism\data\rules\TemplateConstraint;
use qtism\data\rules\TemplateElseIf;
use qtism\data\rules\TemplateIf;
use qtism\data\rules\TemplateRuleCollection;
use ReflectionClass;

/**
 * Unmarshalling/Marshalling implementation focusing on the components composing
 * TemplateCondition QTI components e.g. TemplateIf, TemplateElseIf, ...
 */
class TemplateControlMarshaller extends RecursiveMarshaller
{
    /**
     * @param DOMElement $element
     * @param QtiComponentCollection $children
     * @return mixed
     * @throws UnmarshallingException
     * @throws \ReflectionException
     * @see \qtism\data\storage\xml\marshalling\RecursiveMarshaller::unmarshallChildrenKnown()
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children)
    {
        $expressionElts = $this->getChildElementsByTagName($element, Expression::getExpressionClassNames());

        if (count($expressionElts) > 0) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($expressionElts[0]);
            $expression = $marshaller->unmarshall($expressionElts[0]);
        } elseif (($element->localName == 'templateIf' || $element->localName == 'templateElseIf') && count($expressionElts) == 0) {
            $msg = "An '" . $element->localName . "' must contain an 'expression' element. None found at line " . $element->getLineNo() . "'.";
            throw new UnmarshallingException($msg, $element);
        }

        if ($element->localName == 'templateIf' || $element->localName == 'templateElseIf') {
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
     * @throws MarshallingException
     * @see \qtism\data\storage\xml\marshalling\RecursiveMarshaller::marshallChildrenKnown()
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements)
    {
        $element = self::getDOMCradle()->createElement($component->getQtiClassName());

        if ($component instanceof TemplateIf || $component instanceof TemplateElseIf) {
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
     * @see \qtism\data\storage\xml\marshalling\RecursiveMarshaller::isElementFinal()
     */
    protected function isElementFinal(DOMNode $element)
    {
        return in_array($element->localName, array_merge([
            'setDefaultValue',
            'setCorrectResponse',
            'setTemplateValue',
            'templateConstraint',
            'exitTemplate',
        ]));
    }

    /**
     * @param QtiComponent $component
     * @return bool
     * @see \qtism\data\storage\xml\marshalling\RecursiveMarshaller::isComponentFinal()
     */
    protected function isComponentFinal(QtiComponent $component)
    {
        return ($component instanceof ExitTemplate ||
            $component instanceof SetDefaultValue ||
            $component instanceof SetCorrectResponse ||
            $component instanceof SetTemplateValue ||
            $component instanceof TemplateConstraint);
    }

    /**
     * @param DOMElement $element
     * @return array
     * @see \qtism\data\storage\xml\marshalling\RecursiveMarshaller::getChildrenElements()
     */
    protected function getChildrenElements(DOMElement $element)
    {
        return $this->getChildElementsByTagName($element, [
            'exitTemplate',
            'setDefaultValue',
            'setCorrectResponse',
            'setTemplateValue',
            'templateConstraint',
            'templateCondition',
        ]);
    }

    /**
     * @param QtiComponent $component
     * @return array
     * @see \qtism\data\storage\xml\marshalling\RecursiveMarshaller::getChildrenComponents()
     */
    protected function getChildrenComponents(QtiComponent $component)
    {
        return $component->getTemplateRules()->getArrayCopy();
    }

    /**
     * @param DOMElement $currentNode
     * @return TemplateRuleCollection
     * @see \qtism\data\storage\xml\marshalling\RecursiveMarshaller::createCollection()
     */
    protected function createCollection(DOMElement $currentNode)
    {
        return new TemplateRuleCollection();
    }

    /**
     * @see \qtism\data\storage\xml\marshalling\Marshaller::getExpectedQtiClassName()
     */
    public function getExpectedQtiClassName()
    {
        return '';
    }
}
