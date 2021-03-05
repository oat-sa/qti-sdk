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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Julien Sébire <julien@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\content\xhtml\html5;

use qtism\data\content\enums\CrossOrigin;
use qtism\data\content\enums\Preload;
use qtism\data\QtiComponentCollection;

/**
 * All the common features of Html 5 media (audio and video).
 */
abstract class Html5Media extends Html5Element
{
    /**
     * Contains the collection of sources.
     *
     * @var SourceCollection
     * @qtism-bean-property
     */
    private $sources;

    /**
     * Contains the collection of tracks.
     *
     * @var TrackCollection
     * @qtism-bean-property
     */
    private $tracks;

    /**
     * The 'autoplay' characteristic is a boolean. When present, the user agent
     * (as described in the algorithm described herein) will automatically
     * begin playback of the media resource as soon as it can do so without
     * stopping.
     *
     * @var bool
     * @qtism-bean-property
     */
    private $autoPlay = false;

    /**
     * The 'controls' characteristic is a boolean. If present, it indicates
     * that the author has not provided a scripted controller and would like
     * the user agent to provide its own set of controls.
     *
     * @var bool
     * @qtism-bean-property
     */
    private $controls = false;

    /**
     * The crossorigin content characteristic on media tags is a CORS settings
     * attribute.
     *
     * @var int|null
     * @qtism-bean-property
     */
    private $crossOrigin;

    /**
     * The 'loop' characteristic is a boolean that, if specified, indicates
     * that the media tag is to seek back to the start of the media resource
     * upon reaching the end.
     *
     * @var bool
     * @qtism-bean-property
     */
    private $loop = false;

    /**
     * The 'mediagroup' content characteristic on media elements can be used to
     * link multiple media tags together by implicitly creating a
     * MediaController. The value is text; media tags with the same value are
     * automatically linked by the user agent.
     *
     * @var string
     * @qtism-bean-property
     */
    private $mediaGroup = '';

    /**
     * The 'muted' characteristic on media tags is a boolean that controls the
     * default state of the audio output of the media resource, potentially
     * overriding user preferences. When a media tag is created, if it has a
     * muted characteristic specified, the user agent must mute the media tag's
     * audio output, overriding any user preference.
     *
     * @var bool
     * @qtism-bean-property
     */
    private $muted = false;

    /**
     * The preload characteristic is an enumerated value. The attribute can be
     * changed even once the media resource is being buffered or played; the
     * descriptions in the table below are to be interpreted with that in mind.
     * Preload type. Defaults to "metadata".
     *
     * @var int
     * @qtism-bean-property
     */
    private $preload;

    /**
     * The 'src' content characteristic on media tags gives the address of the
     * media resource (video, audio) to show. The attribute, if present, must
     * contain a valid non-empty URL potentially surrounded by spaces.
     *
     * @var string
     * @qtism-bean-property
     */
    private $src = '';

    /**
     * Create a new Media object (Audio or Video).
     *
     * @param bool $autoPlay
     * @param bool $controls
     * @param int $crossOrigin
     * @param bool $loop
     * @param string $mediaGroup
     * @param bool $muted
     * @param int $preload
     * @param string $src
     * @param string $id A QTI identifier.
     * @param string $class One or more class names separated by spaces.
     * @param string $lang An RFC3066 language.
     * @param string $label A label that does not exceed 256 characters.
     * @param string $title A title in the sense of Html title attribute
     * @param int|null $role A role taken in the Role constants.
     */
    public function __construct(
        $autoPlay = null,
        $controls = null,
        $crossOrigin = null,
        $loop = null,
        $mediaGroup = null,
        $muted = null,
        $preload = null,
        $src = null,
        $title = null,
        $role = null,
        $id = null,
        $class = null,
        $lang = null,
        $label = null
    ) {
        parent::__construct($title, $role, $id, $class, $lang, $label);
        $this->setAutoPlay($autoPlay);
        $this->setControls($controls);
        $this->setCrossOrigin($crossOrigin);
        $this->setLoop($loop);
        $this->setMediaGroup($mediaGroup);
        $this->setMuted($muted);
        $this->setPreload($preload);
        $this->setSrc($src);
        $this->sources = new SourceCollection();
        $this->tracks = new TrackCollection();
    }

    public function getComponents(): QtiComponentCollection
    {
        $comp = array_merge(
            $this->sources->getArrayCopy(),
            $this->tracks->getArrayCopy()
        );

        return new QtiComponentCollection($comp);
    }

    public function addSource(Source $source): void
    {
        $this->sources->attach($source);
    }

    public function getSources(): SourceCollection
    {
        return $this->sources;
    }

    public function addTrack(Track $track): void
    {
        $this->tracks->attach($track);
    }

    public function getTracks(): TrackCollection
    {
        return $this->tracks;
    }

    public function setAutoPlay($autoPlay): void
    {
        $this->autoPlay = $this->acceptBooleanOrNull($autoPlay, 'autoplay', false);
    }

    public function getAutoPlay(): bool
    {
        return $this->autoPlay;
    }

    public function hasAutoPlay(): bool
    {
        return $this->autoPlay !== false;
    }

    public function setControls($controls): void
    {
        $this->controls = $this->acceptBooleanOrNull($controls, 'controls', false);
    }

    public function getControls(): bool
    {
        return $this->controls;
    }

    public function hasControls(): bool
    {
        return $this->controls !== false;
    }

    public function setCrossOrigin($crossOrigin = null): void
    {
        $this->crossOrigin = CrossOrigin::accept($crossOrigin, 'crossorigin');
    }

    public function getCrossOrigin(): ?int
    {
        return $this->crossOrigin;
    }

    public function hasCrossOrigin(): bool
    {
        return $this->crossOrigin !== CrossOrigin::getDefault();
    }

    public function setLoop($loop): void
    {
        $this->loop = $this->acceptBooleanOrNull($loop, 'loop', false);
    }

    public function getLoop(): bool
    {
        return $this->loop;
    }

    public function hasLoop(): bool
    {
        return $this->loop !== false;
    }

    public function setMediaGroup($mediaGroup): void
    {
        $this->mediaGroup = $this->acceptNormalizedStringOrNull($mediaGroup, 'src', '');
    }

    public function getMediaGroup(): string
    {
        return $this->mediaGroup;
    }

    public function hasMediaGroup(): bool
    {
        return $this->mediaGroup !== '';
    }

    public function setMuted($muted): void
    {
        $this->muted = $this->acceptBooleanOrNull($muted, 'muted', false);
    }

    public function getMuted(): bool
    {
        return $this->muted;
    }

    public function hasMuted(): bool
    {
        return $this->muted !== false;
    }

    public function setPreload($preload = null): void
    {
        $this->preload = Preload::accept($preload, 'preload');
    }

    public function getPreload(): int
    {
        return $this->preload;
    }

    public function hasPreload(): bool
    {
        return $this->preload !== Preload::getDefault();
    }

    public function setSrc($src): void
    {
        $this->src = $this->acceptUriOrNull($src, 'src');
    }

    public function getSrc(): string
    {
        return $this->src;
    }

    public function hasSrc(): bool
    {
        return $this->src !== '';
    }
}
