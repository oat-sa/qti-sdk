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
use qtism\data\content\interactions\MediaInteraction;
use qtism\data\content\xhtml\ObjectElement;
use qtism\data\QtiComponent;

/**
 * Marshalling/Unmarshalling implementation for MediaInteraction.
 */
class MediaInteractionMarshaller extends Marshaller
{
    /**
     * Marshall a MediaInteraction object into a DOMElement object.
     *
     * @param QtiComponent $component A MediaInteraction object.
     * @return DOMElement The according DOMElement object.
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component)
    {
        /** @var MediaInteraction $component */
        
        $element = $this->createElement($component);
        $this->fillElement($element, $component);
        $this->setDOMElementAttribute($element, 'responseIdentifier', $component->getResponseIdentifier());
        $this->setDOMElementAttribute($element, 'autostart', $component->mustAutostart());

        if ($component->hasPrompt() === true) {
            $element->appendChild($this->getMarshallerFactory()->createMarshaller($component->getPrompt())->marshall($component->getPrompt()));
        }

        $media = $component->getMedia();

        /** @var DOMElement $marshalledMedia */
        $marshalledMedia = $this->getMarshallerFactory()->createMarshaller($media)->marshall($media);

        $element->appendChild($marshalledMedia);

        if ($component->getMinPlays() !== 0) {
            $this->setDOMElementAttribute($element, 'minPlays', $component->getMinPlays());
        }

        if ($component->getMaxPlays() !== 0) {
            $this->setDOMElementAttribute($element, 'maxPlays', $component->getMaxPlays());
        }

        if ($component->mustLoop() === true) {
            $this->setDOMElementAttribute($element, 'loop', true);
        }

        if ($component->hasXmlBase() === true) {
            self::setXmlBase($element, $component->getXmlBase());
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a MediaInteraction element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A MediaInteraction object.
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element)
    {
        if (($responseIdentifier = $this->getDOMElementAttributeAs($element, 'responseIdentifier')) === null) {
            $msg = "The mandatory 'responseIdentifier' attribute is missing from the 'mediaInteraction' element.";
            throw new UnmarshallingException($msg, $element);
        }

        if (($autostart = $this->getDOMElementAttributeAs($element, 'autostart', 'boolean')) === null) {
            $msg = "The mandatory 'autostart' attribute is missing from the 'mediaInteraction' element.";
            throw new UnmarshallingException($msg, $element);
        }

        $mediaElts = $this->getChildElementsByTagName($element, ['object', 'video', 'audio']);
        if (count($mediaElts) !== 1) {
            $msg = sprintf(
                "A 'mediaInteraction' element must contain exactly one media element ('object', 'video' or 'audio'), %s given.",
                count($mediaElts)
            );
            throw new UnmarshallingException($msg, $element);
        }

        $mediaElt = $mediaElts[0];
        $media = $this->getMarshallerFactory()->createMarshaller($mediaElt)->unmarshall($mediaElt);

        $component = new MediaInteraction($responseIdentifier, $autostart, $media);

        $promptElts = $this->getChildElementsByTagName($element, 'prompt');
        if (count($promptElts) > 0) {
            $promptElt = $promptElts[0];
            $prompt = $this->getMarshallerFactory()->createMarshaller($promptElt)->unmarshall($promptElt);
            $component->setPrompt($prompt);
        }

        if (($minPlays = $this->getDOMElementAttributeAs($element, 'minPlays', 'integer')) !== null) {
            $component->setMinPlays($minPlays);
        }

        if (($maxPlays = $this->getDOMElementAttributeAs($element, 'maxPlays', 'integer')) !== null) {
            $component->setMaxPlays($maxPlays);
        }

        if (($loop = $this->getDOMElementAttributeAs($element, 'loop', 'boolean')) !== null) {
            $component->setLoop($loop);
        }

        if (($xmlBase = self::getXmlBase($element)) !== false) {
            $component->setXmlBase($xmlBase);
        }

        $this->fillBodyElement($component, $element);

        return $component;
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName()
    {
        return 'mediaInteraction';
    }
}
