<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\rendering\markup\xhtml;

use DOMDocumentFragment;
use qtism\data\content\interactions\Media;
use qtism\data\content\interactions\MediaInteraction;
use qtism\data\content\xhtml\html5\Audio;
use qtism\data\content\xhtml\html5\Html5Media;
use qtism\data\content\xhtml\html5\Source;
use qtism\data\content\xhtml\html5\Track;
use qtism\data\content\xhtml\html5\Video;
use qtism\data\content\xhtml\ObjectElement;
use qtism\data\QtiComponent;
use qtism\runtime\rendering\markup\AbstractMarkupRenderingEngine;

/**
 * MediaInteraction renderer. Rendered components will be transformed as
 * 'div' elements with the 'qti-blockInteraction' and 'qti-mediaInteraction' additional CSS classes.
 *
 * * If the object type describes a video media, a <video> tag will be appended to the rendering.
 * * If the object type describes an audio media, an <audio> tag will be appended to the rendering.
 * * If the object type describe an image media, an <img> tag will be appended to the rendering.
 *
 * The following data-X attributes will be rendered:
 *
 * * data-response-identifier = qti:interaction->responseIdentifier
 * * data-autostart = qti:mediaInteraction->autostart
 * * data-min-plays = qti:mediaInteraction->minPlays
 * * data-max-plays = qti:mediaInteraction->maxPlays
 * * data-loop = qti:mediaInteraction->loop
 */
class MediaInteractionRenderer extends InteractionRenderer
{
    /**
     * Create a new MediaInteractionRenderer object.
     *
     * @param AbstractMarkupRenderingEngine|null $renderingEngine
     */
    public function __construct(AbstractMarkupRenderingEngine $renderingEngine = null)
    {
        parent::__construct($renderingEngine);
        $this->transform('div');
    }

    /**
     * @param DOMDocumentFragment $fragment
     * @param QtiComponent $component
     * @param string $base
     */
    protected function appendAttributes(DOMDocumentFragment $fragment, QtiComponent $component, $base = '')
    {
        parent::appendAttributes($fragment, $component, $base);
        $this->additionalClass('qti-blockInteraction');
        $this->additionalClass('qti-mediaInteraction');

        $fragment->firstChild->setAttribute('data-autostart', ($component->mustAutostart() === true) ? 'true' : 'false');
        $fragment->firstChild->setAttribute('data-min-plays', $component->getMinPlays());
        $fragment->firstChild->setAttribute('data-max-plays', $component->getMaxPlays());
        $fragment->firstChild->setAttribute('data-loop', ($component->mustLoop() === true) ? 'true' : 'false');
    }

    /**
     * @param DOMDocumentFragment $fragment
     * @param QtiComponent $component
     * @param string $base
     */
    protected function appendChildren(DOMDocumentFragment $fragment, QtiComponent $component, $base = '')
    {
        /** @var MediaInteraction $component */
        parent::appendChildren($fragment, $component, $base);

        $qtiMedia = $component->getMedia();

        if ($qtiMedia instanceof ObjectElement) {
            if ($qtiMedia->isVideo()) {
                // Transform the object element representing the video.
                $media = $fragment->ownerDocument->createElement('video');
                $source = $fragment->ownerDocument->createElement('source');
                $source->setAttribute('type', $qtiMedia->getType());
                $source->setAttribute('src', $qtiMedia->getData());
                $media->appendChild($source);
            } elseif ($qtiMedia->isAudio()) {
                $media = $fragment->ownerDocument->createElement('audio');
                $source = $fragment->ownerDocument->createElement('source');
                $source->setAttribute('type', $qtiMedia->getType());
                $source->setAttribute('src', $qtiMedia->getData());
                $media->appendChild($source);
            } elseif ($qtiMedia->isImage()) {
                $media = $fragment->ownerDocument->createElement('img');
                $media->setAttribute('src', $qtiMedia->getData());
            }

            if (empty($media) !== true) {
                // Search for the <object> to be replaced.
                $objects = $fragment->firstChild->getElementsByTagName('object');
                $fragment->firstChild->replaceChild($media, $objects->item(0));

                if (!$qtiMedia->isAudio() && $qtiMedia->hasWidth()) {
                    $media->setAttribute('width', $qtiMedia->getWidth());
                }

                if (!$qtiMedia->isAudio() && $qtiMedia->hasHeight()) {
                    $media->setAttribute('height', $qtiMedia->getHeight());
                }
            }
        }
    }
}
