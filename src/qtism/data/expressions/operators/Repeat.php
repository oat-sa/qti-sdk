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

namespace qtism\data\expressions\operators;

use InvalidArgumentException;
use qtism\common\enums\Cardinality;
use qtism\common\utils\Format;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\Pure;

/**
 * The repeat operator takes 1 or more sub-expressions, all of which must have either
 * single or ordered cardinality and the same baseType.
 *
 * The result is an ordered container having the same baseType as its sub-expressions.
 * The container is filled sequentially by evaluating each sub-expression in turn and
 * adding the resulting single values to the container, iterating this process
 * numberRepeats times in total. If numberRepeats refers to a variable whose
 * value is less than 1, the value of the whole expression is NULL.
 *
 * Any sub-expressions evaluating to NULL are ignored. If all sub-expressions
 * are NULL then the result is NULL.
 */
class Repeat extends Operator implements Pure
{
    /**
     * A number of repetitions or a variable reference.
     *
     * @var int|string
     * @qtism-bean-property
     */
    private $numberRepeats;

    /**
     * Create a new instance of Repeat.
     *
     * @param ExpressionCollection $expressions A collection of Expression objects.
     * @param int $numberRepeats An integer or a QTI variable reference.
     */
    public function __construct(ExpressionCollection $expressions, $numberRepeats)
    {
        parent::__construct($expressions, 1, -1, [Cardinality::SINGLE, Cardinality::ORDERED], [OperatorBaseType::SAME]);
        $this->setNumberRepeats($numberRepeats);
    }

    /**
     * Set the numberRepeats attribute.
     *
     * @param int|string $numberRepeats An integer or a QTI variable reference.
     * @throws InvalidArgumentException If $numberRepeats is not an integer nor a valid QTI variable reference.
     */
    public function setNumberRepeats($numberRepeats)
    {
        if (is_int($numberRepeats) || (gettype($numberRepeats) === 'string' && Format::isVariableRef($numberRepeats))) {
            $this->numberRepeats = $numberRepeats;
        } else {
            $msg = "The numberRepeats argument must be an integer or a variable reference, '" . gettype($numberRepeats) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the numberRepeats attribute.
     *
     * @return int|string An integer or a QTI variable reference.
     */
    public function getNumberRepeats()
    {
        return $this->numberRepeats;
    }

    /**
     * @see \qtism\data\QtiComponent::getQtiClassName()
     */
    public function getQtiClassName()
    {
        return 'repeat';
    }

    /**
     * Checks whether this expression is pure.
     *
     * @link https://en.wikipedia.org/wiki/Pure_function
     *
     * @return bool True if the expression is pure, false otherwise
     */
    public function isPure()
    {
        return $this->getExpressions()->isPure();
    }
}
