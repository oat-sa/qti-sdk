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
use qtism\common\enums\BaseType;
use qtism\data\QtiComponent;
use qtism\data\state\InterpolationTableEntry;
use qtism\data\storage\Utils;

/**
 * Marshalling/Unmarshalling implementation for InterpolationTableEntry.
 */
class InterpolationTableEntryMarshaller extends Marshaller
{
    private $baseType = -1;

    /**
     * Get the baseType of the variableDeclaration that contains
     * the interpolationTableEntry to marshall.
     *
     * @return int A value from the BaseType enumeration.
     */
    public function getBaseType()
    {
        return $this->baseType;
    }

    /**
     * Set the base type of the variableDeclaration that contains
     * the interpolationTableEntry to marshall.
     *
     * @param int $baseType A value from the BaseType enumeration.
     * @throws InvalidArgumentException If $baseType is not a value from the BaseType enumeration nor -1.
     */
    public function setBaseType($baseType = -1)
    {
        if (in_array($baseType, BaseType::asArray()) || $baseType == -1) {
            $this->baseType = $baseType;
        } else {
            $msg = 'The baseType attribute must be a value from the BaseType enumeration.';
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Create a new instance of InterpolationTableEntryMarshaller.
     *
     * @param string $version The QTI version on which the Marshaller operates e.g. '2.1'.
     * @param int $baseType The baseType of the variableDeclaration containing the InterpolationTableEntry to unmarshall.
     */
    public function __construct($version, $baseType = -1)
    {
        parent::__construct($version);
        $this->setBaseType($baseType);
    }

    /**
     * Marshall an InterpolationTableEntry object into a DOMElement object.
     *
     * @param QtiComponent $component An InterpolationTableEntry object.
     * @return DOMElement The according DOMElement object.
     */
    protected function marshall(QtiComponent $component)
    {
        $element = $this->createElement($component);

        $this->setDOMElementAttribute($element, 'sourceValue', $component->getSourceValue());
        $this->setDOMElementAttribute($element, 'targetValue', $component->getTargetValue());
        $this->setDOMElementAttribute($element, 'includeBoundary', $component->doesIncludeBoundary());

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI InterpolationEntry element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent An InterpolationTableEntry object.
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element)
    {
        if (($sourceValue = $this->getDOMElementAttributeAs($element, 'sourceValue', 'float')) !== null) {
            if (($targetValue = $this->getDOMElementAttributeAs($element, 'targetValue', 'string')) !== null) {
                $object = new InterpolationTableEntry($sourceValue, Utils::stringToDatatype($targetValue, $this->getBaseType()));

                if (($includeBoundary = $this->getDOMElementAttributeAs($element, 'includeBoundary', 'boolean')) !== null) {
                    $object->setIncludeBoundary($includeBoundary);
                }

                return $object;
            }
        } else {
            $msg = "The mandatory attribute 'sourceValue' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName()
    {
        return 'interpolationTableEntry';
    }
}
