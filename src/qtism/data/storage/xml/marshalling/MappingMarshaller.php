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
use qtism\data\state\MapEntryCollection;
use qtism\data\state\Mapping;

/**
 * Marshalling/Unmarshalling implementation for mapping.
 *
 * This marshaller is a parametric one. It allows you to know
 * the baseType of the 'mapKey' attribute of mapEntry sub-elements
 * while unmarshalling it. The value of the given baseType is found
 * in the related responseDeclaration element.
 */
class MappingMarshaller extends Marshaller
{
    private $baseType;

    /**
     * Set a baseType to this marshaller implementation in order
     * to force the datatype used for the unserialization of the
     * 'mapKey' attribute of mapEntry sub-elements.
     *
     * @param int $baseType A baseType from the BaseType enumeration.
     * @throws InvalidArgumentException If $baseType is not a value from the BaseType enumeration.
     */
    protected function setBaseType($baseType)
    {
        if (in_array($baseType, BaseType::asArray())) {
            $this->baseType = $baseType;
        } else {
            $msg = 'The baseType argument must be a valid QTI baseType value from the BaseType enumeration.';
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the baseType that is used to force the unserialization of
     * the 'mapKey' attribute.
     *
     * @return int A baseType from the BaseType enumeration.
     */
    public function getBaseType()
    {
        return $this->baseType;
    }

    /**
     * Create a new instance of MappingMarshaller.
     *
     * @param string $version The QTI version number on which the Marshaller has to operate e.g. '2.1'.
     * @param int $baseType A value from the BaseType enumeration.
     * @throws InvalidArgumentException if $baseType is not a value from the BaseType enumeration.
     */
    public function __construct($version, $baseType)
    {
        parent::__construct($version);
        $this->setBaseType($baseType);
    }

    /**
     * Marshall a Mapping object into a DOMElement object.
     *
     * @param QtiComponent $component A Mapping object.
     * @return DOMElement The according DOMElement object.
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component)
    {
        $element = $this->createElement($component);

        if ($component->hasLowerBound() === true) {
            $this->setDOMElementAttribute($element, 'lowerBound', $component->getLowerBound());
        }

        if ($component->hasUpperBound() === true) {
            $this->setDOMElementAttribute($element, 'upperBound', $component->getUpperBound());
        }

        $this->setDOMElementAttribute($element, 'defaultValue', $component->getDefaultValue());

        foreach ($component->getMapEntries() as $mapEntry) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($mapEntry, [$this->getBaseType()]);
            $element->appendChild($marshaller->marshall($mapEntry));
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI mapping element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A Mapping object.
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element)
    {
        $mapEntriesElts = $this->getChildElementsByTagName($element, 'mapEntry');
        $mapEntries = new MapEntryCollection();

        foreach ($mapEntriesElts as $mapEntryElt) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($mapEntryElt, [$this->getBaseType()]);
            $mapEntries[] = $marshaller->unmarshall($mapEntryElt);
        }

        try {
            $object = new Mapping($mapEntries);

            if (($defaultValue = $this->getDOMElementAttributeAs($element, 'defaultValue', 'float')) !== null) {
                $object->setDefaultValue($defaultValue);
            }

            if (($lowerBound = $this->getDOMElementAttributeAs($element, 'lowerBound', 'float')) !== null) {
                $object->setLowerBound($lowerBound);
            }

            if (($upperBound = $this->getDOMElementAttributeAs($element, 'upperBound', 'float')) !== null) {
                $object->setUpperBound($upperBound);
            }

            return $object;
        } catch (InvalidArgumentException $e) {
            $msg = "A 'mapping' element must contain at least one 'mapEntry' element. None found";
            throw new UnmarshallingException($msg, $element, $e);
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName()
    {
        return 'mapping';
    }
}
