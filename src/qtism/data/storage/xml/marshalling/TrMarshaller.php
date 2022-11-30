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
use InvalidArgumentException;
use qtism\data\content\xhtml\tables\TableCellCollection;
use qtism\data\content\xhtml\tables\Tr;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * The Marshaller implementation for XHTML tr elements of the content model.
 */
class TrMarshaller extends ContentMarshaller
{
    /**
     * @param DOMElement $element
     * @param QtiComponentCollection $children
     * @return Tr
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children): Tr
    {
        try {
            $component = new Tr(new TableCellCollection($children->getArrayCopy()));
            $this->fillBodyElement($component, $element);

            return $component;
        } catch (InvalidArgumentException $e) {
            $msg = "A 'tr' element must contain at least one 'td' or 'th' element.";
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

        foreach ($elements as $e) {
            $element->appendChild($e);
        }

        $this->fillElement($element, $component);

        return $element;
    }

    protected function setLookupClasses(): void
    {
        $this->lookupClasses = ["qtism\\data\\content\\xhtml\\tables"];
    }
}
