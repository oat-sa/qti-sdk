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
use qtism\data\content\xhtml\tables\ColCollection;
use qtism\data\content\xhtml\tables\Colgroup;
use qtism\data\QtiComponent;

/**
 * Marshalling/Unmarshalling implementation for Colgroup.
 */
class ColgroupMarshaller extends Marshaller
{
    /**
     * Marshall a Colgroup object into a DOMElement object.
     *
     * @param QtiComponent $component A Colgroup object.
     * @return DOMElement The according DOMElement object.
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component)
    {
        $element = self::getDOMCradle()->createElement('colgroup');
        $this->setDOMElementAttribute($element, 'span', $component->getSpan());

        foreach ($component->getContent() as $col) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($col);
            $element->appendChild($marshaller->marshall($col));
        }

        $this->fillElement($element, $component);

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to an XHTML colgroup table element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A Colgroup object.
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element)
    {
        $component = new Colgroup();

        if (($span = $this->getDOMElementAttributeAs($element, 'span', 'integer')) !== null) {
            $component->setSpan($span);
        }

        $cols = new ColCollection();
        foreach ($this->getChildElementsByTagName($element, 'col') as $colElt) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($colElt);
            $cols[] = $marshaller->unmarshall($colElt);
        }
        $component->setContent($cols);

        $this->fillBodyElement($component, $element);

        return $component;
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName()
    {
        return 'colgroup';
    }
}
