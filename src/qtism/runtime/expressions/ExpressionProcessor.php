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

namespace qtism\runtime\expressions;

use InvalidArgumentException;
use qtism\common\utils\Reflection as ReflectionUtils;
use qtism\data\expressions\Expression;
use qtism\runtime\common\Processable;
use qtism\runtime\common\State;

/**
 * The ExpressionProcessor class aims at processing QTI Data Model
 * Expressions.
 */
abstract class ExpressionProcessor implements Processable
{
    /**
     * The QTI Data Model expression to be Processed.
     *
     * @var Expression
     */
    private $expression = null;

    /**
     * A state.
     *
     * @var State
     */
    private $state = null;

    /**
     * Create a new ExpressionProcessor object.
     *
     * @param Expression $expression The QTI Data Model Expression to be processed.
     */
    public function __construct(Expression $expression)
    {
        $this->setExpression($expression);
        $this->setState(new State());
    }

    /**
     * Set the QTI Data Model Expression to be processed.
     *
     * @param Expression $expression A QTI Data Model Expression object.
     * @throws InvalidArgumentException If $expression is not a subclass nor implements the Expression type returned by the getExpressionType method.
     */
    public function setExpression(Expression $expression): void
    {
        $expectedType = $this->getExpressionType();

        if (ReflectionUtils::isInstanceOf($expression, $expectedType) !== true) {
            $msg = sprintf(
                'The %s Expression Processor only processes %s Expression objects, %s given.',
                get_class($this),
                $expectedType,
                get_class($expression)
            );
            throw new InvalidArgumentException($msg);
        }

        $this->expression = $expression;
    }

    /**
     * Get the QTI Data Model Expression to be processed.
     *
     * @return Expression A QTI Data Model Expression object.
     */
    public function getExpression(): Expression
    {
        return $this->expression;
    }

    /**
     * Set the current State object.
     *
     * @param State $state A State object.
     */
    public function setState(State $state): void
    {
        $this->state = $state;
    }

    /**
     * Get the current State object.
     *
     * @return State
     */
    public function getState(): State
    {
        return $this->state;
    }

    /**
     * Get the expected type (fully qualifed class name) of the Expression objects that can be processed
     * by the actual implementation.
     *
     * @return string A Fully Qualified PHP Class Name (FQCN).
     */
    abstract protected function getExpressionType(): string;
}
