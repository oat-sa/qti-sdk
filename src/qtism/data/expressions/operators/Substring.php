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

namespace qtism\data\expressions\operators;

use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\Pure;
use \InvalidArgumentException;

/**
 * From IMS QTI:
 *
 * The substring operator takes two sub-expressions which must both have an effective
 * base-type of string and single cardinality. The result is a single boolean with a
 * value of true if the first expression is a substring of the second expression and
 * false if it isn't. If either sub-expression is NULL then the result of the operator
 * is NULL.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Substring extends Operator implements Pure
{
    /**
	 * From IMS QTI:
	 *
	 * Used to control whether or not the substring is matched case sensitively.
	 * If true then the match is case sensitive and, for example, "Hell" is not
	 * a substring of "Shell". If false then the match is not case sensitive and "Hell"
	 * is a substring of "Shell".
	 *
	 * @var boolean
	 * @qtism-bean-property
	 */
    private $caseSensitive = true;

    /**
	 * Create a new Substring.
	 *
	 * @param ExpressionCollection $expressions A collection of Expression objects.
	 * @param boolean $caseSensitive A boolean value.
	 * @throws \InvalidArgumentException If $caseSensitive is not a boolean or if the count of $expressions is not correct.
	 */
    public function __construct(ExpressionCollection $expressions, $caseSensitive = true)
    {
        parent::__construct($expressions, 2, 2, array(OperatorCardinality::SINGLE), array(OperatorBaseType::STRING));
        $this->setCaseSensitive($caseSensitive);
    }

    /**
	 * Set the caseSensitive attribute.
	 *
	 * @param boolean $caseSensitive A boolean value.
	 * @throws \InvalidArgumentException If $caseSensitive is not a boolean value.
	 */
    public function setCaseSensitive($caseSensitive)
    {
        if (is_bool($caseSensitive)) {
            $this->caseSensitive = $caseSensitive;
        } else {
            $msg = "The caseSensitive argument must be a boolean value, '" . gettype($caseSensitive) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
	 * Wether or not the operator is case sensitive.
	 *
	 * @return boolean
	 */
    public function isCaseSensitive()
    {
        return $this->caseSensitive;
    }

    /**
	 * @see \qtism\data\QtiComponent::getQtiClassName()
	 */
    public function getQtiClassName()
    {
        return 'substring';
    }

    /**
     * Checks whether this expression is pure.
     * @link https://en.wikipedia.org/wiki/Pure_function
     *
     * @return boolean True if the expression is pure, false otherwise
     */
    public function isPure()
    {
        return $this->getExpressions()->isPure();
    }



    /**
     * Transforms this expression into a Qti-PL string.
     *
     *@return string A Qti-PL representation of the expression
     */

    public function toQtiPL()
    {
        $qtipl = $this->getQtiClassName() . "[caseSensitive=" . $this->caseSensitive->toQtiPL();
        $qtipl .= ($this->caseSensitive) ? "" : "[caseSensitive=False]";

        $qtipl.= "](";
        $start = true;

        foreach ($this->getExpressions() as $expr) {

            if ($start) {
                $start = false;
            } else {
                $qtipl .= ", ";
            }

            $qtipl .= $expr->toQtiPL();
        }

        return $qtipl . ")";
    }
}
