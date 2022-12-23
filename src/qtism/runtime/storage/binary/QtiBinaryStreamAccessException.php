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

namespace qtism\runtime\storage\binary;

use qtism\common\storage\BinaryStreamAccessException;

/**
 * A BinaryStreamAccessException extension dedicated to QTI binary data.
 */
class QtiBinaryStreamAccessException extends BinaryStreamAccessException
{
    /**
     * An error occurred while reading/writing a Variable.
     *
     * @var int
     */
    public const VARIABLE = 10;

    /**
     * An error occurred while reading/writing a Record Field.
     *
     * @var int
     */
    public const RECORDFIELD = 11;

    /**
     * An error occurred while reading/writing a QTI identifier.
     *
     * @var int
     */
    public const IDENTIFIER = 12;

    /**
     * An error occurred while reading/writing a QTI point.
     *
     * @var int
     */
    public const POINT = 13;

    /**
     * An error occurred while reading/writing a QTI pair.
     *
     * @var int
     */
    public const PAIR = 14;

    /**
     * An error occurred while reading/writing a QTI directedPair.
     *
     * @var int
     */
    public const DIRECTEDPAIR = 15;

    /**
     * An error occurred while reading/writing a QTI duration.
     *
     * @var int
     */
    public const DURATION = 16;

    /**
     * An error occurred while reading/writing a URI.
     *
     * @var int
     */
    public const URI = 17;

    /**
     * An error occurred while reading/writing File's binary data.
     *
     * @var int
     */
    public const FILE = 18;

    /**
     * An error occurred while reading/writing an intOrIdentifier.
     *
     * @var int
     */
    public const INTORIDENTIFIER = 19;

    /**
     * An error occurred while reading/writing an assessment item session.
     *
     * @var int
     */
    public const ITEM_SESSION = 20;

    /**
     * An error occurred while reading/writing a route item.
     *
     * @var int
     */
    public const ROUTE_ITEM = 21;

    /**
     * An error occurred while reading/writing pending responses.
     *
     * @var int
     */
    public const PENDING_RESPONSES = 22;

    /**
     * An error occurred while reading/writing path.
     *
     * @var int
     */
    public const PATH = 23;

    /**
     * An error occurred while reading/writing shuffling states.
     *
     * @var int
     */
    public const SHUFFLING_STATE = 24;

    /**
     * An error occurred while reading/writing a shuffling group.
     *
     * @var int
     */
    public const SHUFFLING_GROUP = 25;
}
