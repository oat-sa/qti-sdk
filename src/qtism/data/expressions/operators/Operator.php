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
use qtism\data\expressions\Expression;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\QtiComponentCollection;

/**
 * A QTI Operator is a composite expression. The expression
 * contains a set of sub-expressions.
 */
abstract class Operator extends Expression
{
    /**
     * The minimal number of operands the operator can take.
     *
     * @var int
     */
    private $minOperands = 0;

    /**
     * The maximal number of operands the operator can take.
     *
     * @var int
     */
    private $maxOperands = -1;

    /**
     * The cardinalities accepted for the input operands.
     *
     * @var array
     */
    private $acceptedCardinalities = [];

    /**
     * The baseTypes accepted for the input operands.
     *
     * @var array
     */
    private $acceptedBaseTypes = [];

    /**
     * The sub-expressions.
     *
     * @var ExpressionCollection
     * @qtism-bean-property
     */
    private $expressions;

    /**
     * Create a new instance of Operator.
     *
     * @param ExpressionCollection $expressions The sub-expressions that form the operator.
     * @param int $minOperands The minimum operands count (0 equals no min).
     * @param int $maxOperands The maximum operands count (-1 equals no max).
     * @param array $acceptedCardinalities An array of values from the Cardinality enumeration.
     * @param array $acceptedBaseTypes An array of values from the OperatorBaseType enumeration.
     * @throws InvalidArgumentException If $expressions does not match the restrictions or an invalid argument is given.
     */
    public function __construct(ExpressionCollection $expressions, $minOperands = 0, $maxOperands = -1, $acceptedCardinalities = [Cardinality::SINGLE, Cardinality::MULTIPLE, Cardinality::ORDERED], $acceptedBaseTypes = [OperatorBaseType::ANY])
    {
        $this->setMinOperands($minOperands);
        $this->setMaxOperands($maxOperands);
        $this->setAcceptedCardinalities($acceptedCardinalities);
        $this->setAcceptedBaseTypes($acceptedBaseTypes);

        $this->setExpressions($expressions);
    }

    /**
     * Set the collection of expressions that compose the Operation object.
     *
     * @param ExpressionCollection $expressions A collection of $expressions that form the hierarchy of expressions.
     * @throws InvalidArgumentException If $expressions does not contain at least one Expression object.
     */
    public function setExpressions(ExpressionCollection $expressions): void
    {
        $this->expressions = $expressions;
    }

    /**
     * Get the collection of expressions that compose the Operator object.
     *
     * @return ExpressionCollection A collection of Expression objects.
     */
    public function getExpressions(): ExpressionCollection
    {
        return $this->expressions;
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents(): QtiComponentCollection
    {
        $comp = $this->getExpressions()->getArrayCopy();

        return new QtiComponentCollection($comp);
    }

    /**
     * Set the minimum operands count for this Operator.
     *
     * @param int $minOperands An integer which is >= 0.
     * @throws InvalidArgumentException If $minOperands is not an integer >= 0.
     */
    public function setMinOperands($minOperands): void
    {
        if (is_int($minOperands) && $minOperands >= 0) {
            $this->minOperands = $minOperands;
        } else {
            $msg = "The minOperands argument must be an integer >= 0, '" . $minOperands . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the minimum operands count for this Operator.
     *
     * @return int
     */
    public function getMinOperands(): int
    {
        return $this->minOperands;
    }

    /**
     * Set the maxmimum operands count for this Operator. The value
     * is -1 if unlimited.
     *
     * @param int $maxOperands
     * @throws InvalidArgumentException If $maxOperands is not an integer.
     */
    public function setMaxOperands($maxOperands): void
    {
        if (is_int($maxOperands)) {
            $this->maxOperands = $maxOperands;
        } else {
            $msg = "The maxOperands argument must be an integer, '" . gettype($maxOperands) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the maximum operands count for this Operator. The value
     * is -1 if unlimited.
     *
     * @return int
     */
    public function getMaxOperands(): int
    {
        return $this->maxOperands;
    }

    /**
     * Get the accepted operand cardinalities.
     *
     * @return array An array of values from the Cardinality enumeration.
     */
    public function getAcceptedCardinalities(): array
    {
        return $this->acceptedCardinalities;
    }

    /**
     * Set the accepted operand cardinalities.
     *
     * @param array $acceptedCardinalities An array of values from the Cardinality enumeration.
     * @throws InvalidArgumentException If a value from $acceptedCardinalities is not a value from the Cardinality enumeration.
     */
    public function setAcceptedCardinalities(array $acceptedCardinalities): void
    {
        foreach ($acceptedCardinalities as $cardinality) {
            if (!in_array($cardinality, OperatorCardinality::asArray(), true)) {
                $msg = "Accepted cardinalities must be values from the Cardinality enumeration, '" . $cardinality . "' given";
                throw new InvalidArgumentException($msg);
            }
        }

        $this->acceptedCardinalities = $acceptedCardinalities;
    }

    /**
     * Get the accepted operand baseTypes.
     *
     * @return array An array of values from OperatorBaseType enumeration.
     */
    public function getAcceptedBaseTypes(): array
    {
        return $this->acceptedBaseTypes;
    }

    /**
     * Set the accepted operand accepted baseTypes.
     *
     * @param array $acceptedBaseTypes An array of values from the OperatorBaseType enumeration.
     * @throws InvalidArgumentException If a value from the $acceptedBaseTypes is not a value from the OperatorBaseType.
     */
    public function setAcceptedBaseTypes(array $acceptedBaseTypes): void
    {
        foreach ($acceptedBaseTypes as $baseType) {
            if (!in_array($baseType, OperatorBaseType::asArray(), true)) {
                $msg = "Accepted baseTypes must be values from the OperatorBaseType enumeration, '" . $baseType . "' given.";
                throw new InvalidArgumentException($msg);
            }
        }

        $this->acceptedBaseTypes = $acceptedBaseTypes;
    }

    /**
     * Checks whether this expression is pure.
     *
     * @return bool
     */
    public function isPure(): bool
    {
        return $this->getExpressions()->isPure();
    }
}
