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
use qtism\data\state\MatchTableEntry;
use qtism\data\storage\Utils;

/**
 * Marshalling/Unmarshalling implementation for matchTableEntry.
 *
 * This is a specific case of parametric marshaller. Indeed, it needs to known
 * what is the baseType of the targetValue, which is not defined in the matchTableEntry
 * element itself but in the baseType attribute of the parent variableDeclaration.
 */
class MatchTableEntryMarshaller extends Marshaller
{
    /**
     * The baseType of the expected targetValue.
     *
     * @var int
     */
    private $baseType;

    /**
     * Get the baseType of the expected targetValue.
     *
     * @return int A value from the BaseType enumeration.
     */
    protected function getBaseType()
    {
        return $this->baseType;
    }

    /**
     * Set the base type of the expected targetValue.
     *
     * @param int $baseType A value from the BaseType enumeration.
     * @throws InvalidArgumentException If $baseType is not a value from the BaseType enumeration.
     */
    protected function setBaseType($baseType)
    {
        if (in_array($baseType, BaseType::asArray())) {
            $this->baseType = $baseType;
        } else {
            $msg = 'BaseType must be a value from the BaseType enumeration.';
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Create a new instance of MatchTableEntryMarshaller. This marshaller
     * has a specific constructor because its parameteric. It needs to know
     * the baseType of its targetValue, which is defined by its parent variableDeclaration.
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
     * Marshall a MatchTableEntry object into a DOMElement object.
     *
     * @param QtiComponent $component A MatchTableEntry object.
     * @return DOMElement The according DOMElement object.
     */
    protected function marshall(QtiComponent $component)
    {
        $element = $this->createElement($component);

        $this->setDOMElementAttribute($element, 'sourceValue', $component->getSourceValue());
        $this->setDOMElementAttribute($element, 'targetValue', $component->getTargetValue());

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI MatchTableEntry element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A MatchTableEntry object.
     * @throws UnmarshallingException If the mandatory attributes 'sourceValue' or 'targetValue' are missing from $element.
     */
    protected function unmarshall(DOMElement $element)
    {
        if (($sourceValue = $this->getDOMElementAttributeAs($element, 'sourceValue', 'integer')) !== null) {
            if (($targetValue = $this->getDOMElementAttributeAs($element, 'targetValue', 'string')) !== null) {
                return new MatchTableEntry($sourceValue, Utils::stringToDatatype($targetValue, $this->getBaseType()), $this->getBaseType());
            } else {
                $msg = "The mandatory attribute 'targetValue' is missing.";
                throw new InvalidArgumentException($msg, $element);
            }
        } else {
            $msg = "The mandatory attribute 'sourceValue' is missing.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName()
    {
        return 'matchTableEntry';
    }
}
