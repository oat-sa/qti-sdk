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

use qtism\runtime\common\Utils;
use qtism\runtime\expressions\ExpressionEngine;
use qtism\data\rules\TemplateConstraint;

/**
 * From IMS QTI:
 *
 * A templateConstraint contains an expression which must have an effective baseType of boolean and single cardinality.
 * For more information about the runtime data model employed see Expressions. If the expression is false (including if
 * the expression is NULL), the template variables are set to their default values and templateProcessing is restarted;
 * this happens repeatedly until the expression is true or the maximum number of iterations is reached. In the event
 * that the maximum number of iterations is reached, any default values provided for the variables during declaration
 * are used. Processing then continues with the next templateRule after the templateConstraint, or finishes if
 * there are no further templateRules.
 *
 * By using a templateConstraint, authors can ensure that the values of variables set during templateProcessing satisfy
 * the condition specified by the boolean expression. For example, two randomly selected numbers might be required
 * which have no common factors.
 *
 * A templateConstraint may occur anywhere as a child of templateProcessing. It may not be used as a child of any other
 * element. Any number of templateConstraints may be used, though two or more consecutive templateConstraints could
 * be combined using the 'and' element to combine their boolean expressions.
 *
 * The maximum number of times that the operations preceding the templateConstraint can be expected to be performed
 * is assumed to be 100; implementations may permit more iterations, but there must be a finite maximum number of
 * iterations. This prevents the occurrence of an endless loop. It is the responsibility of the author to provide
 * default values for any variables assigned under a templateConstraint.
 */
class TemplateConstraintProcessor extends RuleProcessor
{
    /**
     * Process the TemplateConstraint rule. It simply throws a RuleProcessingException with
     * the special code RuleProcessingException::TEMPLATE_CONSTRAINT_UNSATISFIED to warn client
     * code that the expression related to the constraint returned false or null.
     *
     * @throws RuleProcessingException with code = RuleProcessingException::TEMPLATE_CONSTRAINT_UNSATISFIED.
     */
    public function process()
    {
        $state = $this->getState();
        $rule = $this->getRule();
        $expr = $rule->getExpression();

        $expressionEngine = new ExpressionEngine($expr, $state);
        $val = $expressionEngine->process();

        if (Utils::isNull($val) || $val->getValue() === false) {
            $msg = 'Unsatisfied Template Constraint.';
            throw new RuleProcessingException($msg, $this, RuleProcessingException::TEMPLATE_CONSTRAINT_UNSATISFIED);
        }
    }

    protected function getRuleType()
    {
        return TemplateConstraint::class;
    }
}
