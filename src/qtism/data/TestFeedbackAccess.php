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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data;

use qtism\common\enums\Enumeration;

/**
 * The TestFeedBack enumeration.
 */
class TestFeedbackAccess implements Enumeration
{
    public const AT_END = 0;

    public const DURING = 1;

    /**
     * @return array
     */
    public static function asArray(): array
    {
        return [
            'AT_END' => self::AT_END,
            'DURING' => self::DURING,
        ];
    }

    /**
     * @param false|int $name
     * @return bool|int
     */
    public static function getConstantByName($name)
    {
        switch (strtolower((string)$name)) {
            case 'atend':
                return self::AT_END;
                break;

            case 'during':
                return self::DURING;
                break;

            default:
                return false;
                break;
        }
    }

    /**
     * @param false|string $constant
     * @return bool|string
     */
    public static function getNameByConstant($constant)
    {
        switch ($constant) {
            case self::AT_END:
                return 'atEnd';
                break;

            case self::DURING:
                return 'during';
                break;

            default:
                return false;
                break;
        }
    }
}
