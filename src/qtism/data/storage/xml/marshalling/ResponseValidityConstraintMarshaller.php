<?php

declare(strict_types=1);

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
use qtism\data\state\ResponseValidityConstraint;

/**
 * Marshalling implementation for ResponseValididtyMarshaller extended QTI class.
 */
class ResponseValidityConstraintMarshaller extends Marshaller
{
    /**
     * Marshall a ResponseValidityConstraint object to its XML counterpart.
     *
     * @param QtiComponent $component
     * @return DOMElement
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function marshall(QtiComponent $component): DOMElement
    {
        $element = $this->createElement($component);
        $this->setDOMElementAttribute($element, 'responseIdentifier', $component->getResponseIdentifier());
        $this->setDOMElementAttribute($element, 'minConstraint', $component->getMinConstraint());
        $this->setDOMElementAttribute($element, 'maxConstraint', $component->getMaxConstraint());

        if (($patternMask = $component->getPatternMask()) !== '') {
            $this->setDOMElementAttribute($element, 'patternMask', $patternMask);
        }

        foreach ($component->getAssociationValidityConstraints() as $associationValidityConstraint) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($associationValidityConstraint);
            $element->appendChild($marshaller->marshall($associationValidityConstraint));
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement to its ResponseValidityConstraint data model representation.
     *
     * @param DOMElement $element
     * @return QtiComponent A ResponseValidityConstraint object.
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     */
    public function unmarshall(DOMElement $element): QtiComponent
    {
        if (($responseIdentifier = $this->getDOMElementAttributeAs($element, 'responseIdentifier')) !== null) {
            if (($minConstraint = $this->getDOMElementAttributeAs($element, 'minConstraint', 'integer')) !== null) {
                if (($maxConstraint = $this->getDOMElementAttributeAs($element, 'maxConstraint', 'integer')) !== null) {
                    if (($patternMask = $this->getDOMElementAttributeAs($element, 'patternMask')) === null) {
                        $patternMask = '';
                    }
                    try {
                        $component = new ResponseValidityConstraint($responseIdentifier, $minConstraint, $maxConstraint, $patternMask);

                        // Find child associationValidityConstraint elements if any.
                        $associationValidityConstraintElts = $this->getChildElementsByTagName($element, 'associationValidityConstraint');

                        foreach ($associationValidityConstraintElts as $associationValidityConstraintElt) {
                            $marshaller = $this->getMarshallerFactory()->createMarshaller($associationValidityConstraintElt);
                            $component->addAssociationValidityConstraint($marshaller->unmarshall($associationValidityConstraintElt));
                        }

                        return $component;
                    } catch (InvalidArgumentException $e) {
                        throw new UnmarshallingException(
                            "An error occurred while unmarshalling a 'responseValidityConstraint'. See chained exceptions for more information.",
                            $element,
                            $e
                        );
                    }
                } else {
                    throw new UnmarshallingException(
                        "The mandatory 'maxConstraint' attribute is missing from element 'responseValididtyConstraint'.",
                        $element
                    );
                }
            } else {
                throw new UnmarshallingException(
                    "The mandatory 'minConstraint' attribute is missing from element 'responseValididtyConstraint'.",
                    $element
                );
            }
        } else {
            throw new UnmarshallingException(
                "The mandatory 'responseIdentifier' attribute is missing from element 'responseValididtyConstraint'.",
                $element
            );
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return 'responseValidityConstraint';
    }
}
