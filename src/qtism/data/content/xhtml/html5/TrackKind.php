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

use qtism\common\enums\AbstractEnumeration;

/**
 * The track kind enumeration.
 */
class TrackKind extends AbstractEnumeration
{
    /**
     * Transcription or translation of the dialogue, suitable for when the
     * sound is available but not understood (e.g. because the user does not
     * understand the language of the media resource's audio track).
     * Overlaid on the video.
     * This is the default value for the track element.
     */
    const SUBTITLES = 0;

    /**
     * Transcription or translation of the dialogue, sound effects, relevant
     * musical cues, and other relevant audio information, suitable for when
     * sound is unavailable or not clearly audible (e.g. because it is muted,
     * drowned-out by ambient noise, or because the user is deaf).
     * Overlaid on the video; labeled as appropriate for the hard-of-hearing.
     */
    const CAPTIONS = 1;

    /**
     * Tracks intended for use from script. Not displayed by the user agent.
     */
    const DESCRIPTIONS = 2;

    /**
     * Chapter titles, intended to be used for navigating the media resource.
     * Displayed as an interactive (potentially nested) list in the user
     * agent's interface.
     */
    const CHAPTERS = 3;

    /**
     * Textual descriptions of the video component of the media resource,
     * intended for audio synthesis when the visual component is obscured,
     * unavailable, or not usable (e.g. because the user is interacting with
     * the application without a screen while driving, or because the user is
     * blind).
     * Synthesized as audio.
     */
    const METADATA = 4;

    public static function asArray(): array
    {
        return [
            'subtitles' => self::SUBTITLES,
            'captions' => self::CAPTIONS,
            'descriptions' => self::DESCRIPTIONS,
            'chapters' => self::CHAPTERS,
            'metadata' => self::METADATA,
        ];
    }
}
