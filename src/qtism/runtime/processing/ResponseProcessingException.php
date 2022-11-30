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

namespace qtism\runtime\processing;

use InvalidArgumentException;
use qtism\runtime\common\Processable;
use qtism\runtime\common\ProcessingException;

/**
 * An Exception to be thrown in an Expression Processing context.
 */
class ResponseProcessingException extends ProcessingException
{
    /**
     * Error code to use when a response processing
     * template cannot be found.
     *
     * @var int
     */
    public const TEMPLATE_NOT_FOUND = 11;

    /**
     * Error code to use when a response processing
     * template contains or produces errors.
     *
     * @var int
     */
    public const TEMPLATE_ERROR = 12;

    /**
     * Set the source of the error.
     *
     * @param Processable $source The source of the error.
     * @throws InvalidArgumentException If $source is not a ResponseProcessingEngine object.
     */
    public function setSource(Processable $source): void
    {
        if ($source instanceof ResponseProcessingEngine) {
            parent::setSource($source);
        } else {
            $msg = 'ResponseProcessingException::setSource only accepts ResponseProcessingEngine objects.';
            throw new InvalidArgumentException($msg);
        }
    }
}
