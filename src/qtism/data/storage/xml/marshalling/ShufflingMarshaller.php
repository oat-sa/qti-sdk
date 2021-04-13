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
use qtism\data\QtiComponent;
use qtism\data\state\Shuffling;
use qtism\data\state\ShufflingGroupCollection;

/**
 * Marshalling/Unmarshalling implementation for Shuffling.
 */
class ShufflingMarshaller extends Marshaller
{
    /**
     * Marshall a Shuffling object into a DOMElement object.
     *
     * @param QtiComponent $component A Shuffling object.
     * @return DOMElement The according DOMElement object.
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component)
    {
        $element = $this->createElement($component);
        $this->setDOMElementAttribute($element, 'responseIdentifier', $component->getResponseIdentifier());

        foreach ($component->getShufflingGroups() as $shufflingGroup) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($shufflingGroup);
            $element->appendChild($marshaller->marshall($shufflingGroup));
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI ShufflingGroup element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A ShufflingGroup object.
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element)
    {
        if (($responseIdentifier = $this->getDOMElementAttributeAs($element, 'responseIdentifier')) !== null) {
            $shufflingGroupElts = self::getChildElements($element, 'shufflingGroup');

            if (($c = count($shufflingGroupElts)) === 0) {
                $msg = "A 'shuffling' element must contain at least 1 'shufflingGroup' element. None given.";
                throw new UnmarshallingException($msg, $element);
            } elseif ($c > 2) {
                $msg = "A 'shuffling' element must contain at most 2 'shufflingGroup' elements. ${c} given.";
                throw new UnmarshallingException($msg, $element);
            } else {
                $shufflingGroups = new ShufflingGroupCollection();
                for ($i = 0; $i < $c; $i++) {
                    $marshaller = $this->getMarshallerFactory()->createMarshaller($shufflingGroupElts[$i]);
                    $shufflingGroups[] = $marshaller->unmarshall($shufflingGroupElts[$i]);
                }

                return new Shuffling($responseIdentifier, $shufflingGroups);
            }
        } else {
            $msg = "The mandatory attribute 'responseIdentifier' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName()
    {
        return 'shuffling';
    }
}
