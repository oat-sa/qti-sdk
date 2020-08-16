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
use qtism\common\datatypes\QtiPoint;
use qtism\common\utils\Format;
use qtism\common\utils\Version;
use qtism\data\content\interactions\PositionObjectInteraction;
use qtism\data\QtiComponent;

/**
 * Marshalling/Unmarshalling implementation for PositionObjectInteraction.
 */
class PositionObjectInteractionMarshaller extends Marshaller
{
    /**
     * Marshall an PositionObjectInteraction object into a DOMElement object.
     *
     * @param QtiComponent $component A PositionObjectInteraction object.
     * @return DOMElement The according DOMElement object.
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component)
    {
        $version = $this->getVersion();
        $element = $this->createElement($component);
        $element->appendChild($this->getMarshallerFactory()->createMarshaller($component->getObject())->marshall($component->getObject()));
        $this->setDOMElementAttribute($element, 'responseIdentifier', $component->getResponseIdentifier());

        if ($component->getMaxChoices() > 0) {
            $this->setDOMElementAttribute($element, 'maxChoices', $component->getMaxChoices());
        }

        if (Version::compare($version, '2.1.0', '>=') === true && $component->hasMinChoices() === true) {
            $this->setDOMElementAttribute($element, 'minChoices', $component->getMinChoices());
        }

        if ($component->hasCenterPoint() === true) {
            $centerPoint = $component->getCenterPoint();
            $this->setDOMElementAttribute($element, 'centerPoint', $centerPoint->getX() . ' ' . $centerPoint->getY());
        }

        $this->fillElement($element, $component);

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to an positionObjectInteraction element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A PositionObjectInteraction object.
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element)
    {
        $version = $this->getVersion();
        if (($responseIdentifier = $this->getDOMElementAttributeAs($element, 'responseIdentifier')) !== null) {
            $objectElts = $this->getChildElementsByTagName($element, 'object');
            if (count($objectElts) > 0) {
                $object = $this->getMarshallerFactory()->createMarshaller($objectElts[0])->unmarshall($objectElts[0]);
                $component = new PositionObjectInteraction($responseIdentifier, $object);

                if (($maxChoices = $this->getDOMElementAttributeAs($element, 'maxChoices', 'integer')) !== null) {
                    $component->setMaxChoices($maxChoices);
                }

                if (Version::compare($version, '2.1.0', '>=') === true && ($minChoices = $this->getDOMElementAttributeAs($element, 'minChoices', 'integer')) !== null) {
                    $component->setMinChoices($minChoices);
                }

                if (($centerPoint = $this->getDOMElementAttributeAs($element, 'centerPoint')) !== null) {
                    $points = explode("\x20", $centerPoint);
                    $pointsCount = count($points);

                    if ($pointsCount === 2) {
                        if (Format::isInteger($points[0]) === true) {
                            if (Format::isInteger($points[1]) === true) {
                                $component->setCenterPoint(new QtiPoint(intval($points[0]), intval($points[1])));
                            } else {
                                $msg = "The 2nd integer of the 'centerPoint' attribute value is not a valid integer for element 'positionObjectInteraction'.";
                                throw new UnmarshallingException($msg, $element);
                            }
                        } else {
                            $msg = "The 1st value of the 'centerPoint' attribute value is not a valid integer for element 'positionObjectInteraction'.";
                            throw new UnmarshallingException($msg, $element);
                        }
                    } else {
                        $msg = "The value of the 'centePoint' attribute of a 'positionObjectInteraction' element must be composed of exactly 2 integer values, ${pointsCount} given.";
                        throw new UnmarshallingException($msg, $element);
                    }
                }

                $this->fillBodyElement($component, $element);

                return $component;
            } else {
                $msg = "A 'positionObjectInteraction' element must contain exactly one 'object' element, none given.";
                throw new UnmarshallingException($msg, $element);
            }
        } else {
            $msg = "The mandatory 'responseIdentifier' attribute is missing from the 'positionObjectInteraction' object.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @see \qtism\data\storage\xml\marshalling\Marshaller::getExpectedQtiClassName()
     */
    public function getExpectedQtiClassName()
    {
        return 'positionObjectInteraction';
    }
}
