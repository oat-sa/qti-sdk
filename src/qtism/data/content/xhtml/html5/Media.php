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

use InvalidArgumentException;
use qtism\common\utils\Format;
use qtism\data\content\enums\CrossOrigin;
use qtism\data\content\enums\Preload;
use qtism\data\QtiComponentCollection;

/**
 * All the common features of Html 5 media (audio and video).
 */
abstract class Media extends Html5Element
{
    /**
     * Contains the collection of sources and tracks.
     *
     * @var QtiComponentCollection
     */
    private $components;

    /**
     * Must the media start automatically?
     *
     * @var bool
     */
    private $autoPlay = false;

    /**
     * Do we display controls?
     *
     * @var bool
     */
    private $controls = false;

    /**
     * CORS setting.
     *
     * @var int|null
     */
    private $crossOrigin;

    /**
     * Do we loop?
     *
     * @var bool
     */
    private $loop = false;

    /**
     * Media group
     *
     * @var string
     */
    private $mediaGroup = '';

    /**
     * Is the media muted?
     *
     * @var bool
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
     * Source URI.
     *
     * @var string
     */
    private $src = '';

    /**
     * Create a new BodyElement object.
     *
     * @param string $id A QTI identifier.
     * @param string $class One or more class names separated by spaces.
     * @param string $lang An RFC3066 language.
     * @param string $label A label that does not exceed 256 characters.
     * @param string $title A title in the sense of Html title attribute
     * @param int|null $role A role taken in the Role constants.
     */
    public function __construct(
        $id = '',
        $class = '',
        $lang = '',
        $label = '',
        $title = '',
        $role = null
    ) {
        parent::__construct($id, $class, $lang, $label, $title, $role);
        $this->components = new QtiComponentCollection();
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents(): QtiComponentCollection
    {
        return $this->components;
    }

    /**
     * Adds a source element.
     *
     * @param Source $source
     */
    public function addSource(Source $source)
    {
        $this->components->attach($source);
    }

    /**
     * Adds a track element.
     *
     * @param Track $track
     */
    public function addTrack(Track $track)
    {
        $this->components->attach($track);
    }

    /**
     * @return bool
     */
    public function getAutoPlay(): bool
    {
        return $this->autoPlay;
    }

    /**
     * @param bool $autoPlay
     */
    public function setAutoPlay($autoPlay)
    {
        if (!is_bool($autoPlay)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The "autoplay" argument must be a boolean, "%s" given.',
                    gettype($autoPlay)
                )
            );
        }

        $this->autoPlay = $autoPlay;
    }

    /**
     * @return bool
     */
    public function hasAutoPlay(): bool
    {
        return $this->autoPlay !== false;
    }

    /**
     * @return bool
     */
    public function getControls(): bool
    {
        return $this->controls;
    }

    /**
     * @param bool $controls
     */
    public function setControls($controls)
    {
        if (!is_bool($controls)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The "controls" argument must be a boolean, "%s" given.',
                    gettype($controls)
                )
            );
        }

        $this->controls = $controls;
    }

    /**
     * @return bool
     */
    public function hasControls(): bool
    {
        return $this->controls !== false;
    }

    /**
     * @return int
     */
    public function getCrossOrigin(): int
    {
        return $this->crossOrigin;
    }

    /**
     * @param int $crossOrigin
     */
    public function setCrossOrigin($crossOrigin)
    {
        if (!in_array($crossOrigin, CrossOrigin::asArray(), true)) {
            $given = is_int($crossOrigin)
                ? $crossOrigin
                : gettype($crossOrigin);

            throw new InvalidArgumentException(
                sprintf(
                    'The "crossorigin" argument must be a value from the CrossOrigin enumeration, "%s" given.',
                    $given
                )
            );
        }

        $this->crossOrigin = $crossOrigin;
    }

    /**
     * @return bool
     */
    public function hasCrossOrigin(): bool
    {
        return $this->crossOrigin !== CrossOrigin::ANONYMOUS;
    }

    /**
     * @return bool
     */
    public function getLoop(): bool
    {
        return $this->loop;
    }

    /**
     * @param bool $loop
     */
    public function setLoop($loop)
    {
        if (!is_bool($loop)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The "loop" argument must be a boolean, "%s" given.',
                    gettype($loop)
                )
            );
        }

        $this->loop = $loop;
    }

    /**
     * @return bool
     */
    public function hasLoop(): bool
    {
        return $this->loop !== false;
    }

    /**
     * @return string
     */
    public function getMediaGroup(): string
    {
        return $this->mediaGroup;
    }

    /**
     * @param string $mediaGroup
     */
    public function setMediaGroup($mediaGroup)
    {
        if (!Format::isNormalizedString($mediaGroup)) {
            $given = is_string($mediaGroup)
                ? $mediaGroup
                : gettype($mediaGroup);

            throw new InvalidArgumentException(
                sprintf(
                    'The "src" argument must be a non-empty string, "%s" given.',
                    $given
                )
            );
        }

        $this->mediaGroup = $mediaGroup;
    }

    /**
     * @return bool
     */
    public function hasMediaGroup(): bool
    {
        return $this->mediaGroup !== '';
    }

    /**
     * @return bool
     */
    public function getMuted(): bool
    {
        return $this->muted;
    }

    /**
     * @param bool $muted
     */
    public function setMuted($muted)
    {
        if (!is_bool($muted)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The "muted" argument must be a boolean, "%s" given.',
                    gettype($muted)
                )
            );
        }

        $this->muted = $muted;
    }

    /**
     * @return bool
     */
    public function hasMuted(): bool
    {
        return $this->muted !== false;
    }

    /**
     * Sets the preload type.
     * @param int $preload One of the Preload constants.
     */
    public function setPreload($preload)
    {
        if (!in_array($preload, Preload::asArray(), true)) {
            $given = is_int($preload)
                ? $preload
                : gettype($preload);

            throw new InvalidArgumentException(
                sprintf(
                    'The "preload" argument must be a value from the Preload enumeration, "%s" given.',
                    $given
                )
            );
        }

        $this->preload = $preload;
    }

    public function getPreload(): int
    {
        return $this->preload;
    }

    public function hasPreload(): bool
    {
        return $this->preload !== Preload::METADATA;
    }

    /**
     * @param string $src
     */
    public function setSrc($src)
    {
        if (!Format::isUri($src)) {
            $given = is_string($src)
                ? $src
                : gettype($src);

            throw new InvalidArgumentException(
                sprintf(
                    'The "src" argument must be a valid URI, "%s" given.',
                    $given
                )
            );
        }

        $this->src = $src;
    }

    /**
     * @return bool
     */
    public function hasSrc(): bool
    {
        return $this->src !== '';
    }
}
