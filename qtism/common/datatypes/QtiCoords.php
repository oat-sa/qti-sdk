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
use qtism\common\collections\IntegerCollection;
use qtism\common\Comparable;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;

/**
 * Represents the QTI Coords Datatype.
 */
class QtiCoords extends IntegerCollection implements QtiDatatype, Comparable
{
    /**
     * A value from the Shape enumeration.
     *
     * @var integer
     */
    private $shape;

    /**
     * Create a new Coords object.
     *
     * @param integer $shape A value from the Shape enumeration.
     * @param array $coords An array of number values.
     * @throws InvalidArgumentException If an error occurs while creating the Coords object.
     */
    public function __construct($shape, array $coords = [])
    {
        parent::__construct($coords);
        $this->setShape($shape);

        switch ($this->getShape()) {
            case QtiShape::DEF:
                if (count($this->getDataPlaceHolder()) > 0) {
                    $msg = "No coordinates should be given when the default shape is used.";
                    throw new InvalidArgumentException($msg);
                }
                break;

            case QtiShape::RECT:
                if (count($this->getDataPlaceHolder()) != 4) {
                    $msg = "The rectangle coordinates must be composed by 4 values (x1, y1, x2, y2).";
                    throw new InvalidArgumentException($msg);
                }
                break;

            case QtiShape::CIRCLE:
                if (count($this->getDataPlaceHolder()) != 3) {
                    $msg = "The circle coordinates must be composed by 3 values (x, y, r).";
                    throw new InvalidArgumentException($msg);
                }
                break;

            case QtiShape::POLY:
                if (count($this->getDataPlaceHolder()) % 2 > 0) {
                    $msg = "The polygon coordinates must be composed by a pair amount of values (x1, y1, x2, y2, ...).";
                    throw new InvalidArgumentException($msg);
                }
                break;
        }
    }

    /**
     * Set the $shape on which the coordinates apply.
     *
     * @param integer $shape A value from the Shape enumeration.
     * @throws InvalidArgumentException
     */
    protected function setShape($shape)
    {
        if (in_array($shape, QtiShape::asArray())) {
            $this->shape = $shape;
        } else {
            $msg = "The shape argument must be a value from the Shape enumeration except 'default', '" . $shape . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the shape on which the coordinates apply.
     *
     * @return integer A value from the Shape enumeration.
     *
     */
    public function getShape()
    {
        return $this->shape;
    }

    /**
     * Whether the given $point is inside the coordinates.
     *
     * @param QtiPoint $point A QtiPoint object.
     * @return boolean
     */
    public function inside(QtiPoint $point)
    {
        if ($this->getShape() === QtiShape::DEF) {
            return true;
        } elseif ($this->getShape() === QtiShape::RECT) {
            return $point->getX() >= $this[0] && $point->getX() <= $this[2] && $point->getY() >= $this[1] && $point->getY() <= $this[3];
        } elseif ($this->getShape() === QtiShape::CIRCLE) {
            return pow($point->getX() - $this[0], 2) + pow($point->getY() - $this[1], 2) < pow($this[2], 2);
        } else {
            // we consider it is a polygon.
            // - Transform coordinates in vertices.
            // -- Use of the "point in polygon" algorithm.
            $vertices = [];
            for ($i = 0; $i < count($this); $i++) {
                $vertex = [];
                $vertex[] = $this[$i]; //x
                $i++;
                $vertex[] = $this[$i]; //y

                $vertices[] = $vertex;
            }

            $intersects = 0;
            for ($i = 1; $i < count($vertices); $i++) {
                $vertex1 = $vertices[$i - 1];
                $vertex2 = $vertices[$i];

                if ($vertex1[1] === $vertex2[1] && $vertex1[1] === $point->getY() && $point->getX() > min($vertex1[0], $vertex2[0]) && $point->getX() < max($vertex1[0], $vertex2[0])) {
                    // we are on a boundary.
                    return true;
                }

                if ($point->getY() > min($vertex1[1], $vertex2[1]) && $point->getY() <= max($vertex1[1], $vertex2[1]) && $point->getX() <= max($vertex1[0], $vertex2[0]) && $vertex1[1] !== $vertex2[1]) {
                    $xinters = ($point->getY() - $vertex1[1]) * ($vertex2[0] - $vertex1[0]) / ($vertex2[1] - $vertex1[1]) + $vertex1[0];

                    if ($xinters === $point->getX()) {
                        // Again, we are on a boundary.
                        return true;
                    }

                    if ($vertex1[0] === $vertex2[0] || $point->getX() <= $xinters) {
                        // We have a single intersection.
                        $intersects++;
                    }
                }
            }

            // If we passed through an odd number of edges, we are in the polygon!
            return $intersects % 2 !== 0;
        }
    }

    /**
     * Return all the points of the coordinates, separated by commas (,).
     *
     * @return string
     */
    public function __toString()
    {
        return implode(",", $this->getDataPlaceHolder());
    }

    /**
     * Whether or not $obj is equals to $this. Two Coords objects are
     * considered to be equal if they have the same coordinates and shape.
     *
     * @param mixed $obj
     * @return boolean
     */
    public function equals($obj)
    {
        return $obj instanceof QtiCoords && $this->getShape() === $obj->getShape() && $this->getArrayCopy() == $obj->getArrayCopy();
    }

    /**
     * Get the baseType of the value. This method systematically returns
     * BaseType::COORDS.
     *
     * @return integer A value from the BaseType enumeration.
     */
    public function getBaseType()
    {
        return BaseType::COORDS;
    }

    /**
     * Get the cardinality of the value. This method systematically returns
     * Cardinality::SINGLE.
     *
     * @return integer A value from the Cardinality enumeration.
     */
    public function getCardinality()
    {
        return Cardinality::SINGLE;
    }
}
