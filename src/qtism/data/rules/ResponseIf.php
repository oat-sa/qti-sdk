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
 * Copyright (c) 2013-2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\rules;

use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use qtism\data\expressions\Expression;
use qtism\data\QtiPLisable;

/**
 * From IMS QTI:
 *
 * A responseIf part consists of an expression which must have an effective
 * baseType of boolean and single cardinality. For more information about the
 * runtime data model employed see Expressions. It also contains a set of
 * sub-rules. If the expression is true then the sub-rules are processed,
 * otherwise they are skipped (including if the expression is NULL) and the
 * following responseElseIf or responseElse parts (if any) are considered
 * instead.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ResponseIf extends QtiComponent implements QtiPLisable
{
    /**
	 * The expression to be evaluated with the If statement.
	 *
	 * @var \qtism\data\expressions\Expression
	 * @qtism-bean-property
	 */
    private $expression;

    /**
	 * The sub rules to execute if the Expression returns true;
	 *
	 * @var \qtism\data\rules\ResponseRuleCollection
	 * @qtism-bean-property
	 */
    private $responseRules;

    /**
	 * Create a new instance of ResponseIf.
	 *
	 * @param \qtism\data\expressions\Expression $expression The expression to be evaluated with the If statement.
	 * @param \qtism\data\rules\ResponseRuleCollection $responseRules A collection of sub expressions to be evaluated if the Expression returns true.
	 */
    public function __construct(Expression $expression, ResponseRuleCollection $responseRules)
    {
        $this->setExpression($expression);
        $this->setResponseRules($responseRules);
    }

    /**
	 * Get the expression to be evaluated with the If statement.
	 *
	 * @return \qtism\data\expressions\Expression An expression.
	 */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
	 * Set the expression to be evaluated with the If statement.
	 *
	 * @param \qtism\data\expressions\Expression $expression An expression.
	 */
    public function setExpression(Expression $expression)
    {
        $this->expression = $expression;
    }

    /**
	 * Set the ResponseRule objects to be evaluated as sub expressions if the expression
	 * evaluated with the If statement returns true.
	 *
	 * @param \qtism\data\rules\ResponseRuleCollection $responseRules A collection of ResponseRule objects.
	 */
    public function setResponseRules(ResponseRuleCollection $responseRules)
    {
        $this->responseRules = $responseRules;
    }

    /**
	 * Get the ResponseRule objects to be evaluated as sub expressions if the expression
	 * evaluated with the If statement returns true.
	 *
	 * @return \qtism\data\rules\ResponseRuleCollection A collection of ResponseRule objects.
	 */
    public function getResponseRules()
    {
        return $this->responseRules;
    }

    /**
	 * @see \qtism\data\QtiComponent::getQtiClassName()
	 */
    public function getQtiClassName()
    {
        return 'responseIf';
    }

    /**
     * @see \qtism\data\QtiComponent::getComponents()
     */
    public function getComponents()
    {
        $comp = array_merge(
            array($this->getExpression()),
            $this->getResponseRules()->getArrayCopy()
        );

        return new QtiComponentCollection($comp);
    }

    /**
     * Transforms this QtiComponent into a Qti-PL string.
     *
     *@return string A Qti-PL representation of the QtiComponent
     */
    public function toQtiPL()
    {
        return "if (" . $this->getExpression()->toQtiPL() . ") {\n" . $this->getResponseRules()->toQtiPL() . "}";
    }
}
