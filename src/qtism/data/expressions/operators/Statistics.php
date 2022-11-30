<?php

declare(strict_types=1);

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

namespace qtism\data\expressions\operators;

use qtism\common\enums\Enumeration;

/**
 * The QTI Statistics enumeration.
 */
class Statistics implements Enumeration
{
    /**
     * From IMS QTI:
     *
     * The arithmetic mean of the argument, which must be a container of numerical
     * base type, which contains a sample of observations.
     *
     * @var int
     */
    public const MEAN = 0;

    /**
     * From IMS QTI:
     *
     * The variance of the argument, which must be a container of numerical base type,
     * with containerSize greater than 1, containing a sample of observations.
     *
     * @var int
     */
    public const SAMPLE_VARIANCE = 1;

    /**
     * From IMS QTI:
     *
     * The standard deviation of the argument, which must be a container of numerical
     * base type, with containerSize greater than 1, containing a sample of observations.
     *
     * @var int
     */
    public const SAMPLE_SD = 2;

    /**
     * From IMS QTI:
     *
     * The variance of the argument, which must be a container of numerical base type
     * with containerSize greater than 1.
     *
     * @var int
     */
    public const POP_VARIANCE = 3;

    /**
     * From IMS QTI:
     *
     * The standard deviation of the argument, which must be a container of numerical base
     * type with containerSize greater than 1.
     *
     * @var int
     */
    public const POP_SD = 4;

    /**
     * @return array
     */
    public static function asArray(): array
    {
        return [
            'MEAN' => self::MEAN,
            'SAMPLE_VARIANCE' => self::SAMPLE_VARIANCE,
            'SAMPLE_SD' => self::SAMPLE_SD,
            'POP_VARIANCE' => self::POP_VARIANCE,
            'POP_SD' => self::POP_SD,
        ];
    }

    /**
     * @param false|int $name
     * @return bool|int
     */
    public static function getConstantByName($name)
    {
        switch (strtolower((string)$name)) {
            case 'mean':
                return self::MEAN;
                break;

            case 'samplevariance':
                return self::SAMPLE_VARIANCE;
                break;

            case 'samplesd':
                return self::SAMPLE_SD;
                break;

            case 'popvariance':
                return self::POP_VARIANCE;
                break;

            case 'popsd':
                return self::POP_SD;
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
            case self::MEAN:
                return 'mean';
                break;

            case self::SAMPLE_VARIANCE:
                return 'sampleVariance';
                break;

            case self::SAMPLE_SD:
                return 'sampleSD';
                break;

            case self::POP_VARIANCE:
                return 'popVariance';
                break;

            case self::POP_SD:
                return 'popSD';
                break;

            default:
                return false;
                break;
        }
    }
}
