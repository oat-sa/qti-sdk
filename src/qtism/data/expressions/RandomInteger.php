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

namespace qtism\data\expressions;

use \InvalidArgumentException;
use qtism\common\utils\Format;

/**
 * From IMS QTI:
 *
 * Selects a random integer from the specified range [min,max] satisfying min + step * n for
 * some integer n. For example, with min=2, max=11 and step=3 the values {2,5,8,11} are possible.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class RandomInteger extends Expression implements Pure
{
    /**
     * The min attribute value.
     *
     * @var integer
     * @qtism-bean-property
     */
    private $min = 0;

    /**
	 * The max attribute value.
	 *
	 * @var integer
	 * @qtism-bean-property
	 */
    private $max;

    /**
	 * The step attribute value.
	 *
	 * @var integer
	 * @qtism-bean-property
	 */
    private $step = 1;

    /**
	 * Create a new instance of RandomInteger.
	 *
	 * @param int|string $min
	 * @param int|string $max
	 * @param int $step
	 * @throws \InvalidArgumentException If $min, $max, or $step are not integers.
	 */
    public function __construct($min, $max, $step = 1)
    {
        $this->setMin($min);
        $this->setMax($max);
        $this->setStep($step);
    }

    /**
	 * Get the value of the min attribute.
	 *
	 * @return integer
	 */
    public function getMin()
    {
        return $this->min;
    }

    /**
	 * Set the value of the min attribute.
	 *
	 * @param integer $min
	 * @throws \InvalidArgumentException
	 */
    public function setMin($min)
    {
        if (is_int($min) || Format::isVariableRef($max)) {
            $this->min = $min;
        } else {
            $msg = "'Min' must be an integer, '" . gettype($min) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
	 * Get the value of the max attribute.
	 *
	 * @return integer
	 */
    public function getMax()
    {
        return $this->max;
    }

    /**
	 * Set the value of the max attribute.
	 *
	 * @param integer $max
	 * @throws \InvalidArgumentException
	 */
    public function setMax($max)
    {
        if (is_int($max) || Format::isVariableRef($max)) {
            $this->max = $max;
        } else {
            $msg = "'Max' must be an integer, '" . gettype($max) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
	 * Get the value of the step attribute.
	 *
	 * @return integer
	 */
    public function getStep()
    {
        return $this->step;
    }

    /**
	 * Set the value of the step attribute.
	 *
	 * @param integer $step
	 * @throws \InvalidArgumentException
	 */
    public function setStep($step)
    {
        if (is_int($step)) {
            $this->step = $step;
        } else {
            $msg = "'Step' must be an integer, '" . gettype($step) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
	 * @see \qtism\data\QtiComponent::getQtiClassName()
	 */
    public function getQtiClassName()
    {
        return 'randomInteger';
    }

    /**
     * Checks whether this expression is pure.
     * @link https://en.wikipedia.org/wiki/Pure_function
     *
     * @return boolean True if the expression is pure, false otherwise
     */
    public function isPure()
    {
        return false; // random --> false
    }
}
