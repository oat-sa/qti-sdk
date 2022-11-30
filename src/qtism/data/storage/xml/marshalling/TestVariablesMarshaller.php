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
use qtism\common\enums\BaseType;
use qtism\data\expressions\TestVariables;
use qtism\data\QtiComponent;

/**
 * A marshalling/unmarshalling implementation for the QTI TesVariable expression.
 */
class TestVariablesMarshaller extends ItemSubsetMarshaller
{
    /**
     * Marshall a TestVariable object in its DOMElement equivalent.
     *
     * @param QtiComponent $component A TestVariable object.
     * @return DOMElement The corresponding testVariable QTI element.
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        $element = parent::marshall($component);

        $this->setDOMElementAttribute($element, 'variableIdentifier', $component->getVariableIdentifier());

        $baseType = $component->getBaseType();
        if ($baseType != -1) {
            $this->setDOMElementAttribute($element, 'baseType', BaseType::getNameByConstant($baseType));
        }

        $weightIdentifier = $component->getWeightIdentifier();
        if (!empty($weightIdentifier)) {
            $this->setDOMElementAttribute($element, 'weightIdentifier', $weightIdentifier);
        }

        return $element;
    }

    /**
     * Marshall a testVariable QTI element in its TestVariable object equivalent.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent The corresponding TestVariable object.
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element): TestVariables
    {
        $baseComponent = parent::unmarshall($element);

        if (($variableIdentifier = $this->getDOMElementAttributeAs($element, 'variableIdentifier')) !== null) {
            $object = new TestVariables($variableIdentifier);
            $object->setSectionIdentifier($baseComponent->getSectionIdentifier());
            $object->setIncludeCategories($baseComponent->getIncludeCategories());
            $object->setExcludeCategories($baseComponent->getExcludeCategories());

            if (($baseType = $this->getDOMElementAttributeAs($element, 'baseType')) !== null) {
                $object->setBaseType(BaseType::getConstantByName($baseType));
            }

            if (($weightIdentifier = $this->getDOMElementAttributeAs($element, 'weightIdentifier')) !== null) {
                $object->setWeightIdentifier($weightIdentifier);
            }

            return $object;
        } else {
            $msg = "The mandatory attribute 'variableIdentifier' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return 'testVariables';
    }
}
