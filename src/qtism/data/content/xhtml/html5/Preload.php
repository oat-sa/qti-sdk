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
 * @author Julien Sébire <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\content\xhtml\html5;

use qtism\common\enums\AbstractEnumeration;

/**
 * The html5 media Preload enumeration.
 */
class Preload extends AbstractEnumeration
{
    /**
     * Hints to the user agent that either the author does not expect the user
     * to need the media resource, or that the server wants to minimize
     * unnecessary traffic. This state does not provide a hint regarding how
     * aggressively to actually download the media resource if buffering starts
     * anyway (e.g. once the user hits 'play').
     */
    const NONE = 0;

    /**
     * Hints to the user agent that the user agent can put the user's needs
     * first without risk to the server, up to and including optimistically
     * downloading the entire resource.
     */
    const AUTO = 1;

    /**
     * Hints to the user agent that the author does not expect the user to need
     * the media resource, but that fetching the resource metadata (dimensions,
     * track list, duration, etc), and maybe even the first few frames, is
     * reasonable. If the user agent precisely fetches no more than the
     * metadata, then the media element will end up with its readyState
     * attribute set to HAVE_METADATA; typically though, some frames will be
     * obtained as well and it will probably be HAVE_CURRENT_DATA or
     * HAVE_FUTURE_DATA. When the media resource is playing, hints to the user
     * agent that bandwidth is to be considered scarce, e.g. suggesting
     * throttling the download so that the media data is obtained at the
     * slowest possible rate that still maintains consistent playback.
     */
    const METADATA = 2;

    public static function asArray(): array
    {
        return [
            'none' => self::NONE,
            'auto' => self::AUTO,
            'metadata' => self::METADATA,
        ];
    }
}
