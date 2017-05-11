<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * Copyright (c) 2013-2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\rules;

use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;
use qtism\data\QtiPLisable;

/**
 * From IMS QTI:
 *
 * outcomeElseIf is defined in an identical way to outcomeIf.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class OutcomeElseIf extends QtiComponent implements QtiPLisable
{
    /**
	 * The expression to be evaluated with the Else If statement.
	 *
	 * @var \qtism\data\expressions\Expression
	 * @qtism-bean-property
	 */
    private $expression;

    /**
	 * The collection of OutcomRule objects to be evaluated as sub expressions
	 * if the expression bound to the Else If statement is evaluated to true.
	 *
	 * @var \qtism\data\rules\OutcomeRuleCollection
	 * @qtism-bean-property
	 */
    private $outcomeRules;

    /**
	 * Create a new instance of OutcomeElseIf.
	 *
	 * @param \qtism\data\expressions\Expression $expression An expression to be evaluated with the Else If statement.
	 * @param \qtism\data\rules\OutcomeRuleCollection $outcomeRules A collection of OutcomeRule objects.
	 * @throws \InvalidArgumentException If $outcomeRules is an empty collection.
	 */
    public function __construct(Expression $expression, OutcomeRuleCollection $outcomeRules)
    {
        $this->setExpression($expression);
        $this->setOutcomeRules($outcomeRules);
    }

    /**
	 * Get the expression to be evaluated with the Else If statement.
	 *
	 * @return \qtism\data\expressionsExpression An Expression object.
	 */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
	 * Set the expression to be evaluated with the Else If statement.
	 *
	 * @param \qtism\data\expressions\Expression $expression An Expression object.
	 */
    public function setExpression(Expression $expression)
    {
        $this->expression = $expression;
    }

    /**
	 * Get the OutcomeRules to be evaluated as sub expressions if the expression bound
	 * to the Else If statement returns true.
	 *
	 * @return \qtism\data\rules\OutcomeRuleCollection A collection of OutcomeRule objects.
	 */
    public function getOutcomeRules()
    {
        return $this->outcomeRules;
    }

    /**
	 * Set the OutcomeRules to be evaluated as sub expressions if the expression bound
	 * to the Else If statement returns true.
	 *
	 * @param \qtism\data\rules\OutcomeRuleCollection $outcomeRules A collection of OutcomeRule objects.
	 * @throws \InvalidArgumentException If $outcomeRules is an empty collection.
	 */
    public function setOutcomeRules(OutcomeRuleCollection $outcomeRules)
    {
        if (count($outcomeRules) > 0) {
            $this->outcomeRules = $outcomeRules;
        } else {
            $msg = "An OutcomeElseIf object must be bound to at lease one OutcomeRule object.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
	 * @see \qtism\data\QtiComponent::getQtiClassName()
	 */
    public function getQtiClassName()
    {
        return 'outcomeElseIf';
    }

    /**
	 * @see \qtism\data\QtiComponent::getComponents()
	 */
    public function getComponents()
    {
        $comp = array_merge(array($this->getExpression()), $this->getOutcomeRules()->getArrayCopy());

        return new QtiComponentCollection($comp);
    }

    /**
     * Transforms this QtiComponent into a Qti-PL string.
     *
     *@return string A Qti-PL representation of the QtiComponent
     */
    public function toQtiPL()
    {
        return "elseif (" . $this->getExpression()->toQtiPL() . ") {\n" . $this->getOutcomeRules()->toQtiPL() . "}";
    }
}
