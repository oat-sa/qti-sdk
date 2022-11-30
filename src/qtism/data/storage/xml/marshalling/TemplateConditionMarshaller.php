<?php

declare(strict_types=1);

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
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\rules\TemplateCondition;
use qtism\data\rules\TemplateElse;
use qtism\data\rules\TemplateElseIf;
use qtism\data\rules\TemplateElseIfCollection;
use qtism\data\rules\TemplateIf;
use qtism\data\rules\TemplateRuleCollection;

/**
 * Marshalling/Unmarshalling implementation focusing on TemplateCondition
 * QTI components.
 */
class TemplateConditionMarshaller extends RecursiveMarshaller
{
    /**
     * @param DOMElement $element
     * @param QtiComponentCollection $children
     * @return TemplateCondition
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children): TemplateCondition
    {
        if (count($children) > 0) {
            // The first element of $children must be a templateIf.
            $templateIf = $children[0];
            $templateCondition = new TemplateCondition($templateIf);

            if (isset($children[1])) {
                $templateElseIfs = new TemplateElseIfCollection();
                // We have at least one elseIf.
                for ($i = 1; $i < count($children) - 1; $i++) {
                    $templateElseIfs[] = $children[$i];
                }

                $templateCondition->setTemplateElseIfs($templateElseIfs);
                $lastTemplateControl = $children[count($children) - 1];

                if ($lastTemplateControl instanceof TemplateElseIf) {
                    // There is no else.
                    $templateElseIfs[] = $lastTemplateControl;
                } else {
                    // We have an else.
                    $templateCondition->setTemplateElse($lastTemplateControl);
                }
            }

            return $templateCondition;
        } else {
            $msg = "A 'templateCondition' element must contain at least one 'templateIf' element. None given.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @param QtiComponent $component
     * @param array $elements
     * @return DOMElement
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements): DOMElement
    {
        $element = $this->createElement($component);

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
        return !in_array($element->localName, ['templateIf', 'templateElseIf', 'templateElse', 'templateCondition']);
    }

    /**
     * @param QtiComponent $component
     * @return bool
     */
    protected function isComponentFinal(QtiComponent $component): bool
    {
        return (!$component instanceof TemplateIf &&
            !$component instanceof TemplateElseIf &&
            !$component instanceof TemplateElse &&
            !$component instanceof TemplateCondition);
    }

    /**
     * @param DOMElement $element
     * @return array
     */
    protected function getChildrenElements(DOMElement $element): array
    {
        return $this->getChildElementsByTagName($element, [
            'templateIf',
            'templateElseIf',
            'templateElse',
            'exitTemplate',
            'templateConstraint',
            'setTemplateValue',
            'setCorrectResponse',
            'setDefaultValue',
            'templateCondition',
        ]);
    }

    /**
     * @param QtiComponent $component
     * @return array
     */
    protected function getChildrenComponents(QtiComponent $component): array
    {
        if ($component instanceof TemplateIf || $component instanceof TemplateElseIf || $component instanceof TemplateElse) {
            // TemplateControl
            return $component->getTemplateRules()->getArrayCopy();
        } else {
            // TemplateCondition
            $returnValue = [$component->getTemplateIf()];

            if (count($component->getTemplateElseIfs()) > 0) {
                $returnValue = array_merge($returnValue, $component->getTemplateElseIfs()->getArrayCopy());
            }

            if ($component->getTemplateElse() !== null) {
                $returnValue[] = $component->getTemplateElse();
            }

            return $returnValue;
        }
    }

    /**
     * @param DOMElement $currentNode
     * @return QtiComponentCollection|TemplateRuleCollection
     */
    #[\ReturnTypeWillChange]
    protected function createCollection(DOMElement $currentNode)
    {
        if ($currentNode->localName != 'templateCondition') {
            return new TemplateRuleCollection();
        } else {
            return new QtiComponentCollection();
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return '';
    }
}
