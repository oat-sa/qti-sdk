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

use qtism\data\QtiPLisable;
use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use qtism\data\expressions\Expression;

/**
 * A preCondition is a simple expression attached to an assessmentSection or assessmentItemRef
 * that must evaluate to true if the item is to be presented. Pre-conditions are evaluated at
 * the time the associated item, section or testPart is to be attempted by the candidate,
 * during the test. They differ from rules for selection and ordering (see Test Structure)
 * which are followed at or before the start of the test.
 *
 * If the expression evaluates to false, or has a NULL value, the associated item or section
 * is skipped.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class PreCondition extends QtiComponent implements QtiPLisable
{
    /**
	 * The expression that will make the Precondition return true or false.
	 *
	 * @var \qtism\data\expressions\Expression
	 * @qtism-bean-property
	 */
    private $expression;

    /**
	 * Create a new instance of PreCondition.
	 *
	 * @param \qtism\data\expressions\Expression $expression
	 */
    public function __construct(Expression $expression)
    {
        $this->setExpression($expression);
    }

    /**
	 * Get the expression of the PreCondition.
	 *
	 * @return \qtism\data\expressions\Expression A QTI Expression.
	 */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
	 * Set the expression of the Precondition.
	 *
	 * @param \qtism\data\expressions\Expression $expression A QTI Expression.
	 */
    public function setExpression(Expression $expression)
    {
        $this->expression = $expression;
    }

    /**
	 * @see \qtism\data\QtiComponent::getQtiClassName()
	 */
    public function getQtiClassName()
    {
        return 'preCondition';
    }

    /**
	 * @see \qtism\data\QtiComponent::getComponents()
	 */
    public function getComponents()
    {
        return new QtiComponentCollection(array($this->getExpression()));
    }

    /**
     * Transforms this preCondition into a Qti-PL string.
     *
     *@return string A Qti-PL representation of the preCondition
     */
    public function toQtiPL()
    {
        return $this->getQtiClassName() . "(" . $this->getExpression()->toQtiPL() . ")";
    }
}
