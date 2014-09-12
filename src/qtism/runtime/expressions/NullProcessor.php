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

namespace qtism\runtime\expressions;

use qtism\data\expressions\Expression;
use qtism\data\expressions\NullValue;

/**
 * The NullProcessor class aims at processing NullValue QTI DataModel expressions.
 *
 * From IMS QTI:
 *
 * null is a simple expression that returns the NULL value - the null value is
 * treated as if it is of any desired baseType.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class NullProcessor extends ExpressionProcessor
{
    /**
	 * Returns NULL.
	 *
	 * @return null The null value.
	 */
    public function process()
    {
        return null;
    }
    
    /**
     * @see \qtism\runtime\expressions\ExpressionProcessor::getExpressionType()
     */
    protected function getExpressionType()
    {
        return 'qtism\\data\\expressions\\NullValue';
    }
}
