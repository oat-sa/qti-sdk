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

namespace qtism\data\storage;

use Exception;
use qtism\common\ContentPackageExceptionInterface;

/**
 * An error to be thrown when an error occurs while dealing with
 * AssessmentTest description storage (loading/saving/parsing).
 */
class StorageException extends Exception implements ContentPackageExceptionInterface
{
    /**
     * The error is unknown.
     *
     * @var int
     */
    const UNKNOWN = 0;

    /**
     * The error occurred while reading.
     *
     * @var int
     */
    const READ = 1;

    /**
     * The error occurred while writing.
     *
     * @var int
     */
    const WRITE = 2;

    /**
     * The error is related to a version issue.
     *
     * @var int
     */
    const VERSION = 3;

    /**
     * Create a new StorageException object.
     *
     * @param string $message A human-readable message.
     * @param int $code A exception code (see class constants).
     * @param Exception $previous An eventual previous Exception object.
     */
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
