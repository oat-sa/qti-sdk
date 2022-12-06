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
use qtism\data\content\ObjectFlowCollection;
use qtism\data\content\xhtml\ObjectElement;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * The Marshaller implementation for object elements of the content model.
 */
class ObjectMarshaller extends ContentMarshaller
{
    /**
     * @param DOMElement $element
     * @param QtiComponentCollection $children
     * @return mixed
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children): QtiComponent
    {
        // At item authoring time, we could admit that an empty data attribute
        // may occur.
        $data = $this->getDOMElementAttributeAs($element, 'data') ?? '';

        $type = $this->getDOMElementAttributeAs($element, 'type');
        if ($type === null) {
            $msg = "The mandatory attribute 'type' is missign from the 'object' element.";
            throw new UnmarshallingException($msg, $element);
        }

        $fqClass = $this->lookupClass($element);
        $component = new $fqClass($data, $type);
        $component->setContent(new ObjectFlowCollection($children->getArrayCopy()));
        $component->setHeight($this->getDOMElementAttributeAs($element, 'height'));
        $component->setWidth($this->getDOMElementAttributeAs($element, 'width'));

        $xmlBase = self::getXmlBase($element);
        if ($xmlBase !== false) {
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
    protected function marshallChildrenKnown(QtiComponent $component, array $elements): DOMElement
    {
        /** @var ObjectElement $component */
        $element = $this->createElement($component);
        $this->setDOMElementAttribute($element, 'data', $component->getData());
        $this->setDOMElementAttribute($element, 'type', $component->getType());

        if ($component->hasWidth()) {
            $this->setDOMElementAttribute($element, 'width', $component->getWidth());
        }

        if ($component->hasHeight()) {
            $this->setDOMElementAttribute($element, 'height', $component->getHeight());
        }

        if ($component->hasXmlBase()) {
            self::setXmlBase($element, $component->getXmlBase());
        }

        foreach ($elements as $e) {
            $element->appendChild($e);
        }

        $this->fillElement($element, $component);

        return $element;
    }

    protected function setLookupClasses(): void
    {
        $this->lookupClasses = ["qtism\\data\\content\\xhtml"];
    }
}
