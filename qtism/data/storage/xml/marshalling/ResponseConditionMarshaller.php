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
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\rules\ResponseCondition;
use qtism\data\rules\ResponseElse;
use qtism\data\rules\ResponseElseIf;
use qtism\data\rules\ResponseElseIfCollection;
use qtism\data\rules\ResponseIf;
use qtism\data\rules\ResponseRuleCollection;

/**
 * Marshalling/Unmarshalling implementation of ResponseCondition
 * QTI components.
 */
class ResponseConditionMarshaller extends RecursiveMarshaller
{
    /**
     * @param DOMElement $element
     * @param QtiComponentCollection $children
     * @return ResponseCondition
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children)
    {
        if (count($children) > 0) {
            // The first element of $children must be a responseIf.
            $responseIf = $children[0];
            $responseCondition = new ResponseCondition($responseIf);

            if (isset($children[1])) {
                $responseElseIfs = new ResponseElseIfCollection();
                // We have at least one elseIf.
                for ($i = 1; $i < count($children) - 1; $i++) {
                    $responseElseIfs[] = $children[$i];
                }

                $responseCondition->setResponseElseIfs($responseElseIfs);
                $lastOutcomeControl = $children[count($children) - 1];

                if ($lastOutcomeControl instanceof ResponseElseIf) {
                    // There is no else.
                    $responseElseIfs[] = $lastOutcomeControl;
                } else {
                    // We have an else.
                    $responseCondition->setResponseElse($lastOutcomeControl);
                }
            }

            return $responseCondition;
        } else {
            $msg = "A 'responseCondition' element must contain at least one 'responseIf' element. None given.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @param QtiComponent $component
     * @param array $elements
     * @return DOMElement
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements)
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
    protected function isElementFinal(DOMNode $element)
    {
        $exclusion = ['responseIf', 'responseElseIf', 'responseElse', 'responseCondition'];

        return !in_array($element->localName, $exclusion);
    }

    /**
     * @param QtiComponent $component
     * @return bool
     */
    protected function isComponentFinal(QtiComponent $component)
    {
        return (!$component instanceof ResponseIf &&
            !$component instanceof ResponseElseIf &&
            !$component instanceof ResponseElse &&
            !$component instanceof ResponseCondition);
    }

    /**
     * @param DOMElement $element
     * @return array
     */
    protected function getChildrenElements(DOMElement $element)
    {
        return $this->getChildElementsByTagName($element, [
            'responseIf',
            'responseElseIf',
            'responseElse',
            'exitResponse',
            'lookupOutcomeValue',
            'setOutcomeValue',
            'responseCondition',
        ]);
    }

    /**
     * @param QtiComponent $component
     * @return array
     */
    protected function getChildrenComponents(QtiComponent $component)
    {
        if ($component instanceof ResponseIf || $component instanceof ResponseElseIf || $component instanceof ResponseElse) {
            // ResponseControl
            return $component->getResponseRules()->getArrayCopy();
        } else {
            // ResponseCondition
            $returnValue = [$component->getResponseIf()];

            if (count($component->getResponseElseIfs()) > 0) {
                $returnValue = array_merge($returnValue, $component->getResponseElseIfs()->getArrayCopy());
            }

            if ($component->getResponseElse() !== null) {
                $returnValue[] = $component->getResponseElse();
            }

            return $returnValue;
        }
    }

    /**
     * @param DOMElement $currentNode
     * @return QtiComponentCollection|ResponseRuleCollection
     */
    protected function createCollection(DOMElement $currentNode)
    {
        if ($currentNode->localName != 'responseCondition') {
            return new ResponseRuleCollection();
        } else {
            return new QtiComponentCollection();
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName()
    {
        return '';
    }
}
