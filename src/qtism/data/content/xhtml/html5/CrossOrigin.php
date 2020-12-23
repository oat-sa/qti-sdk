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
 * The html5 media CrossOrigin enumeration.
 * The crossorigin content characteristic on media tags is a CORS settings
 * attribute.
 */
class CrossOrigin extends AbstractEnumeration
{
    /**
     * Cross-origin CORS requests for the element will have the omit
     * credentials flag set.
     */
    const ANONYMOUS = 0;

    /**
     * Cross-origin CORS requests for the element will not have the omit
     * credentials flag set.
     */
    const USE_CREDENTIALS = 1;

    public static function asArray(): array
    {
        return [
            'anonymous' => self::ANONYMOUS,
            'use-credentials' => self::USE_CREDENTIALS,
        ];
    }
}
