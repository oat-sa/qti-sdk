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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\common\enums;

use qtism\common\enums\Enumeration;

/**
 * Class Aria.
 *
 * This class represents the different aria-* attributes that can be used in QTI 2.2.
 *
 * @package qtism\data\common\enums
 */
class Aria implements Enumeration
{

    const CONTROLS = 0;

    const DESCRIBED_BY = 1;

    const FLOW_TO = 2;

    const LABEL = 3;

    const LABELLED_BY = 4;

    const LEVEL = 5;

    const LIVE = 6;

    const ORIENTATION = 7;

    const OWNS = 8;

    /**
     * @return array
     */
    public static function asArray()
    {
        return [
            'CONTROLS' => self::CONTROLS,
            'DESCRIBED_BY' => self::DESCRIBED_BY,
            'FLOW_TO' => self::FLOW_TO,
            'LABEL' => self::LABEL,
            'LABELLED_BY' => self::LABELLED_BY,
            'LEVEL' => self::LEVEL,
            'LIVE' => self::LIVE,
            'ORIENTATION' => self::ORIENTATION,
            'OWNS' => self::OWNS
        ];
    }

    /**
     * @param string $name
     * @return false|int
     */
    public static function getConstantByName($name)
    {
        switch (trim(strtolower($name))) {
            case 'aria-controls':
                return self::CONTROLS;
                break;

            case 'aria-describedby':
                return self::DESCRIBED_BY;
                break;

            case 'aria-flowto':
                return self::FLOW_TO;
                break;

            case 'aria-label':
                return self::LABEL;
                break;

            case 'aria-labelledby':
                return self::LABELLED_BY;
                break;

            case 'aria-level':
                return self::LEVEL;
                break;

            case 'aria-live':
                return self::LIVE;
                break;

            case 'aria-orientation':
                return self::ORIENTATION;
                break;

            case 'aria-owns':
                return self::OWNS;
                break;

            default:
                return false;
                break;
        }
    }

    /**
     * @param integer $constant
     * @return false|string
     */
    public static function getNameByConstant($constant)
    {
        switch ($constant) {
            case self::CONTROLS:
                return 'aria-controls';
                break;

            case self::DESCRIBED_BY:
                return 'aria-describedby';
                break;

            case self::FLOW_TO:
                return 'aria-flowto';
                break;

            case self::LABEL:
                return 'aria-label';
                break;

            case self::LABELLED_BY:
                return 'aria-labelledby';
                break;

            case self::LEVEL:
                return 'aria-level';
                break;

            case self::LIVE:
                return 'aria-live';
                break;

            case self::ORIENTATION:
                return 'aria-orientation';
                break;

            case self::OWNS:
                return 'aria-owns';
                break;

            default:
                return false;
                break;
        }
    }
}
