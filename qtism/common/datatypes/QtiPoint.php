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

namespace qtism\common\datatypes;

use InvalidArgumentException;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;

/**
 * Represents the QTI Point datatype.
 *
 * From IMS QTI:
 *
 * A point value represents an integer tuple corresponding to a
 * graphic point. The two integers correspond to the horizontal (x-axis)
 * and vertical (y-axis) positions respectively. The up/down and
 * left/right senses of the axes are context dependent.
 */
class QtiPoint implements QtiDatatype
{
    /**
     * The position on the x-axis.
     *
     * @var int
     */
    private $x;

    /**
     * The position on the y-axis.
     *
     * @var int
     */
    private $y;

    /**
     * Create a new Point object.
     *
     * @param int $x A position on the x-axis.
     * @param int $y A position on the y-axis.
     * @throws InvalidArgumentException If $x or $y are not integer values.
     */
    public function __construct($x, $y)
    {
        $this->setX($x);
        $this->setY($y);
    }

    /**
     * Set the position on the x-axis.
     *
     * @param int $x A position on the x-axis.
     * @throws InvalidArgumentException If $x is nto an integer value.
     */
    public function setX($x)
    {
        if (is_int($x)) {
            $this->x = $x;
        } else {
            $msg = "The X argument must be an integer value, '" . gettype($x) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the position on the x-axis.
     *
     * @return int A position on the x-axis.
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * Set the position on y-axis.
     *
     * @param int $y A position on the y-axis.
     * @throws InvalidArgumentException If $y is not an integer value.
     */
    public function setY($y)
    {
        if (is_int($y)) {
            $this->y = $y;
        } else {
            $msg = "The Y argument must be an integer value, '" . gettype($x) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the position on the y-axis.
     *
     * @return int A position on the y-axis.
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * Whether a given $obj is equal to this Point object. Two Point objects
     * are considered to be the same if they have the same coordinates.
     *
     * @param mixed $obj An object.
     * @return bool Whether or not the equality is established.
     */
    public function equals($obj)
    {
        return (is_object($obj) &&
            $obj instanceof self &&
            $obj->getX() === $this->getX() &&
            $obj->getY() === $this->getY());
    }

    /**
     * Returns a string representation of the Point object
     * e.g. "20 30" for a Point object where 20 is the value
     * of X and 30 is the value of Y.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getX() . ' ' . $this->getY();
    }

    /**
     * Get the BaseType of the value. This method systematically returns
     * the BaseType::POINT value.
     *
     * @return int A value from the BaseType enumeration.
     */
    public function getBaseType()
    {
        return BaseType::POINT;
    }

    /**
     * Get the Cardinality of the value. This method systematically returns
     * the Cardinality::SINGLE value.
     *
     * @return int A value from the Cardinality enumeration.
     */
    public function getCardinality()
    {
        return Cardinality::SINGLE;
    }
}
