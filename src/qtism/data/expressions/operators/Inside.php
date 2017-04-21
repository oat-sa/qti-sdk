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

use qtism\common\datatypes\QtiShape;
use qtism\common\datatypes\QtiCoords;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\Pure;
use \InvalidArgumentException;

/**
 * From IMS QTI:
 *
 * The inside operator takes a single sub-expression which must have a baseType of point.
 * The result is a single boolean with a value of true if the given point is inside the
 * area defined by shape and coords. If the sub-expression is a container the result is
 * true if any of the points are inside the area. If either sub-expression is NULL then
 * the operator results in NULL.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Inside extends Operator implements Pure
{
    /**
	 * From IMS QTI:
	 *
	 * The shape of the area.
	 *
	 * @var int
	 * @qtism-bean-property
	 */
    private $shape;

    /**
	 * From IMS QTI:
	 *
	 * The size and position of the area, interpreted in conjunction with the shape.
	 *
	 * @var \qtism\common\datatypes\Coords
	 * @qtism-bean-property
	 */
    private $coords;

    /**
	 * Create a new Inside object.
	 *
	 * @param \qtism\data\expressions\ExpressionCollection $expressions A collection of Expression objects.
	 * @param int $shape A value from the Shape enumeration
	 * @param \qtism\common\datatypes\Coords $coords A Coords object as the size and position of the area, interpreted in conjunction with $shape.
	 * @throws \InvalidArgumentException If the $expressions count exceeds 1 or if $shape is not a value from the Shape enumeration.
	 */
    public function __construct(ExpressionCollection $expressions, $shape, QtiCoords $coords)
    {
        parent::__construct($expressions, 1, 1, array(OperatorCardinality::SINGLE, OperatorCardinality::MULTIPLE, OperatorCardinality::ORDERED), array(OperatorBaseType::POINT));
        $this->setShape($shape);
        $this->setCoords($coords);
    }

    /**
	 * Set the shape.
	 *
	 * @param int $shape A value from the Shape enumeration.
	 * @throws \InvalidArgumentException If $shape is not a value from the Shape enumeration.
	 */
    public function setShape($shape)
    {
        if (in_array($shape, QtiShape::asArray())) {
            $this->shape = $shape;
        } else {
            $msg = "The shape argument must be a value from the Shape enumeration, '" . $shape . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
	 * Get the shape.
	 *
	 * @return int A value from the Shape enumeration.
	 */
    public function getShape()
    {
        return $this->shape;
    }

    /**
	 * Set the coordinates.
	 *
	 * @param \qtism\common\datatypes\Coords $coords A Coords object.
	 */
    public function setCoords(QtiCoords $coords)
    {
        $this->coords = $coords;
    }

    /**
	 * Get the coordinates
	 *
	 * @return \qtism\common\datatypes\Coords A Coords object.
	 */
    public function getCoords()
    {
        return $this->coords;
    }

    /**
	 * @see \qtism\data\QtiComponent::getQtiClassName()
	 */
    public function getQtiClassName()
    {
        return 'inside';
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
}
