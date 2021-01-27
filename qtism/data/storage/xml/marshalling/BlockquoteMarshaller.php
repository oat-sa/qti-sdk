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
use InvalidArgumentException;
use qtism\data\content\BlockCollection;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * The Marshaller implementation for Blockquote elements of the content model.
 */
class BlockquoteMarshaller extends ContentMarshaller
{
    /**
     * @param DOMElement $element
     * @param QtiComponentCollection $children
     * @return mixed
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children)
    {
        $fqClass = $this->lookupClass($element);
        $component = new $fqClass();

        $blockCollection = new BlockCollection();
        foreach ($children as $c) {
            try {
                $blockCollection[] = $c;
            } catch (InvalidArgumentException $e) {
                $msg = "A 'blockquote' element cannot contain '" . $c->getQtiClassName() . "' elements.";
                throw new UnmarshallingException($msg, $element);
            }
        }
        $component->setContent($blockCollection);

        if (($cite = $this->getDOMElementAttributeAs($element, 'cite')) !== null) {
            $component->setCite($cite);
        }

        if (($xmlBase = self::getXmlBase($element)) !== false) {
            $component->setXmlBase($xmlBase);
        }

        $this->fillBodyElement($component, $element);

        return $component;
    }

    /**
     * @param QtiComponent $component
     * @param array $elements
     * @return DOMElement
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements)
    {
        $element = $this->createElement($component);

        if ($component->hasCite() === true) {
            $element->setAttribute('cite', $component->getCite());
        }

        if ($component->hasXmlBase() === true) {
            $element->setAttribute('xml:base', $component->getXmlBase());
        }

        foreach ($elements as $e) {
            $element->appendChild($e);
        }

        $this->fillElement($element, $component);

        return $element;
    }

    protected function setLookupClasses()
    {
        $this->lookupClasses = ["qtism\\data\\content\\xhtml\\text"];
    }
}
