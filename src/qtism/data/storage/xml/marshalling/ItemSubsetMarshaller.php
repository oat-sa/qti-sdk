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
use qtism\common\collections\IdentifierCollection;
use qtism\data\expressions\ItemSubset;
use qtism\data\QtiComponent;

/**
 * A complex Operator marshaller focusing on the marshalling/unmarshalling process
 * of itemSubset QTI operators.
 */
class ItemSubsetMarshaller extends Marshaller
{
    /**
     * @param QtiComponent $component
     * @return DOMElement
     */
    protected function marshall(QtiComponent $component)
    {
        $element = self::getDOMCradle()->createElement($this->getExpectedQtiClassName());

        $sectionIdentifier = $component->getSectionIdentifier();
        if (!empty($sectionIdentifier)) {
            $this->setDOMElementAttribute($element, 'sectionIdentifier', $sectionIdentifier);
        }

        $includeCategories = $component->getIncludeCategories();
        if (count($includeCategories) > 0) {
            $this->setDOMElementAttribute($element, 'includeCategory', implode(' ', $includeCategories->getArrayCopy()));
        }

        $excludeCategories = $component->getExcludeCategories();
        if (count($excludeCategories) > 0) {
            $this->setDOMElementAttribute($element, 'excludeCategory', implode(' ', $excludeCategories->getArrayCopy()));
        }

        return $element;
    }

    /**
     * @param DOMElement $element
     * @return ItemSubset
     */
    protected function unmarshall(DOMElement $element)
    {
        $object = new ItemSubset();

        if (($sectionIdentifier = $this->getDOMElementAttributeAs($element, 'sectionIdentifier')) !== null) {
            $object->setSectionIdentifier($sectionIdentifier);
        }

        if (($includeCategories = $this->getDOMElementAttributeAs($element, 'includeCategory')) !== null) {
            $includeCategories = new IdentifierCollection(explode("\x20", $includeCategories));
            $object->setIncludeCategories($includeCategories);
        }

        if (($excludeCategories = $this->getDOMElementAttributeAs($element, 'excludeCategory')) !== null) {
            $excludeCategories = new IdentifierCollection(explode("\x20", $excludeCategories));
            $object->setExcludeCategories($excludeCategories);
        }

        return $object;
    }

    public function getExpectedQtiClassName()
    {
        return 'itemSubset';
    }
}
