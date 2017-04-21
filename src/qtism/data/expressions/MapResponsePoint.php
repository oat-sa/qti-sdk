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

use qtism\common\utils\Format;
use \InvalidArgumentException;

/**
 * From IMS QTI:
 *
 * This expression looks up the value of a response variable that must be of
 * base-type point, and transforms it using the associated areaMapping. The
 * transformation is similar to mapResponse except that the points are tested
 * against each area in turn. When mapping containers each area can be mapped
 * once only. For example, if the candidate identified two points that both
 * fall in the same area then the mappedValue is still added to the calculated
 * total just once.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class MapResponsePoint extends Expression implements Pure
{
    /**
	 * The QTI Identifier of the associated mapping.
	 *
	 * @var string
	 * @qtism-bean-property
	 */
    private $identifier;

    /**
	 * Create a new instance of MapResponsePoint.
	 *
	 * @param string $identifier A QTI Identifier.
	 * @throws \InvalidArgumentException If $identifier is not a valid QTI Identifier.
	 */
    public function __construct($identifier)
    {
        $this->setIdentifier($identifier);
    }

    /**
	 * Set the QTI Identifier of the associated mapping.
	 *
	 * @param string $identifier A QTI Identifier.
	 * @throws \InvalidArgumentException If $identifier is not a valid QTI Identifier.
	 */
    public function setIdentifier($identifier)
    {
        if (Format::isIdentifier($identifier, false)) {
            $this->identifier = $identifier;
        } else {
            $msg = "'${identifier}' is not a valid QTI Identifier.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
	 * Get the QTI Identifier of the associated mapping.
	 *
	 * @return string A QTI Identifier.
	 */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
	 * @see \qtism\data\QtiComponent::getQtiClassName()
	 */
    public function getQtiClassName()
    {
        return 'mapResponsePoint';
    }

    /**
     * Checks whether this expression is pure.
     * @link https://en.wikipedia.org/wiki/Pure_function
     *
     * @return boolean True if the expression is pure, false otherwise
     */
    public function isPure()
    {
        return true; // implements map, + and reduce functions, which are all pure functions --> pure
    }
}
