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

namespace qtism\runtime\rules;

use qtism\data\rules\SetOutcomeValue;
use qtism\runtime\common\OutcomeVariable;

/**
 * From IMS QTI:
 *
 * The setOutcomeValue rule sets the value of an outcome variable to the value
 * obtained from the associated expression. An outcome variable can be updated with
 * reference to a previously assigned value, in other words, the outcome variable
 * being set may appear in the expression where it takes the value previously assigned
 * to it.
 *
 * Special care is required when using the numeric base-types because floating point
 * values can not be assigned to integer variables and vice-versa. The truncate,
 * round or integerToFloat operators must be used to achieve numeric type conversion.
 */
class SetOutcomeValueProcessor extends SetValueProcessor
{
    /**
     * @return string
     */
    protected function getRuleType(): string
    {
        return SetOutcomeValue::class;
    }

    /**
     * @inheritDoc
     */
    protected function getVariableType(): string
    {
        return OutcomeVariable::class;
    }
}
