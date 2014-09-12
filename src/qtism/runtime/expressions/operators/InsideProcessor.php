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
 * Copyright (c) 2013-2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 *
 */

namespace qtism\runtime\expressions\operators;

use qtism\common\datatypes\Boolean;
use qtism\data\expressions\operators\Inside;
use qtism\data\expressions\Expression;

/**
 * The InsideProcessor class aims at processing Inside operators.
 *
 * From IMS QTI:
 *
 * The inside operator takes a single sub-expression which must have a baseType of
 * point. The result is a single boolean with a value of true if the given point is
 * inside the area defined by shape and coords. If the sub-expression is a container
 * the result is true if any of the points are inside the area. If either
 * sub-expression is NULL then the operator results in NULL.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class InsideProcessor extends OperatorProcessor
{
    /**
	 * Process the Inside operator.
	 *
	 * @return boolean|null Whether the given point is inside the area defined by shape and coords or NULL if the sub-expression is NULL.
	 * @throws \qtism\runtime\expressions\operators\OperatorProcessingException
	 */
    public function process()
    {
        $operands = $this->getOperands();

        if ($operands->containsNull() === true) {
            return null;
        }

        if ($operands->exclusivelySingle() === false) {
            $msg = "The Inside operator only accepts operands with a single cardinality.";
            throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_CARDINALITY);
        }

        if ($operands->exclusivelyPoint() === false) {
            $msg = "The Inside operator only accepts operands with a baseType of point.";
            throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_BASETYPE);
        }

        $operand = $operands[0];
        $coords = $this->getExpression()->getCoords();

        return new Boolean($coords->inside($operand));
    }
    
    /**
     * @see \qtism\runtime\expressions\ExpressionProcessor::getExpressionType()
     */
    protected function getExpressionType()
    {
        return 'qtism\\data\\expressions\\operators\\Inside';
    }
}
