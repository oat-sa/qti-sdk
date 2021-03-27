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
use qtism\data\content\enums\TrackKind;

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
    private $default = false;

    /**
     * The kind attribute specifies the kind of text track.
     * The value of this attribute must be one of the TrackKind constants.
     * Defaults to TrackKind::SUBTITLES.
     *
     * @var integer
     * @qtism-bean-property
     */
    private $kind;

    /**
     * From QTI spec:
     * The 'srclang' characteristic gives the language of the text track data.
     * The value must be a valid BCP 47 language tag.
     * This attribute must be present if the tag's "kind" attribute is in the
     * subtitles state.
     *
     * OAT note:
     * This is inconsistent indeed because default value for "kind" is actually
     * "subtitles" and no default value is given for "srcLang", making it
     * impossible to rely only on default values. We decide to use "en" as a
     * default value for "srcLang" when "kind" is in "subtitles" state, and an
     * empty string when "kind" is not in "subtitles" state.
     *
     * @see https://en.wikipedia.org/wiki/IETF_language_tag for BCP 47.
     *
     * @var string
     * @qtism-bean-property
     */
    private $srcLang = 'en';

    /**
     * Create a new Track object.
     *
     * @param mixed $src A URI.
     * @param bool|int|string|null $default Is this track the default track?
     * @param int|string|null $kind Kind of track. One of the TrackKind constants.
     * @param string|null $srcLang The srclang attribute specifies the language of the track text data.
     * @param string|null $id A QTI identifier.
     * @param string|null $class One or more class names separated by spaces.
     * @param string|null $lang An RFC3066 language.
     * @param string|null $label A label that does not exceed 256 characters.
     * @param string|null $title A title in the sense of Html title attribute
     * @param int|string|null $role A role taken in the Role constants.
     */
    public function __construct(
        $src,
        $default = null,
        $kind = null,
        $srcLang = null,
        $title = null,
        $role = null,
        $id = null,
        $class = null,
        $lang = null,
        $label = null
    ) {
        parent::__construct($title, $role, $id, $class, $lang, $label);
        $this->setSrc($src);
        $this->setDefault($default);
        $this->setKind($kind);
        $this->setSrcLang($srcLang);
    }

    public function setSrc($src): void
    {
        $this->src = $this->acceptUri($src, 'src');
    }

    public function getSrc(): string
    {
        return $this->src;
    }

    public function setDefault($default = null): void
    {
        $this->default = $this->acceptBooleanOrNull($default, 'default', false);
    }

    public function getDefault(): bool
    {
        return $this->default;
    }

    public function hasDefault(): bool
    {
        return $this->default !== false;
    }

    public function setKind($kind = null): void
    {
        $this->kind = TrackKind::accept($kind, 'kind');

        // srcLang attribute is required if kind="subtitles" => revalidate srcLang.
        if ($this->isKindSubtitles()) {
            $this->setSrcLang($this->getSrcLang());
        }
    }

    public function getKind(): int
    {
        return $this->kind;
    }

    /**
     * Has the kind attribute a non-default value?
     */
    public function hasKind(): bool
    {
        return !$this->isKindSubtitles();
    }

    /**
     * Has the kind attribute the default value?
     */
    public function isKindSubtitles(): bool
    {
        return $this->kind === TrackKind::getConstantByName('subtitles');
    }

    public function setSrcLang(string $srcLang = null): void
    {
        $srcLang = $srcLang ?? '';

        if ($srcLang === '' && $this->isKindSubtitles()) {
            $srcLang = 'en';
        }

        if ($srcLang !== '' && !Format::isBCP47Lang($srcLang)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The "srclang" argument must be a valid BCP 47 language code, "%s" given.',
                    $srcLang
                )
            );
        }

        $this->srcLang = $srcLang;
    }

    public function getSrcLang(): string
    {
        return $this->srcLang;
    }

    public function hasSrcLang(): bool
    {
        return $this->srcLang !== '';
    }

    public function getQtiClassName(): string
    {
        return 'track';
    }
}
