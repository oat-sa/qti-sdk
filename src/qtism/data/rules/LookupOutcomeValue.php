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
use qtism\common\utils\Format;
use \InvalidArgumentException;

/**
 * From IMS QTI:
 *
 * The lookupOutcomeValue rule sets the value of an outcome variable to the value obtained
 * by looking up the value of the associated expression in the lookupTable associated with
 * the outcome's declaration.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class LookupOutcomeValue extends QtiComponent implements OutcomeRule, ResponseRule
{
    /**
	 * The identifier of the outcome variable to set.
	 *
	 * @var string
	 * @qtism-bean-property
	 */
    private $identifier;

    /**
	 * From IMS QTI:
	 *
	 * An expression which must have single cardinality and an effective baseType of
	 * either integer, float or duration. Integer type is required when the associated
	 * table is a matchTable.
	 *
	 * @var \qtism\data\expressions\Expression
	 * @qtism-bean-property
	 */
    private $expression;

    /**
	 * Create a new instance of LookupOutcomeValue.
	 *
	 * @param string $identifier The identifier of the outcome variable to set.
	 * @param \qtism\data\expressions\Expression $expression An expression which must have single cardinality and an effective baseType of either integer, float or duration.
	 * @throws \InvalidArgumentException If $identifier is not a valid QTI Identifier.
	 */
    public function __construct($identifier, Expression $expression)
    {
        $this->setIdentifier($identifier);
        $this->setExpression($expression);
    }

    /**
	 * Get the identifier of the outcome variable to set.
	 *
	 * @return string A QTI Identifier.
	 */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
	 * Set the identifier of the outcome variable to set.
	 *
	 * @param string $identifier A QTI Identifier.
	 * @throws \InvalidArgumentException If $identifier is not a valid QTI Identifier.
	 */
    public function setIdentifier($identifier)
    {
        if (Format::isIdentifier($identifier, false)) {
            $this->identifier = $identifier;
        } else {
            $msg = "Identifier must be a vali QTI Identifier.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
	 * Get the expression.
	 *
	 * @return \qtism\data\expressions\Expression A QTI Expression object.
	 */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
	 * Set the expression.
	 *
	 * @param \qtism\data\expressions\Expression $expression A QTI Expression object.
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
        return 'lookupOutcomeValue';
    }

    /**
	 * @see \qtism\data\QtiComponent::getComponents()
	 */
    public function getComponents()
    {
        $comp = array($this->getExpression());

        return new QtiComponentCollection($comp);
    }

    /**
     * Transforms this rule into a Qti-PL string.
     *
     *@return string A Qti-PL representation of the rule
     */
    public function toQtiPL()
    {
        $qtipl = $this->getQtiClassName() . "[identifier=\"" . $this->getIdentifier() . "\"]";
        return $qtipl . "(" . $this->expression->toQtiPL() . ")";
    }
}
