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
class Video extends Media
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
     *
     * @var int
     * @qtism-bean-property
     */
    private $height = 0;

    /**
     * Width of the video content in CSS pixels.
     *
     * @var int
     * @qtism-bean-property
     */
    private $width = 0;

    /**
     * Set the poster attribute.
     *
     * @param string $poster A URI.
     * @throws InvalidArgumentException If $poster is not a valid URI.
     */
    public function setPoster($poster)
    {
        if (!Format::isUri($poster)) {
            $given = is_string($poster)
                ? $poster
                : gettype($poster);

            throw new InvalidArgumentException(
                sprintf(
                    'The "poster" argument must be a valid URI, "%s" given.',
                    $given
                )
            );
        }

        $this->poster = $poster;
    }

    public function getPoster(): string
    {
        return $this->poster;
    }

    public function hasPoster(): bool
    {
        return $this->poster !== '';
    }
    
    /**
     * Set the height attribute.
     *
     * @param int $height Height of the video.
     * @throws InvalidArgumentException If $height is not an integer or strictly negative.
     */
    public function setHeight($height)
    {
        if (!is_int($height) || $height < 0) {
            $given = is_int($height)
                ? $height
                : gettype($height);

            throw new InvalidArgumentException(
                sprintf(
                    'The "height" argument must be 0 or a positive integer, "%s" given.',
                    $given
                )
            );
        }

        $this->height = $height;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function hasHeight(): bool
    {
        return $this->height !== 0;
    }
    
    /**
     * Set the Width attribute.
     *
     * @param int $width Width of the video.
     * @throws InvalidArgumentException If $width is not an integer or strictly negative.
     */
    public function setWidth($width)
    {
        if (!is_int($width) || $width < 0) {
            $given = is_int($width)
                ? $width
                : gettype($width);

            throw new InvalidArgumentException(
                sprintf(
                    'The "width" argument must be 0 or a positive integer, "%s" given.',
                    $given
                )
            );
        }

        $this->width = $width;
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
