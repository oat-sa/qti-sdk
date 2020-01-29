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
 * Copyright (c) 2018-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Moyon Camille <camille@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\results;

use qtism\common\enums\Enumeration;

class SessionStatus implements Enumeration
{
    /**
     * The value to use when the item variables represent the values at the end of an attempt after response processing has taken place.
     * In other words, after the outcome values have been updated to reflect the values of the response variables.
     */
    const STATUS_FINAL = 0;

    /**
     * The value to use for sessions in the initial state, as described above. This value can only be used to describe sessions
     * for which the response variable numAttempts is 0. The values of the variables are set according to the rules
     * defined in the appropriate declarations (see responseDeclaration, outcomeDeclaration and templateDeclaration).
     */
    const STATUS_INITIAL = 1;

    /**
     * The value to use when the item variables represent the values of the response variables after submission but before response processing has taken place.
     * Again, the outcomes are those assigned at the end of the previous attempt as they are awaiting response processing.
     */
    const STATUS_PENDING_RESPONSE_PROCESSING = 2;

    /**
     * The value to use when the item variables represent a snapshot of the current values during an attempt
     * (in other words, while interacting or suspended). The values of the response variables represent work in progress
     * that has not yet been submitted for response processing by the candidate.
     * The values of the outcome variables represent the values assigned during response processing at the end of the previous attempt or,
     * in the case of the first attempt, the default values given in the variable declarations.
     */
    const STATUS_PENDING_SUBMISSON = 3;

    /**
     * Get the array representation of SessionStatuses
     *
     * @return array
     */
    public static function asArray()
    {
        return [
            self::STATUS_FINAL,
            self::STATUS_INITIAL,
            self::STATUS_PENDING_RESPONSE_PROCESSING,
            self::STATUS_PENDING_SUBMISSON,
        ];
    }

    /**
     * Find a constant with string name
     * Return false if does not match
     *
     * @param false|int $name
     * @return bool|int
     */
    public static function getConstantByName($name)
    {
        switch ($name) {
            case 'final':
                return self::STATUS_FINAL;
                break;

            case 'initial':
                return self::STATUS_INITIAL;
                break;

            case 'pendingResponseProcessing':
                return self::STATUS_PENDING_RESPONSE_PROCESSING;
                break;

            case 'pendingSubmission':
                return self::STATUS_PENDING_SUBMISSON;
                break;

            default:
                return false;
                break;
        }
    }

    /**
     * Find a human name associated to constant
     * Return false if does not match
     *
     * @param false|int $constant
     * @return bool|int
     */
    public static function getNameByConstant($constant)
    {
        switch ($constant) {
            case self::STATUS_FINAL:
                return 'final';
                break;

            case self::STATUS_INITIAL:
                return 'initial';
                break;

            case self::STATUS_PENDING_RESPONSE_PROCESSING:
                return 'pendingResponseProcessing';
                break;

            case self::STATUS_PENDING_SUBMISSON:
                return 'pendingSubmission';
                break;

            default:
                return false;
                break;
        }
    }
}
