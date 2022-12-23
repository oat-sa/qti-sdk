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

namespace qtism\common\storage;

/**
 * The BinaryStreamAccessException class represents the error
 * that could occur while reading/extracting data from a BinaryStream
 * object.
 */
class BinaryStreamAccessException extends StreamAccessException
{
    /**
     * An error occurred while reading|writing a tinyint.
     *
     * @var int
     */
    public const TINYINT = 2;

    /**
     * An error occurred while reading|writing a short int.
     *
     * @var int
     */
    public const SHORT = 3;

    /**
     * An error occurred while reading|writing an int.
     *
     * @var int
     */
    public const INT = 4;

    /**
     * An error occurred while reading|writing a float.
     *
     * @var int
     */
    public const FLOAT = 5;

    /**
     * An error occurred while reading|writing a boolean.
     *
     * @var int
     */
    public const BOOLEAN = 6;

    /**
     * An error occurred while reading|writing a string.
     *
     * @var int
     */
    public const STRING = 7;

    /**
     * An error occurred while reading|writing binary data.
     *
     * @var int
     */
    public const BINARY = 8;

    /**
     * An error occurred while reading|writing a DateTime.
     *
     * @var int
     */
    public const DATETIME = 9;
}
