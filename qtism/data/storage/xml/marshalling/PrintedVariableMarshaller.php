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
     */
    protected function marshall(QtiComponent $component)
    {
        $element = self::getDOMCradle()->createElement('printedVariable');
        self::setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
        self::setDOMElementAttribute($element, 'base', $component->getBase());
        self::setDOMElementAttribute($element, 'powerForm', $component->mustPowerForm());
        self::setDOMElementAttribute($element, 'delimiter', $component->getDelimiter());
        self::setDOMElementAttribute($element, 'mappingIndicator', $component->getMappingIndicator());

        if ($component->hasFormat() === true) {
            self::setDOMElementAttribute($element, 'format', $component->getFormat());
        }

        if ($component->hasIndex() === true) {
            self::setDOMElementAttribute($element, 'index', $component->getIndex());
        }

        if ($component->hasField() === true) {
            self::setDOMElementAttribute($element, 'field', $component->getField());
        }

        if ($component->hasXmlBase() === true) {
            self::setXmlBase($element, $component->getXmlBase());
        }

        self::fillElement($element, $component);

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
        if (($identifier = self::getDOMElementAttributeAs($element, 'identifier')) !== null) {
            $component = new PrintedVariable($identifier);

            if (($format = self::getDOMElementAttributeAs($element, 'format')) !== null) {
                $component->setFormat($format);
            }

            if (($powerForm = self::getDOMElementAttributeAs($element, 'powerForm', 'boolean')) !== null) {
                $component->setPowerForm($powerForm);
            }

            if (($base = self::getDOMElementAttributeAs($element, 'base')) !== null) {
                $component->setBase((Format::isInteger($base) === true) ? (int)$base : $base);
            }

            if (($index = self::getDOMElementAttributeAs($element, 'index')) !== null) {
                $component->setIndex((Format::isInteger($index) === true) ? (int)$index : $base);
            }

            if (($delimiter = self::getDOMElementAttributeAs($element, 'delimiter')) !== null) {
                $component->setDelimiter($delimiter);
            }

            if (($field = self::getDOMElementAttributeAs($element, 'field')) !== null) {
                $component->setField($field);
            }

            if (($mappingIndicator = self::getDOMElementAttributeAs($element, 'mappingIndicator'))) {
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

    /**
     * @return string
     */
    public function getExpectedQtiClassName()
    {
        return 'printedVariable';
    }
}
