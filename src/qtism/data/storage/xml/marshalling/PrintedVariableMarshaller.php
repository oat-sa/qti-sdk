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
use qtism\common\utils\Format;
use qtism\common\utils\Version;
use qtism\data\content\PrintedVariable;
use qtism\data\QtiComponent;

/**
 * Marshalling/Unmarshalling implementation for PrintedVariable.
 */
class PrintedVariableMarshaller extends Marshaller
{
    /**
     * Marshall a PrintedVariable object into a DOMElement object.
     *
     * @param QtiComponent $component A PrintedVariable object.
     * @return DOMElement The according DOMElement object.
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component)
    {
        $element = $this->createElement($component);
        $version = $this->getVersion();

        $this->setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
        $this->setDOMElementAttribute($element, 'base', $component->getBase());

        if (Version::compare($version, '2.1.0', '>=') === true) {
            $this->setDOMElementAttribute($element, 'powerForm', $component->mustPowerForm());
            $this->setDOMElementAttribute($element, 'delimiter', $component->getDelimiter());
            $this->setDOMElementAttribute($element, 'mappingIndicator', $component->getMappingIndicator());
        }

        if ($component->hasFormat() === true) {
            $this->setDOMElementAttribute($element, 'format', $component->getFormat());
        }

        if ($component->hasIndex() === true) {
            $this->setDOMElementAttribute($element, 'index', $component->getIndex());
        }

        if ($component->hasField() === true) {
            $this->setDOMElementAttribute($element, 'field', $component->getField());
        }

        if ($component->hasXmlBase() === true) {
            self::setXmlBase($element, $component->getXmlBase());
        }

        $this->fillElement($element, $component);

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a printedVariable element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A PrintedVariable object.
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element)
    {
        $version = $this->getVersion();

        if (($identifier = $this->getDOMElementAttributeAs($element, 'identifier')) !== null) {
            $component = new PrintedVariable($identifier);

            if (($format = $this->getDOMElementAttributeAs($element, 'format')) !== null) {
                $component->setFormat($format);
            }

            if (Version::compare($version, '2.1.0', '>=') === true && ($powerForm = $this->getDOMElementAttributeAs($element, 'powerForm', 'boolean')) !== null) {
                $component->setPowerForm($powerForm);
            }

            if (($base = $this->getDOMElementAttributeAs($element, 'base')) !== null) {
                $component->setBase((Format::isInteger($base) === true) ? (int)$base : $base);
            }

            if (($index = $this->getDOMElementAttributeAs($element, 'index')) !== null) {
                $component->setIndex((Format::isInteger($index) === true) ? (int)$index : $base);
            }

            if (Version::compare($version, '2.1.0', '>=') === true && ($delimiter = $this->getDOMElementAttributeAs($element, 'delimiter')) !== null) {
                $component->setDelimiter($delimiter);
            }

            if (($field = $this->getDOMElementAttributeAs($element, 'field')) !== null) {
                $component->setField($field);
            }

            if (Version::compare($version, '2.1.0', '>=') === true && ($mappingIndicator = $this->getDOMElementAttributeAs($element, 'mappingIndicator')) !== null) {
                $component->setMappingIndicator($mappingIndicator);
            }

            if (($xmlBase = self::getXmlBase($element)) !== false) {
                $component->setXmlBase($xmlBase);
            }

            $this->fillBodyElement($component, $element);

            return $component;
        } else {
            $msg = "The mandatory 'identifier' attribute is missing from the 'printedVariable' element.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    public function getExpectedQtiClassName()
    {
        return 'printedVariable';
    }
}
