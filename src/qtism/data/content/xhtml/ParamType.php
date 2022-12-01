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

namespace qtism\data\content\xhtml;

use qtism\common\enums\Enumeration;

/**
 * The paramType enumeration.
 */
class ParamType implements Enumeration
{
    /**
     * DATA
     *
     * @var int
     */
    public const DATA = 0;

    /**
     * REF
     *
     * @var int
     */
    public const REF = 1;

    /**
     * @return array
     */
    public static function asArray(): array
    {
        return [
            'DATA' => self::DATA,
            'REF' => self::REF,
        ];
    }

    /**
     * @param false|int $name
     * @return bool|int
     */
    public static function getConstantByName($name)
    {
        switch (strtolower((string)$name)) {
            case 'data':
                return self::DATA;
                break;

            case 'ref':
                return self::REF;
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
            case self::DATA:
                return 'DATA';
                break;

            case self::REF:
                return 'REF';
                break;

            default:
                return false;
                break;
        }
    }
}
