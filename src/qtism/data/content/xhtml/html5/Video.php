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

/**
 * Html 5 Video element used for playing videos or movies, and audio files with
 * captions.
 */
class Video extends Html5Media
{
    /**
     * The 'poster' characteristic gives the address of an image file that the
     * user agent can show while no video data is available. The characteristic,
     * if present, must contain a valid non-empty URL potentially surrounded by
     * spaces.
     *
     * @var string
     * @qtism-bean-property
     */
    private $poster = '';

    /**
     * Height of the video content in CSS pixels.
     * A non negative integer.
     *
     * @var int
     * @qtism-bean-property
     */
    private $height = 0;

    /**
     * Width of the video content in CSS pixels.
     * A non negative integer.
     *
     * @var int
     * @qtism-bean-property
     */
    private $width = 0;


    /**
     * Create a new Media object (Audio or Video).
     *
     * @param string $poster
     * @param int $height
     * @param int $width
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
        $poster = null,
        $height = null,
        $width = null,
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
        parent::__construct($autoPlay, $controls, $crossOrigin, $loop, $mediaGroup, $muted, $preload, $src, $title, $role, $id, $class, $lang, $label);
        $this->setPoster($poster);
        $this->setHeight($height);
        $this->setWidth($width);
    }
    
    public function setPoster($poster): void
    {
        $this->poster = $this->acceptUriOrNull($poster, 'poster');
    }

    public function getPoster(): string
    {
        return $this->poster;
    }

    public function hasPoster(): bool
    {
        return $this->poster !== '';
    }

    public function setHeight($height): void
    {
        $this->height = $this->acceptNonNegativeIntegerOrNull($height, 'height', 0);
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function hasHeight(): bool
    {
        return $this->height !== 0;
    }

    public function setWidth($width): void
    {
        $this->width = $this->acceptNonNegativeIntegerOrNull($width, 'width', 0);
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function hasWidth(): bool
    {
        return $this->width !== 0;
    }

    public function getQtiClassName(): string
    {
        return 'video';
    }
}
