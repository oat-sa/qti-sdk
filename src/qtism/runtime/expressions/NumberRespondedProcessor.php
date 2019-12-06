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
 * Copyright (c) 2013-2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 *
 */

namespace qtism\runtime\expressions;

use qtism\common\datatypes\QtiInteger;
use qtism\data\expressions\NumberResponded;

/**
 * The NumberRespondedProcessor aims at processing NumberResponded
 * Outcome Processing only expressions.
 *
 * From IMS QTI:
 *
 * This expression, which can only be used in outcomes processing, calculates the number of
 * items in a given sub-set that have been attempted (at least once) and for which a response
 * was given. In other words, items for which at least one declared response has a value
 * that differs from its declared default (typically NULL). The result is an integer with
 * single cardinality.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class NumberRespondedProcessor extends ItemSubsetProcessor
{
    /**
	 * Process the related NumberResponded expression.
	 *
	 * @return QtiInteger The number of items in the given sub-set that been attempted (at least once) and for which a response was given.
	 * @throws \qtism\runtime\expressions\ExpressionProcessingException
	 */
    public function process()
    {
        $testSession = $this->getState();
        $itemSubset = $this->getItemSubset();
        $numberResponded = 0;

        foreach ($itemSubset as $item) {
            $itemSessions = $testSession->getAssessmentItemSessions($item->getIdentifier());
            
            if ($itemSessions !== false) {
                foreach ($itemSessions as $itemSession) {
                    if ($itemSession->isResponded() === true) {
                        $numberResponded++;
                    }
                }    
            }
        }

        return new QtiInteger($numberResponded);
    }
    
    /**
     * @see \qtism\runtime\expressions\ExpressionProcessor::getExpressionType()
     */
    protected function getExpressionType()
    {
        return NumberResponded::class;
    }
}
