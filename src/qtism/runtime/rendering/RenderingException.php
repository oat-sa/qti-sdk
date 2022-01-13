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

namespace qtism\runtime\rendering;

use Exception;
use qtism\common\QtiSdkPackageContentException;

/**
 * Exception to be thrown when an error occurs during a Rendering
 * process.
 */
class RenderingException extends Exception implements QtiSdkPackageContentException
{
    /**
     * Error code to use when the nature of the error
     * is unknown.
     *
     * (Should never be used)
     *
     * @var int
     */
    const UNKNOWN = 0;

    /**
     * Error code to use when no renderer is found
     * for a given component.
     *
     * @var int
     */
    const NO_RENDERER = 1;

    /**
     * Error code to use for exception only occuring/detectable at runtime.
     *
     * @var int
     */
    const RUNTIME = 2;

    /**
     * Create a new RenderingException object.
     *
     * @param string $message A message describing the error.
     * @param int $code A code for the client-code.
     * @param Exception $previous An optional previous exception.
     */
    public function __construct($message, $code, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
