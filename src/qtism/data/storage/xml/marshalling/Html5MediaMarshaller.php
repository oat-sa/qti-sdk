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
use qtism\common\utils\Version;
use qtism\data\content\BodyElement;
use qtism\data\content\enums\CrossOrigin;
use qtism\data\content\enums\Preload;
use qtism\data\content\xhtml\html5\Html5Media;
use qtism\data\QtiComponent;

/**
 * Marshalling/Unmarshalling implementation for generic Html5.
 */
abstract class Html5MediaMarshaller extends Html5ElementMarshaller
{
    /**
     * Fill $element with the attributes of $bodyElement.
     *
     * @param QtiComponent $component
     * @return DOMElement The according DOMElement object.
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        $element = parent::marshall($component);

        /** @var Html5Media $component */

        if ($component->hasAutoPlay()) {
            $this->setDOMElementAttribute($element, 'autoplay', $component->getAutoPlay() ? 'true' : 'false');
        }

        if ($component->hasControls()) {
            $this->setDOMElementAttribute($element, 'controls', $component->getControls() ? 'true' : 'false');
        }

        if ($component->hasCrossOrigin()) {
            $this->setDOMElementAttribute($element, 'crossorigin', CrossOrigin::getNameByConstant($component->getCrossOrigin()));
        }

        if ($component->hasLoop()) {
            $this->setDOMElementAttribute($element, 'loop', $component->getLoop() ? 'true' : 'false');
        }

        if ($component->hasMediaGroup()) {
            $this->setDOMElementAttribute($element, 'mediagroup', $component->getMediaGroup());
        }

        if ($component->hasMuted()) {
            $this->setDOMElementAttribute($element, 'muted', $component->getMuted() ? 'true' : 'false');
        }

        if ($component->hasPreload()) {
            $this->setDOMElementAttribute($element, 'preload', Preload::getNameByConstant($component->getPreload()));
        }

        if ($component->hasSrc()) {
            $this->setDOMElementAttribute($element, 'src', $component->getSrc());
        }

        foreach ($component->getSources() as $source) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($source);
            $element->appendChild($marshaller->marshall($source));
        }

        foreach ($component->getTracks() as $track) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($track);
            $element->appendChild($marshaller->marshall($track));
        }
        
        return $element;
    }

    /**
     * Fill $bodyElement with the following Html 5 element attributes and children:
     *
     * * autoplay
     * * controls
     * * crossorigin
     * * loop
     * * mediagroup
     * * muted
     * * preload
     * * src
     * * source
     * * track
     *
     * @param BodyElement $bodyElement The bodyElement to fill.
     * @param DOMElement $element The DOMElement object from where the attribute values must be retrieved.
     * @throws UnmarshallingException If one of the attributes of $element is not valid.
     * @throws MarshallerNotFoundException when source or track can't be unmarshalled.
     */
    protected function fillBodyElement(BodyElement $bodyElement, DOMElement $element): void
    {
        if (Version::compare($this->getVersion(), '2.2.0', '>=') === true) {
            /** @var Html5Media $bodyElement */
            $autoplay = $this->getDOMElementAttributeAs($element, 'autoplay', 'boolean');
            $bodyElement->setAutoplay($autoplay);

            $controls = $this->getDOMElementAttributeAs($element, 'controls', 'boolean');
            $bodyElement->setControls($controls);

            $crossOrigin = $this->getDOMElementAttributeAs($element, 'crossorigin', CrossOrigin::class);
            $bodyElement->setCrossOrigin($crossOrigin);

            $loop = $this->getDOMElementAttributeAs($element, 'loop', 'boolean');
            $bodyElement->setLoop($loop);

            $mediaGroup = $this->getDOMElementAttributeAs($element, 'mediagroup');
            $bodyElement->setMediaGroup($mediaGroup);

            $muted = $this->getDOMElementAttributeAs($element, 'muted', 'boolean');
            $bodyElement->setMuted($muted);

            $preload = $this->getDOMElementAttributeAs($element, 'preload', Preload::class);
            $bodyElement->setPreload($preload);

            $src = $this->getDOMElementAttributeAs($element, 'src');
            $bodyElement->setSrc($src);

            $sourceElements = $this->getChildElementsByTagName($element, 'source');
            foreach ($sourceElements as $sourceElement) {
                $marshaller = $this->getMarshallerFactory()->createMarshaller($sourceElement);
                $bodyElement->addSource($marshaller->unmarshall($sourceElement));
            }

            $trackElements = $this->getChildElementsByTagName($element, 'track');
            foreach ($trackElements as $trackElement) {
                $marshaller = $this->getMarshallerFactory()->createMarshaller($trackElement);
                $bodyElement->addTrack($marshaller->unmarshall($trackElement));
            }
        }

        parent::fillBodyElement($bodyElement, $element);
    }
}
