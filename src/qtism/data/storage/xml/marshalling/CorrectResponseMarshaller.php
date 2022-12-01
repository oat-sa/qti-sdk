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
use qtism\data\state\CorrectResponse;
use qtism\data\state\ValueCollection;

/**
 * Marshalling/Unmarshalling implementation for correctResponse.
 */
class CorrectResponseMarshaller extends Marshaller
{
    /**
     * The baseType of values.
     *
     * @var int
     */
    private $baseType = -1;

    /**
     * @param int $baseType
     */
    public function setBaseType($baseType = -1): void
    {
        if (in_array($baseType, BaseType::asArray()) || $baseType == -1) {
            $this->baseType = $baseType;
        } else {
            $msg = 'The baseType argument must be a value from the BaseType enumeration.';
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the base type of inner values.
     *
     * @return int
     */
    public function getBaseType(): int
    {
        return $this->baseType;
    }

    /**
     * Create a new CorrectResponseMarshaller object.
     *
     * @param string $version The QTI version number on which the Marshaller operates e.g. '2.1'.
     * @param int $baseType The base type of the Variable referencing this CorrectResponse.
     */
    public function __construct($version, $baseType = -1)
    {
        parent::__construct($version);
        $this->setBaseType($baseType);
    }

    /**
     * Marshall a CorrectResponse object into a DOMElement object.
     *
     * @param QtiComponent $component A CorrectResponse object.
     * @return DOMElement The according DOMElement object.
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        $element = $this->createElement($component);

        $interpretation = $component->getInterpretation();
        if (!empty($interpretation)) {
            $this->setDOMElementAttribute($element, 'interpretation', $interpretation);
        }

        // A CorrectResponse contains 1..* Value objects
        foreach ($component->getValues() as $value) {
            $valueMarshaller = $this->getMarshallerFactory()->createMarshaller($value, [$this->getBaseType()]);
            $valueElement = $valueMarshaller->marshall($value);
            $element->appendChild($valueElement);
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI correctResponse element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A CorrectResponse object.
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException If the DOMElement object cannot be unmarshalled in a valid CorrectResponse object.
     */
    #[\ReturnTypeWillChange]
    protected function unmarshall(DOMElement $element): CorrectResponse
    {
        $interpretation = $this->getDOMElementAttributeAs($element, 'interpretation', 'string');
        $interpretation = (empty($interpretation)) ? '' : $interpretation;

        // Retrieve the values ...
        $values = new ValueCollection();
        $valueElements = $element->getElementsByTagName('value');

        if ($valueElements->length > 0) {
            for ($i = 0; $i < $valueElements->length; $i++) {
                $valueMarshaller = $this->getMarshallerFactory()->createMarshaller($valueElements->item($i), [$this->getBaseType()]);
                $values[] = $valueMarshaller->unmarshall($valueElements->item($i));
            }

            return new CorrectResponse($values, $interpretation);
        } else {
            $msg = "A 'correctResponse' QTI element must contain at least one 'value' QTI element.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return 'correctResponse';
    }
}
