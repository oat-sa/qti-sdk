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
use qtism\data\expressions\NumberIncorrect;
use qtism\data\QtiComponent;

/**
 * A marshalling/unmarshalling implementation for the QTI numberIncorrect expression.
 */
class NumberIncorrectMarshaller extends ItemSubsetMarshaller
{
    /**
     * Marshall an NumberIncorrect object in its DOMElement equivalent.
     *
     * @param QtiComponent $component A NumberIncorrect object.
     * @return DOMElement The corresponding numberIncorrect QTI element.
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        return parent::marshall($component);
    }

    /**
     * Marshall an numberIncorrect QTI element in its NumberIncorrect object equivalent.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent The corresponding NumberIncorrect object.
     */
    #[\ReturnTypeWillChange]
    protected function unmarshall(DOMElement $element): NumberIncorrect
    {
        $baseComponent = parent::unmarshall($element);
        $object = new NumberIncorrect();
        $object->setSectionIdentifier($baseComponent->getSectionIdentifier());
        $object->setIncludeCategories($baseComponent->getIncludeCategories());
        $object->setExcludeCategories($baseComponent->getExcludeCategories());

        return $object;
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return 'numberIncorrect';
    }
}
