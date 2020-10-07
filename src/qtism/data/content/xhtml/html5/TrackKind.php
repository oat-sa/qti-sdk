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

use qtism\common\enums\Enumeration;

/**
 * The track kind enumeration.
 */
class TrackKind implements Enumeration
{
    const SUBTITLES = 0;

    const CAPTIONS = 1;

    const DESCRIPTIONS = 2;

    const CHAPTERS = 3;

    const METADATA = 4;

    public static function asArray()
    {
        return [
            'subtitles' => self::SUBTITLES,
            'captions' => self::CAPTIONS,
            'descriptions' => self::DESCRIPTIONS,
            'chapters' => self::CHAPTERS,
            'metadata' => self::METADATA,
        ];
    }

    public static function getConstantByName($name)
    {
        return self::asArray()[$name] ?? false;
    }

    public static function getNameByConstant($constant)
    {
        $constants = array_flip(self::asArray());

        return $constants[$constant] ?? false;
    }
}
