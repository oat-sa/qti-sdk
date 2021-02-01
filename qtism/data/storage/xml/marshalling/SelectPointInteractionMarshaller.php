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
use qtism\data\content\interactions\SelectPointInteraction;
use qtism\data\QtiComponent;

/**
 * Marshalling/Unmarshalling implementation for SelectPointInteraction.
 */
class SelectPointInteractionMarshaller extends Marshaller
{
    /**
     * Marshall a SelectPointInteraction object into a DOMElement object.
     *
     * @param QtiComponent $component A SelectPointInteraction object.
     * @return DOMElement The according DOMElement object.
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component)
    {
        $element = $this->createElement($component);
        $this->fillElement($element, $component);
        $this->setDOMElementAttribute($element, 'responseIdentifier', $component->getResponseIdentifier());
        $this->setDOMElementAttribute($element, 'maxChoices', $component->getMaxChoices());

        if ($component->hasPrompt() === true) {
            $element->appendChild($this->getMarshallerFactory()->createMarshaller($component->getPrompt())->marshall($component->getPrompt()));
        }

        $element->appendChild($this->getMarshallerFactory()->createMarshaller($component->getObject())->marshall($component->getObject()));

        if ($component->getMinChoices() !== 0) {
            $this->setDOMElementAttribute($element, 'minChoices', $component->getMinChoices());
        }

        if ($component->hasXmlBase() === true) {
            self::setXmlBase($element, $component->getXmlBase());
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a selectPointInteraction element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A SelectPointInteraction object.
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element)
    {
        if (($responseIdentifier = $this->getDOMElementAttributeAs($element, 'responseIdentifier')) === null) {
            $msg = "The mandatory 'responseIdentifier' attribute is missing from the 'selectPointInteraction' element.";
            throw new UnmarshallingException($msg, $element);
        }

        $objectElts = $this->getChildElementsByTagName($element, 'object');
        if (count($objectElts) <= 0) {
            $msg = "A 'selectPointInteraction' element must contain exactly one 'object' element, none given.";
            throw new UnmarshallingException($msg, $element);
        }

        $maxChoices = $this->getDOMElementAttributeAs($element, 'maxChoices', 'integer');
        // This has to be fixed since maxChoices is only mandatory in QTI 2.1 and before.
        if ($maxChoices === null) {
            $msg = "The mandatory 'maxChoices' attribute is missing from the 'selectPointInteraction' element.";
            throw new UnmarshallingException($msg, $element);
        }

        $object = $this->getMarshallerFactory()->createMarshaller($objectElts[0])->unmarshall($objectElts[0]);
        $component = new SelectPointInteraction($responseIdentifier, $object, $maxChoices);

        $minChoices = $this->getDOMElementAttributeAs($element, 'minChoices', 'integer');
        if ($minChoices !== null) {
            $component->setMinChoices($minChoices);
        }

        if (($xmlBase = self::getXmlBase($element)) !== false) {
            $component->setXmlBase($xmlBase);
        }

        $promptElts = $this->getChildElementsByTagName($element, 'prompt');
        if (count($promptElts) > 0) {
            $promptElt = $promptElts[0];
            $prompt = $this->getMarshallerFactory()->createMarshaller($promptElt)->unmarshall($promptElt);
            $component->setPrompt($prompt);
        }

        $this->fillBodyElement($component, $element);

        return $component;
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName()
    {
        return 'selectPointInteraction';
    }
}
