<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @license GPLv2
 *
 * @author Bartłomiej Marszał <bartlomiej@taotesting.com>
 *
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

namespace qtism\data\state;

use qtism\common\enums\Enumeration;

class ExternalScore implements Enumeration
{
    const HUMAN = 'human';

    const EXTERNAL_MACHINE = 'externalMachine';

    /**
     * Return the possible values of the enumeration as an array.
     *
     * @return array An associative array where keys are constant names (as they appear in the code) and values are constant values.
     */
    public static function asArray()
    {
        return array(
            'HUMAN' => self::HUMAN,
            'EXTERNAL_MACHINE' => self::EXTERNAL_MACHINE,
        );
    }

    /**
     * Get a constant value by its name. If $name does not match any of the value
     * of the enumeration, false is returned.
     *
     * @param string|false $name The value relevant to $name or false if not found.
     *
     * @return string|bool
     */
    public static function getConstantByName($name)
    {
        switch (strtolower($name)) {
            case 'human':
                return self::HUMAN;
            case 'external_machine':
                return self::EXTERNAL_MACHINE;
            default:
                return false;
        }
    }

    /**
     * Get a constant name by its value. If $constant does not match any of the names
     * of the enumeration, false is returned.
     *
     * @param string|false $constant The relevant name or false if not found.
     *
     * @return string|bool
     */
    public static function getNameByConstant($constant)
    {
        switch ($constant) {
            case self::HUMAN:
                return 'human';
            case self::EXTERNAL_MACHINE:
                return 'externalMachine';
            default:
                return false;
        }
    }
}
