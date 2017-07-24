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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Aleksej Tikhanovich <aleksej@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\marshalling;

use qtism\data\AdaptiveEngineRef;
use qtism\data\QtiComponent;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for AdaptiveEngineRef.
 *
 * @author Aleksej Tikhanovich <aleksej@taotesting.com>
 *
 */
class AdaptiveEngineRefMarshaller extends SectionPartMarshaller
{
    /**
     * Marshall an AdaptiveEngineRef object into a DOMElement object.
     *
     * @param QtiComponent $component An AdaptiveEngineRef object.
     * @return DOMElement The according DOMElement object.
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component)
    {
        $element = parent::marshall($component);

        self::setDOMElementAttribute($element, 'href', $component->getHref());

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI AdaptiveEngineRef element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent An AdaptiveEngineRef object.
     * @throws UnmarshallingException If the mandatory attribute 'href' is missing.
     */
    protected function unmarshall(DOMElement $element)
    {
        $baseComponent = parent::unmarshall($element);

        if (($href = static::getDOMElementAttributeAs($element, 'href')) !== null) {
            $object = new AdaptiveEngineRef($baseComponent->getIdentifier(), $href);
            return $object;
        } else {
            $msg = "The mandatory attribute 'href' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @see \qtism\data\storage\xml\marshalling\Marshaller::getExpectedQtiClassName()
     */
    public function getExpectedQtiClassName()
    {
        return 'adaptiveEngineRef';
    }
}
