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
 * Copyright (c) 2013-2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\marshalling;

use qtism\data\content\ObjectFlowCollection;
use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use \DOMElement;

/**
 * The Marshaller implementation for object elements of the content model.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ObjectMarshaller extends ContentMarshaller
{
    /**
     * @see \qtism\data\storage\xml\marshalling\RecursiveMarshaller::unmarshallChildrenKnown()
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children)
    {
        // At item authoring time, we could admit that an empty data attribute
        // may occur.
        if (($data = $this->getDOMElementAttributeAs($element, 'data')) === null) {
            $data = '';
        }

        if (($type = $this->getDOMElementAttributeAs($element, 'type')) !== null) {

            $fqClass = $this->lookupClass($element);
            $component = new $fqClass($data, $type);
            $component->setContent(new ObjectFlowCollection($children->getArrayCopy()));

            if (($width = $this->getDOMElementAttributeAs($element, 'width', 'integer')) !== null) {
                $component->setWidth($width);
            }

            if (($height = $this->getDOMElementAttributeAs($element, 'height', 'integer')) !== null) {
                $component->setHeight($height);
            }

            if (($xmlBase = self::getXmlBase($element)) !== false) {
                $component->setXmlBase($xmlBase);
            }

            $this->fillBodyElement($component, $element);

            return $component;
        } else {
            $msg = "The mandatory attribute 'type' is missign from the 'object' element.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @see \qtism\data\storage\xml\marshalling\RecursiveMarshaller::marshallChildrenKnown()
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements)
    {
        $element = self::getDOMCradle()->createElement($component->getQtiClassName());
        $this->setDOMElementAttribute($element, 'data', $component->getData());
        $this->setDOMElementAttribute($element, 'type', $component->getType());

        if ($component->hasWidth() === true) {
            $this->setDOMElementAttribute($element, 'width', $component->getWidth());
        }

        if ($component->hasHeight() === true) {
            $this->setDOMElementAttribute($element, 'height', $component->getHeight());
        }

        if ($component->hasXmlBase() === true) {
            self::setXmlBase($element, $component->getXmlBase());
        }

        foreach ($elements as $e) {
            $element->appendChild($e);
        }

        $this->fillElement($element, $component);

        return $element;
    }

    /**
     * @see \qtism\data\storage\xml\marshalling\ContentMarshaller::setLookupClasses()
     */
    protected function setLookupClasses()
    {
        $this->lookupClasses = array("qtism\\data\\content\\xhtml");
    }
}
