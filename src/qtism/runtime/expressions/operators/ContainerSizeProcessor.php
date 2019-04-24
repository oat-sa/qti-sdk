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

use qtism\common\datatypes\QtiInteger;
use qtism\data\expressions\Expression;
use qtism\data\expressions\operators\ContainerSize;

/**
 * The ContainerSizeProcessor class aims at processing ContainerSize QTI Data Model Expression objects.
 *
 * From IMS QTI:
 *
 * The containerSize operator takes a sub-expression with any base-type and either multiple or ordered cardinality.
 * The result is an integer giving the number of values in the sub-expression, in other words, the size of the container.
 * If the sub-expression is NULL the result is 0. This operator can be used for determining how many choices were selected
 * in a multiple-response choiceInteraction, for example.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ContainerSizeProcessor extends OperatorProcessor
{
    /**
	 * Process the current expression.
	 *
	 * @return QtiInteger|null The size of the container or null if it contains NULL.
	 * @throws \qtism\runtime\expressions\operators\OperatorProcessingException
	 */
    public function process()
    {
        $operands = $this->getOperands();

        if ($operands->containsNull() === true) {
            return new QtiInteger(0);
        }

        if ($operands->exclusivelyMultipleOrOrdered() === false) {
            $msg = "The ContainerSize operator only accepts operands with a multiple or ordered cardinality.";
            throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_CARDINALITY);
        }

        return new QtiInteger(count($operands[0]));
    }
    
    /**
     * @see \qtism\runtime\expressions\ExpressionProcessor::getExpressionType()
     */
    protected function getExpressionType()
    {
        return 'qtism\\data\\expressions\\operators\\ContainerSize';
    }
}
