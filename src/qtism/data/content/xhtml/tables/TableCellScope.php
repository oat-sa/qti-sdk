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

namespace qtism\data\content\xhtml\tables;

use qtism\common\enums\Enumeration;

/**
 * The QTI tableCellScope class.
 */
class TableCellScope implements Enumeration
{
    /**
     * @var int
     */
    const ROW = 0;

    /**
     * @var int
     */
    const COL = 1;

    /**
     * @var int
     */
    const ROWGROUP = 2;

    /**
     * @var int
     */
    const COLGROUP = 3;

    /**
     * @return array
     */
    public static function asArray()
    {
        return [
            'ROW' => self::ROW,
            'COL' => self::COL,
            'ROWGROUP' => self::ROWGROUP,
            'COLGROUP' => self::COLGROUP,
        ];
    }

    /**
     * @param false|int $name
     * @return bool|int
     */
    public static function getConstantByName($name)
    {
        switch (strtolower($name)) {
            case 'row':
                return self::ROW;
                break;

            case 'col':
                return self::COL;
                break;

            case 'rowgroup':
                return self::ROWGROUP;
                break;

            case 'colgroup':
                return self::COLGROUP;
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
            case self::ROW:
                return 'row';
                break;

            case self::COL:
                return 'col';
                break;

            case self::ROWGROUP:
                return 'rowgroup';
                break;

            case self::COLGROUP:
                return 'colgroup';
                break;

            default:
                return false;
                break;
        }
    }
}
