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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Moyon Camille, <camille@taotesting.com>
 * @license GPLv2
 *
 */

namespace qtism\data\results;

use qtism\common\datatypes\QtiIdentifier;
use qtism\data\QtiComponent;
use qtism\common\enums\Cardinality;
use qtism\common\enums\BaseType;
use \InvalidArgumentException;

/**
 * Class Variable
 *
 * The Item result information related to a { Response | Outcome | Template } variable.
 *
 * @package qtism\data\results
 */
abstract class ItemVariable extends QtiComponent
{
    /**
     * The identifier of the variable.
     *
     * Multiplicity [1]
     * @var QtiIdentifier
     */
    private $identifier;

    /**
     * The cardinality of the variable, taken from the corresponding declaration or definition.
     *
     * Multiplicity [1]
     * @var integer
     */
    private $cardinality;

    /**
     * The baseType of the variable, taken from the corresponding declaration of definition.
     * This value is omitted only for variables with record cardinality.
     *
     * Multiplicity [0,1]
     * @var integer
     */
    private $baseType=null;

    /**
     * Variable constructor.
     *
     * @param $identifier
     * @param $cardinality
     * @param int $baseType
     */
    public function __construct(QtiIdentifier $identifier, $cardinality, $baseType=null)
    {
        $this->setIdentifier($identifier);
        $this->setCardinality($cardinality);
        $this->setBaseType($baseType);
    }

    /**
     * Get the identifier of the Variable.
     *
     * @return QtiIdentifier An identifier.
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Get the identifier of the Variable.
     *
     * @param QtiIdentifier $identifier
     * @return $this
     */
    public function setIdentifier(QtiIdentifier $identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Get the cardinality of the Variable.
     *
     * @return integer
     */
    public function getCardinality()
    {
        return $this->cardinality;
    }

    /**
     * Set the cardinality of the Variable.
     *
     * @param string $cardinality
     * @return $this
     *
     * @throws InvalidArgumentException If the Cardinality is invalid
     */
    public function setCardinality($cardinality)
    {
        if (!in_array($cardinality, Cardinality::asArray())) {
            $msg = sprintf('Invalid Cardinality. Should be one of "%s"', implode('", "', Cardinality::asArray()));
            throw new InvalidArgumentException($msg);
        }
        $this->cardinality = $cardinality;
        return $this;
    }

    /**
     * Get the baseType of the Variable.
     *
     * @return integer A value from the Cardinality enumeration.
     */
    public function getBaseType()
    {
        return $this->baseType;
    }

    /**
     * Set the baseType of the Variable.
     *
     * @param string $baseType
     * @return $this
     *
     * @throws InvalidArgumentException If the baseType is invalid
     */
    public function setBaseType($baseType=null)
    {
        if (!is_null($baseType) && !in_array($baseType, BaseType::asArray())) {
            $msg = sprintf('Invalid baseType. Should be one of "%s"', implode('", "', BaseType::asArray()));
            throw new InvalidArgumentException($msg);
        }
        $this->baseType = $baseType;
        return $this;
    }

    /**
     * Check if variable has a baseType
     *
     * @return bool
     */
    public function hasBaseType()
    {
        return !is_null($this->baseType);
    }
}