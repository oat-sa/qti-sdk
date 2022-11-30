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

namespace qtism\runtime\rules;

use qtism\data\rules\TemplateCondition;

/**
 * From IMS QTI:
 *
 * If the expression given in the templateIf or templateElseIf evaluates to true then the sub-rules containe
 * within it are followed and any following templateElseIf or templateElse parts are ignored for this template
 * condition.
 *
 * If the expression given in the templateIf or templateElseIf does not evaluate to true then consideration
 * passes to the next templateElseIf or, if there are no more templateElseIf parts then the sub-rules of
 * the templateElse are followed (if specified).
 */
class TemplateConditionProcessor extends AbstractConditionProcessor
{
    /**
     * @return string
     */
    public function getQtiNature(): string
    {
        return 'template';
    }

    /**
     * @return string
     */
    protected function getRuleType(): string
    {
        return TemplateCondition::class;
    }
}
