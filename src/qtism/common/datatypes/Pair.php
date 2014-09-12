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
 * Copyright (c) 2013-2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */
namespace qtism\common\datatypes;

use qtism\common\enums\Cardinality;
use qtism\common\enums\BaseType;
use qtism\common\utils\Format;
use \InvalidArgumentException;

/**
 * Represents the QTI Pair datatype.
 *
 * From IMS QTI:
 *
 * A pair value represents a pair of identifiers corresponding to an association between two
 * objects. The association is undirected so (A,B) and (B,A) are equivalent.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Pair implements QtiDatatype
{
    /**
	 * The first identifier of the Pair.
	 *
	 * @var string
	 */
    private $first;

    /**
	 * The second identifier of the Pair.
	 *
	 * @var string
	 */
    private $second;

    /**
	 * Create a new instance of Pair.
	 *
	 * @param string $first The first identifier of the pair.
	 * @param string $second The second identifier of the pair.
	 * @throws \InvalidArgumentException If $first nor $second are valid QTI identifiers.
	 */
    public function __construct($first, $second)
    {
        $this->setFirst($first);
        $this->setSecond($second);
    }

    /**
	 * Set the first identifier of the pair.
	 *
	 * @param string $first A QTI Identifier.
	 * @throws \InvalidArgumentException If $first is an invalid QTI Identifier.
	 */
    public function setFirst($first)
    {
        if (Format::isIdentifier($first)) {
            $this->first = $first;
        } else {
            $msg = "'${first}' is an invalid QTI identifier.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
	 * Get the first identifier of the pair.
	 *
	 * @return string A QTI Identifier.
	 */
    public function getFirst()
    {
        return $this->first;
    }

    /**
	 * Set the second identifier of the pair.
	 *
	 * @param string $second A QTI Identifier.
	 * @throws \InvalidArgumentException If $identifier is not a valid QTI Identifier.
	 */
    public function setSecond($second)
    {
        if (Format::isIdentifier($second)) {
            $this->second = $second;
        } else {
            $msg = "'${second}' is an invalid QTI identifier.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
	 * Get the second identifier of the pair.
	 *
	 * @return string A QTI Identifier.
	 */
    public function getSecond()
    {
        return $this->second;
    }

    /**
	 * Returns 'FIRST_ID SECOND_ID'.
	 *
	 * @return string The serialized version of the Pair.
	 */
    public function __toString()
    {
        return $this->getFirst() . ' ' . $this->getSecond();
    }

    /**
	 * Whether a given $obj is equal to this Pair. Two Pair objects
	 * are considered to be identical if the first and second values
	 * of $obj are the same as the ones of $obj.
	 *
	 * @param mixed $obj A value to compare.
	 * @return boolean Whether or not the equality could be established.
	 */
    public function equals($obj)
    {
        if (gettype($obj) === 'object' && $obj instanceof self) {
            $a = array($this->getFirst(), $this->getSecond());
            $b = array($obj->getFirst(), $obj->getSecond());

            return in_array($b[0], $a) && in_array($b[1], $a);
        }

        return false;
    }

    /**
	 * Get the baseType of the value. This method systematically returns
	 * The BaseType::PAIR value.
	 *
	 * @return A value from the BaseType enumeration.
	 */
    public function getBaseType()
    {
        return BaseType::PAIR;
    }

    /**
	 * Get the Cardinality of the value. This method systematically returns
	 * the Cardinality::SINGLE value.
	 *
	 * @return A value from the Cardinality enumeration.
	 */
    public function getCardinality()
    {
        return Cardinality::SINGLE;
    }
}
