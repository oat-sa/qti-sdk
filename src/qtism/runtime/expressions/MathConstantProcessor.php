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

namespace qtism\runtime\expressions;

use qtism\common\datatypes\QtiFloat;
use qtism\data\expressions\MathConstant;
use qtism\data\expressions\MathEnumeration;

/**
 * The MathConstant processor aims at processing QTI Data Model MathConstant expressions.
 *
 * From IMS QTI:
 *
 * The result is a mathematical constant returned as a single float, e.g. π and e.
 */
class MathConstantProcessor extends ExpressionProcessor
{
    /**
     * Process the MathConstant Expression.
     *
     * @return QtiFloat A float value (e or pi).
     */
    public function process()
    {
        $expr = $this->getExpression();
        if ($expr->getName() === MathEnumeration::E) {
            return new QtiFloat(M_E);
        } else {
            return new QtiFloat(M_PI);
        }
    }

    /**
     * @return string
     */
    protected function getExpressionType()
    {
        return MathConstant::class;
    }
}
