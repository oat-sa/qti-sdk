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

namespace qtism\runtime\expressions\operators;

use InvalidArgumentException;
use qtism\data\expressions\Expression;
use qtism\runtime\expressions\ExpressionProcessor;

/**
 * Class OperatorProcessor
 */
abstract class OperatorProcessor extends ExpressionProcessor
{
    /**
     * A collection of QTI Runtime compliant values.
     *
     * @var OperandsCollection
     */
    private $operands;

    /**
     * Create a new OperatorProcessor object.
     *
     * @param Expression $expression A QTI Data Model Operator object.
     * @param OperandsCollection $operands A collection of QTI Runtime compliant values.
     * @throws InvalidArgumentException If $expression is not a QTI Data Model Operator object.
     */
    public function __construct(Expression $expression, OperandsCollection $operands)
    {
        parent::__construct($expression);
        $this->setOperands($operands);
    }

    /**
     * Set the collection of QTI Runtime compliant values
     * to be used as the operands of the Operator to be processed.
     *
     * @param OperandsCollection $operands A collection of QTI Runtime compliant values.
     * @throws OperatorProcessingException If The operands are not compliant with minimum or maximum amount of operands the operator can take.
     */
    public function setOperands(OperandsCollection $operands): void
    {
        // Check minimal operand count.
        $min = $this->getExpression()->getMinOperands();
        $given = count($operands);

        if ($given < $min) {
            $msg = "The Operator to be processed '" . get_class($this) . "' requires at least ${min} operand(s). ";
            $msg .= "${given} operand(s) given.";
            throw new OperatorProcessingException($msg, $this, OperatorProcessingException::NOT_ENOUGH_OPERANDS);
        }

        // Check maximal operand count.
        $max = $this->getExpression()->getMaxOperands();
        $given = count($operands);

        if ($max !== -1 && $given > $max) {
            $msg = "The Operator to be processed '" . get_class($this) . "' requires at most ${max} operand(s). ";
            $msg .= "${given} operand(s) given.";
            throw new OperatorProcessingException($msg, $this, OperatorProcessingException::TOO_MUCH_OPERANDS);
        }

        $this->operands = $operands;
    }

    /**
     * Get the collection of QTI Runtime compliant values to be used
     * as the operands of the Operator to be processed.
     *
     * @return OperandsCollection A collection of QTI Runtime compliant values.
     */
    public function getOperands(): OperandsCollection
    {
        return $this->operands;
    }
}
