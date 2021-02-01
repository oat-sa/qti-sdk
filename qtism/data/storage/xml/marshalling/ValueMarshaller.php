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
use qtism\data\state\Value;
use qtism\data\storage\Utils;

/**
 * Marshalling/Unmarshalling implementation for value.
 *
 * This marshaller is a parametric one. You can force the baseType
 * of the value to indicate how to unserialize the intrinsic value of
 * the Value object in case it has not baseType attribute. This happen
 * when the baseType must be derived from the variableDeclaration.
 */
class ValueMarshaller extends Marshaller
{
    private $baseType = -1;

    /**
     * Set a baseType to this marshaller implementation in order
     * to force the datatype used for the unserialization of the intrinsic
     * value of the value. Set to -1 if there is no forced baseType.
     *
     * @param int $baseType A baseType from the BaseType enumeration.
     * @throws InvalidArgumentException If $baseType is not a value from the BaseType enumeration.
     */
    protected function setBaseType($baseType)
    {
        if (in_array($baseType, BaseType::asArray()) || $baseType == -1) {
            $this->baseType = $baseType;
        } else {
            $msg = 'The baseType argument must be a valid QTI baseType value from the BaseType enumeration.';
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the baseType that is used to force the unserialization of the instrinsic
     * value. Returns -1 if there is no forced baseType.
     *
     * @return int A baseType from the BaseType enumeration.
     */
    public function getBaseType()
    {
        return $this->baseType;
    }

    /**
     * Create a new instance of ValueMarshaller.
     *
     * @param string $version The QTI version number on which the Marshaller operates.
     * @param int $baseType A value from the BaseType enumeration.
     * @throws InvalidArgumentException if $baseType is not a value from the BaseType enumeration nor -1.
     */
    public function __construct($version, $baseType = -1)
    {
        parent::__construct($version);
        $this->setBaseType($baseType);
    }

    /**
     * Marshall a Value object into a DOMElement object.
     *
     * @param QtiComponent $component A Value object.
     * @return DOMElement The according DOMElement object.
     */
    protected function marshall(QtiComponent $component)
    {
        $element = $this->createElement($component);

        $fieldIdentifer = $component->getFieldIdentifier();
        $baseType = $component->getBaseType();

        self::setDOMElementValue($element, $component->getValue());

        if (!empty($fieldIdentifer)) {
            $this->setDOMElementAttribute($element, 'fieldIdentifier', $fieldIdentifer);
        }

        if ($component->isPartOfRecord() && $baseType >= 0) {
            $this->setDOMElementAttribute($element, 'baseType', BaseType::getNameByConstant($baseType));
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI Value element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A Value object.
     * @throws UnmarshallingException If the 'baseType' attribute is not a valid QTI baseType.
     */
    protected function unmarshall(DOMElement $element)
    {
        $object = null;

        if (($baseType = $this->getDOMElementAttributeAs($element, 'baseType', 'string')) !== null) {
            // baseType attribute is set -> part of a record.
            $baseTypeCst = BaseType::getConstantByName($baseType);
            if ($baseTypeCst !== false) {
                $object = new Value(Utils::stringToDatatype(trim($element->nodeValue), $baseTypeCst), $baseTypeCst);
                $object->setPartOfRecord(true);
            } else {
                $msg = "The 'baseType' attribute value ('$baseType') is not a valid QTI baseType in element '" . $element->localName . "'.";
                throw new UnmarshallingException($msg, $element);
            }
        } else {
            // baseType attribute not set -> not part of a record.
            $nodeValue = trim($element->nodeValue);

            // Try to use the marshaller as parametric to know how to unserialize the value.
            if ($this->getBaseType() !== -1) {
                // Empty value only accepted if base type is string (consider empty string).
                if ($nodeValue === '' && $this->getBaseType() !== BaseType::STRING) {
                    $msg = sprintf('The element "%s" has no value.', $element->localName);
                    throw new UnmarshallingException($msg, $element);
                }

                $object = new Value(Utils::stringToDatatype($nodeValue, $this->getBaseType()), $this->getBaseType());
            } else {
                // value used as plain string (at your own risks).
                $object = new Value($nodeValue);
            }
        }

        if (($value = $this->getDOMElementAttributeAs($element, 'fieldIdentifier', 'string')) !== null) {
            $object->setFieldIdentifier($value);
        }

        return $object;
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName()
    {
        return 'value';
    }
}
