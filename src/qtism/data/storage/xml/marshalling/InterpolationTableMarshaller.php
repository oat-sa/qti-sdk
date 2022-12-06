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
use qtism\data\state\InterpolationTable;
use qtism\data\state\InterpolationTableEntryCollection;
use qtism\data\storage\Utils;
use UnexpectedValueException;

/**
 * Marshalling/Unmarshalling implementation for interpolationTable.
 *
 * This marshaller is parametric and thus need to be construct with
 * the baseType of the variableDeclaration where the interpolationTable
 * to marshall is contained.
 */
class InterpolationTableMarshaller extends Marshaller
{
    private $baseType = -1;

    /**
     * Create a new instance of InterpolationTableMarshaller. Because the InterpolationTableMarshaller
     * needs to know the baseType of the variableDeclaration that contains the interpolationTable,
     * a $baseType can be passed as an argument for instantiation.
     *
     * @param string $version The QTI version number on which the Marshaller operates e.g. '2.1'.
     * @param int $baseType A value from the BaseType enumeration or -1.
     * @throws InvalidArgumentException If $baseType is not a value from the BaseType enumeration nor -1.
     */
    public function __construct($version, $baseType = -1)
    {
        parent::__construct($version);
        $this->setBaseType($baseType);
    }

    /**
     * Set the baseType of the variableDeclaration where the interpolationTable
     * to marshall is contained. Set to -1 if no baseType found for the related
     * variableDeclaration.
     *
     * @param int $baseType A value from the BaseType enumeration.
     * @throws InvalidArgumentException If $baseType is not a value from the BaseType enumeration nor -1.
     */
    public function setBaseType($baseType): void
    {
        if (in_array($baseType, BaseType::asArray(), true) || $baseType === -1) {
            $this->baseType = $baseType;
        } else {
            $msg = 'The BaseType attribute must be a value from the BaseType enumeration.';
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the baseType of the variableDeclaration where the interpolationTable
     * to marshall is contained. It returns -1 if no baseType found for the related
     * variableDeclaration.
     *
     * @return int A value from the BaseType enumeration or -1 if no baseType found for the related variableDeclaration.
     */
    public function getBaseType(): int
    {
        return $this->baseType;
    }

    /**
     * Marshall an InterpolationTable object into a DOMElement object.
     *
     * @param QtiComponent $component An InterpolationTable object.
     * @return DOMElement The according DOMElement object.
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        $element = $this->createElement($component);
        foreach ($component->getInterpolationTableEntries() as $interpolationTableEntry) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($interpolationTableEntry, [$this->getBaseType()]);
            $element->appendChild($marshaller->marshall($interpolationTableEntry));
        }

        if ($component->getDefaultValue() !== null) {
            $this->setDOMElementAttribute($element, 'defaultValue', $component->getDefaultValue());
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI InterpolationTable element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent An InterpolationTable object.
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException If $element does not contain any interpolationTableEntry QTI elements.
     */
    #[\ReturnTypeWillChange]
    protected function unmarshall(DOMElement $element): InterpolationTable
    {
        $interpolationTableEntryElements = $element->getElementsByTagName('interpolationTableEntry');

        if ($interpolationTableEntryElements->length > 0) {
            $interpolationTableEntryCollection = new InterpolationTableEntryCollection();
            for ($i = 0; $i < $interpolationTableEntryElements->length; $i++) {
                $marshaller = $this->getMarshallerFactory()->createMarshaller($interpolationTableEntryElements->item($i), [$this->getBaseType()]);
                $interpolationTableEntryCollection[] = $marshaller->unmarshall($interpolationTableEntryElements->item($i));
            }

            $object = new InterpolationTable($interpolationTableEntryCollection);

            if (($defaultValue = $this->getDOMElementAttributeAs($element, 'defaultValue')) !== null) {
                try {
                    $object->setDefaultValue(Utils::stringToDatatype($defaultValue, $this->getBaseType()));
                } catch (UnexpectedValueException $e) {
                    $msg = "Unable to transform '${defaultValue}' into float.";
                    throw new UnmarshallingException($msg, $element, $e);
                }
            }

            return $object;
        } else {
            $msg = "An 'interpolationTable' element must contain at least one 'interpolationTableEntry' element.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return 'interpolationTable';
    }
}
