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
 * Copyright (c) 2013-2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\state\ResponseValidityConstraint;
use \DOMElement;
use \InvalidArgumentException;

/**
 * Marshalling implementation for ResponseValididtyMarshaller extended QTI class.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ResponseValidityConstraintMarshaller extends Marshaller
{
    /**
     * Marshall a ResponseValidityConstraint object to its XML counterpart.
     *
     * @param \qtism\data\QtiComponent $component
     * @return \DOMElement
     */
    public function marshall(QtiComponent $component)
    {
        $element = self::getDOMCradle()->createElement('responseValidityConstraint');
        self::setDOMElementAttribute($element, 'responseIdentifier', $component->getResponseIdentifier());
        self::setDOMElementAttribute($element, 'minConstraint', $component->getMinConstraint());
        self::setDOMElementAttribute($element, 'maxConstraint', $component->getMaxConstraint());

        return $element;
    }

    /**
     * Unmarshall a DOMElement to its ResponseValidityConstraint data model representation.
     *
     * @param \DOMElement $element
     * @return \qtism\data\QtiComponent A ResponseValidityConstraint object.
     * @throws \qtism\data\storage\xml\marshalling\UnmarshallingException
     */
    public function unmarshall(DOMElement $element)
    {
        if (($responseIdentifier = self::getDOMElementAttributeAs($element, 'responseIdentifier')) !== null) {
            
            if (($minConstraint = self::getDOMElementAttributeAs($element, 'minConstraint', 'integer')) !== null) {
                
                if (($maxConstraint = self::getDOMElementAttributeAs($element, 'maxConstraint', 'integer')) !== null) {
                    
                    try {
                        return new ResponseValidityConstraint($responseIdentifier, $minConstraint, $maxConstraint);
                    } catch (InvalidArgumentException $e) {
                        throw new UnmarshallingException(
                            "An error occured while unmarshalling a 'responseValidityConstraint'. See chained exceptions for more information.",
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
     * @see \qtism\data\storage\xml\marshalling\Marshaller::getExpectedQtiClassName()
     */
    public function getExpectedQtiClassName()
    {
        return 'responseValidityConstraint';
    }
}
