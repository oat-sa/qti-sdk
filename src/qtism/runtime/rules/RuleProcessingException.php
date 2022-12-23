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

namespace qtism\runtime\rules;

use InvalidArgumentException;
use qtism\runtime\common\Processable;
use qtism\runtime\common\ProcessingException;

/**
 * An Exception to be thrown in a Rule Processing context.
 */
class RuleProcessingException extends ProcessingException
{
    /**
     * The error code to use when the exitResponse rule is invoked
     * during rule processing.
     *
     * @var int
     */
    public const EXIT_RESPONSE = 10;

    /**
     * The error code to use when the exitTest rule is invoked
     * during rule processing.
     *
     * @var int
     */
    public const EXIT_TEST = 11;

    /**
     * The error code to use when the exitTemplate rule is invoked
     * during rule processing.
     *
     * @var int
     */
    public const EXIT_TEMPLATE = 12;

    /**
     * The error code to use when a templateConstraint rule returned
     * false or null.
     *
     * @var int
     */
    public const TEMPLATE_CONSTRAINT_UNSATISFIED = 13;

    /**
     * Set the source of the error.
     *
     * @param Processable $source The source of the error.
     * @throws InvalidArgumentException If $source is not an ExpressionProcessor object.
     */
    public function setSource(Processable $source): void
    {
        if ($source instanceof RuleProcessor) {
            parent::setSource($source);
        } else {
            $msg = 'RuleProcessingException::setSource only accept RuleProcessor objects.';
            throw new InvalidArgumentException($msg);
        }
    }
}
