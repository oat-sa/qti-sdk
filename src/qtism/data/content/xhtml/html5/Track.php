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
 * Html 5 media track class.
 */
class Track extends Html5EmptyElement
{
    /**
     * The required src attribute specifies the URL of the track file.
     * Tracks are formatted in WebVTT format (.vtt files).
     *
     * @var string
     * @qtism-bean-property
     */
    private $src;

    /**
     * When present, the default attribute specifies that the track is to be
     * enabled if the user's preferences do not indicate that another track
     * would be more appropriate.
     * There must not be more than one track element with a default attribute
     * per media element.
     *
     * @var bool
     * @qtism-bean-property
     */
    private $default;

    /**
     * The kind attribute specifies the kind of text track.
     * The value of this attribute must be one of the TrackKind constants.
     *
     * @var integer
     * @qtism-bean-property
     */
    private $kind;

    /**
     * The srclang attribute specifies the language of the track text data.
     * This attribute is required if kind="subtitles".
     *
     * @see https://en.wikipedia.org/wiki/ISO_639-1 for all language codes.
     *
     * @var string
     * @qtism-bean-property
     */
    private $srcLang;

    /**
     * Create a new Track object.
     *
     * @param string $src A URI.
     * @param bool $default Is this track the default track?
     * @param int $kind Kind of track. One of the TrackKind constants.
     * @param string $srcLang The srclang attribute specifies the language of the track text data.
     * @param string $id A QTI identifier.
     * @param string $class One or more class names separated by spaces.
     * @param string $lang An RFC3066 language.
     * @param string $label A label that does not exceed 256 characters.
     */
    public function __construct(
        $src,
        $default = false,
        $kind = TrackKind::SUBTITLES,
        $srcLang = '',
        $id = '',
        $class = '',
        $lang = '',
        $label = ''
    ) {
        parent::__construct($id, $class, $lang, $label);
        $this->setSrc($src);
        $this->setDefault($default);
        $this->setKind($kind);
        $this->setSrcLang($srcLang);
    }

    /**
     * Set the src attribute.
     *
     * @param string $src A URI.
     * @throws InvalidArgumentException If $src is not a valid URI.
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
     * Get the src attribute.
     *
     * @return string A URI.
     */
    public function getSrc(): string
    {
        return $this->src;
    }

    /**
     * Set the alt attribute.
     *
     * @param bool $default Is this track the default for the related media?
     * @throws InvalidArgumentException If $default is not a boolean.
     */
    public function setDefault($default)
    {
        if (!is_bool($default)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The "default" argument must be a boolean, "%s" given.',
                    gettype($default)
                )
            );
        }

        $this->default = $default;
    }

    /**
     * Get the value of the default attribute.
     *
     * @return bool
     */
    public function getDefault(): bool
    {
        return $this->default;
    }

    /**
     * Get the value of the default attribute.
     *
     * @return bool
     */
    public function hasDefault(): bool
    {
        return $this->default !== false;
    }

    /**
     * Sets the kind of track.
     * @param int $kind One of the TrackKind constants.
     */
    public function setKind($kind)
    {
        if (!in_array($kind, TrackKind::asArray(), true)) {
            $given = is_int($kind)
                ? $kind
                : gettype($kind);

            throw new InvalidArgumentException(
                sprintf(
                    'The "kind" argument must be a value from the TrackKind enumeration, "%s" given.',
                    $given
                )
            );
        }

        $this->kind = $kind;
    }

    /**
     * @return int
     */
    public function getKind(): int
    {
        return $this->kind;
    }

    /**
     * Get the value of the default attribute.
     *
     * @return bool
     */
    public function hasKind(): bool
    {
        return $this->kind !== TrackKind::SUBTITLES;
    }

    /**
     * @param string $srcLang
     */
    public function setSrcLang($srcLang)
    {
        if (!is_string($srcLang)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The "srclang" argument must be a string, "%s" given.',
                    gettype($srcLang)
                )
            );
        }
        
        $this->srcLang = $srcLang;
    }

    /**
     * @return string
     */
    public function getSrcLang(): string
    {
        return $this->srcLang;
    }

    /**
     * @return bool
     */
    public function hasSrcLang(): bool
    {
        return $this->srcLang !== '';
    }

    /**
     * @return string
     */
    public function getQtiClassName()
    {
        return 'track';
    }
}
