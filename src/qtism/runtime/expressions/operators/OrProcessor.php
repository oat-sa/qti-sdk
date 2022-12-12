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

namespace qtism\runtime\expressions\operators;

use qtism\common\datatypes\QtiBoolean;
use qtism\data\expressions\operators\OrOperator;
use qtism\runtime\common\Container;

/**
 * The OrProcessor class aims at processing OrOperator QTI Data Model Expression objects.
 *
 * Developer's note: IMS does not explain what happens when one or more sub-expressions are NULL
 * but not all sub-expressions are false. In this implementation, we consider that NULL is returned
 * if one ore more sub-expressions are NULL.
 *
 * From IMS QTI:
 *
 * The or operator takes one or more sub-expressions each with a base-type of boolean and single cardinality.
 * The result is a single boolean which is true if any of the sub-expressions are true and false if all of them are
 * false. If one or more sub-expressions are NULL and all the others are false then the operator also results in NULL.
 */
class OrProcessor extends OperatorProcessor
{
    /**
     * Process the current expression.
     *
     * @return QtiBoolean True if the expression is true, false otherwise.
     * @throws OperatorProcessingException
     */
    public function process(): ?QtiBoolean
    {
        $operands = $this->getOperands();
        $allFalse = true;

        foreach ($operands as $op) {
            if ($op instanceof Container) {
                $msg = 'The Or Expression only accept operands with single cardinality.';
                throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_CARDINALITY);
            } elseif ($op === null) {
                continue;
            } elseif (!$op instanceof QtiBoolean) {
                $msg = 'The Or Expression only accept operands with boolean baseType.';
                throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_BASETYPE);
            } elseif ($op->getValue() !== false) {
                $allFalse = false;
            }
        }

        if ($allFalse === true && $operands->containsNull() === true) {
            return null;
        }

        foreach ($operands as $operand) {
            if ($operand !== null && $operand->getValue() === true) {
                return new QtiBoolean(true);
            }
        }

        return new QtiBoolean(false);
    }

    /**
     * @return string
     */
    protected function getExpressionType(): string
    {
        return OrOperator::class;
    }
}
