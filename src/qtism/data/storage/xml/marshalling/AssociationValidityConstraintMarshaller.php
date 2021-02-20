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
use qtism\data\QtiComponent;
use qtism\data\state\AssociationValidityConstraint;

/**
 * Marshalling implementation for AssociationValidityMarshaller extended QTI class.
 */
class AssociationValidityConstraintMarshaller extends Marshaller
{
    /**
     * Marshall an AssociationValidityConstraint object to its XML counterpart.
     *
     * @param QtiComponent $component
     * @return DOMElement
     */
    public function marshall(QtiComponent $component)
    {
        $element = $this->createElement($component);
        $this->setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
        $this->setDOMElementAttribute($element, 'minConstraint', $component->getMinConstraint());
        $this->setDOMElementAttribute($element, 'maxConstraint', $component->getMaxConstraint());

        return $element;
    }

    /**
     * Unmarshall a DOMElement to its AssociationValidityConstraint data model representation.
     *
     * @param DOMElement $element
     * @return QtiComponent An AssociationValidityConstraint object.
     * @throws UnmarshallingException
     */
    public function unmarshall(DOMElement $element)
    {
        if (($identifier = $this->getDOMElementAttributeAs($element, 'identifier')) !== null) {
            if (($minConstraint = $this->getDOMElementAttributeAs($element, 'minConstraint', 'integer')) !== null) {
                if (($maxConstraint = $this->getDOMElementAttributeAs($element, 'maxConstraint', 'integer')) !== null) {
                    try {
                        return new AssociationValidityConstraint($identifier, $minConstraint, $maxConstraint);
                    } catch (InvalidArgumentException $e) {
                        throw new UnmarshallingException(
                            "An error occurred while unmarshalling an 'associationValidityConstraint' element. See chained exceptions for more information.",
                            $element,
                            $e
                        );
                    }
                } else {
                    throw new UnmarshallingException(
                        "The mandatory 'maxConstraint' attribute is missing from element 'associationValididtyConstraint'.",
                        $element
                    );
                }
            } else {
                throw new UnmarshallingException(
                    "The mandatory 'minConstraint' attribute is missing from element 'associationValididtyConstraint'.",
                    $element
                );
            }
        } else {
            throw new UnmarshallingException(
                "The mandatory 'identifier' attribute is missing from element 'associationValididtyConstraint'.",
                $element
            );
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName()
    {
        return 'associationValidityConstraint';
    }
}
