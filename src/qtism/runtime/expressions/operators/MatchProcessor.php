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

use qtism\common\Comparable;
use qtism\common\datatypes\QtiBoolean;
use qtism\data\expressions\operators\Match;

/**
 * The MatchProcessor class aims at processing Match QTI Data Model Expression objects.
 *
 * From IMS QTI:
 *
 * The match operator takes two sub-expressions which must both have the same
 * base-type and cardinality. The result is a single boolean with a value of
 * true if the two expressions represent the same value and false if they do not.
 * If either sub-expression is NULL then the operator results in NULL.
 *
 * The match operator must not be confused with broader notions of equality such as numerical equality.
 * To avoid confusion, the match operator should not be used to compare subexpressions with base-types
 * of float and must not be used on sub-expressions with a base-type of duration.
 */
class MatchProcessor extends OperatorProcessor
{
    /**
     * Process the Match Expression object.
     *
     * @return QtiBoolean|null Whether the two expressions represent the same value or NULL if either of the sub-expressions is NULL.
     * @throws OperatorProcessingException
     */
    public function process()
    {
        $operands = $this->getOperands();
        $expression = $this->getExpression();

        if ($operands->containsNull() === true) {
            return null;
        }

        if ($operands->sameCardinality() === false) {
            $msg = 'The Match Expression only accepts operands with the same cardinality.';
            throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_CARDINALITY);
        }

        if ($operands->sameBaseType() === false) {
            $msg = 'The Match Expression only accepts operands with the same baseType.';
            throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_BASETYPE);
        }

        $firstOperand = $operands[0];
        $secondOperand = $operands[1];

        if ($operands[0] instanceof Comparable) {
            // 2 containers to compare.
            return new QtiBoolean($operands[0]->equals($operands[1]));
        } else {
            return new QtiBoolean($operands[0] === $operands[1]);
        }
    }

    /**
     * @see \qtism\runtime\expressions\ExpressionProcessor::getExpressionType()
     */
    protected function getExpressionType()
    {
        return Match::class;
    }
}
