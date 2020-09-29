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

namespace qtism\runtime\storage\common;

use Exception;

/**
 * The StorageException class represents exceptions that AssessmentTestSession
 * Storage Services encounter an error.
 */
class StorageException extends Exception
{
    /**
     * The error code to be used when the nature of the error is unknown.
     * Should be used in absolute necessity. Otherwise, use the appropriate
     * error code.
     *
     * @var int
     */
    const UNKNOWN = 0;

    /**
     * Error code to be used when an error occurs while
     * instantiating an AssessmentTestSession.
     *
     * @var int
     */
    const INSTANTIATION = 1;

    /**
     * @var int
     * @deprecated since 0.23.0. Use qtism\runtime\storage\common\StorageException::PERSISTENCE instead. Will be dropped in 0.24.0.
     * Know usage:
     * https://github.com/oat-sa/extension-tao-testqti/blob/4260de91509d2bdb4a101faf6e31fdbcbed1f048/helpers/class.TestSessionStorage.php#L199
     */
    const PERSITANCE = 2;

    /**
     * Error code to use when an error occurs while
     * persisting an AssessmentTestSession.
     *
     * @var int
     */
    const PERSISTENCE = 2;

    /**
     * Error code to use when an error occurs while
     * retrieving an AssessmentTestSession.
     *
     * @var int
     */
    const RETRIEVAL = 3;
}
