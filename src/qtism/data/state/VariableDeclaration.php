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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\state;

use InvalidArgumentException;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\common\utils\Format;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\QtiIdentifiable;
use qtism\data\QtiIdentifiableTrait;
use SplObjectStorage;

/**
 * From IMS QTI:
 *
 * Item variables are declared by variable declarations. All variables must be declared except
 * for the built-in session variables referred to below which are declared implicitly and must
 * not be declared. The purpose of the declaration is to associate an identifier with the
 * variable and to identify the runtime type of the variable's value.
 *
 * An item variable may have no value at all, in which case it is said to have the special
 * value NULL. For example, if the candidate has not yet had an opportunity to respond to
 * an interaction then any associated response variable will have a NULL value. Empty
 * containers and empty strings are always treated as NULL values.
 */
class VariableDeclaration extends QtiComponent implements QtiIdentifiable
{
    use QtiIdentifiableTrait;

    /**
     * From IMS QTI:
     *
     * The identifiers of the built-in session variables are reserved. They are completionStatus,
     * numAttempts and duration. All item variables declared in an item share the same
     * namespace. Different items have different namespaces.
     *
     * @var string
     * @qtism-bean-property
     */
    private $identifier;

    /**
     * From IMS QTI:
     *
     * Each variable is either single valued or multi-valued. Multi-valued variables are
     * referred to as containers and come in ordered, unordered and record types.
     * See cardinality for more information.
     *
     * @var int
     * @qtism-bean-property
     */
    private $cardinality;

    /**
     * The baseType of the variable declaration. A negative value means there is no baseType.
     *
     * From IMS QTI:
     *
     * The value space from which the variable's value can be drawn (or in the case of containers,
     * from which the individual values are drawn) is identified with a baseType. The baseType
     * selects one of a small set of predefined types that are considered to have atomic values
     * within the runtime data model. Variables with record cardinality have no base-type.
     *
     * @var int
     * @qtism-bean-property
     */
    private $baseType = -1;

    /**
     * An optional default value for the variable. The point at which a variable is set to
     * its default value varies depending on the type of item variable. If not set, will
     * contain the null value.
     *
     * @var DefaultValue
     * @qtism-bean-property
     */
    private $defaultValue = null;

    /**
     * Create a new instance of VariableDeclaration.
     *
     * @param string $identifier The identifier of the VariableDeclaration.
     * @param int $baseType A value from the BaseType enumeration or -1 if no baseType.
     * @param int $cardinality A value from the Cardinality enumeration.
     * @param DefaultValue $defaultValue A DefaultValue object.
     */
    public function __construct($identifier, $baseType = -1, $cardinality = Cardinality::SINGLE, ?DefaultValue $defaultValue = null)
    {
        $this->setObservers(new SplObjectStorage());

        $this->setIdentifier($identifier);
        $this->setBaseType($baseType);
        $this->setCardinality($cardinality);
        $this->setDefaultValue($defaultValue);
    }

    /**
     * Get the identifer of the VariableDeclaration;
     *
     * @return string A QTI identifier.
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Set the identifier of the VariableDeclaration.
     *
     * @param string $identifier A QTI Identifier.
     * @throws InvalidArgumentException If $identifier is not a valid QTI Identifier.
     */
    public function setIdentifier($identifier): void
    {
        if (Format::isIdentifier($identifier, false)) {
            $this->identifier = $identifier;
            $this->notify();
        } else {
            $msg = "'{$identifier}' is not a valid QTI Identifier.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the BaseType of the VariableDeclaration.
     *
     * @return int A value from the BaseType enumeration.
     */
    public function getBaseType(): int
    {
        return $this->baseType;
    }

    /**
     * Set the basetype of the VariableDeclaration;
     *
     * @param int $baseType A value from the BaseType enumeration.
     * @throws InvalidArgumentException If $baseType is not a value from the BaseType enumeration.
     */
    public function setBaseType($baseType): void
    {
        if (in_array($baseType, BaseType::asArray()) || $baseType === -1) {
            $this->baseType = $baseType;
        } else {
            $msg = 'BaseType must be a value from the BaseType enumeration.';
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Whether the baseType attribute is defined for this
     * VariableDeclaration.
     *
     * @return bool
     */
    public function hasBaseType(): bool
    {
        return $this->getBaseType() !== -1;
    }

    /**
     * Get the default value of the variable declaration. If not set, returns the null
     * value.
     *
     * @return DefaultValue A DefaultValue object.
     */
    public function getDefaultValue(): ?DefaultValue
    {
        return $this->defaultValue;
    }

    /**
     * Set the default value of the variable declaration. If not set, returns the null
     * value.
     *
     * @param DefaultValue $defaultValue A DefaultValue object.
     */
    public function setDefaultValue(?DefaultValue $defaultValue = null): void
    {
        $this->defaultValue = $defaultValue;
    }

    /**
     * Whether a DefaultValue object is contained by the VariableDeclaration.
     *
     * @return bool
     */
    public function hasDefaultValue(): bool
    {
        return $this->getDefaultValue() !== null;
    }

    /**
     * Get the cardinality of the variable.
     *
     * @return int A value from the Cardinality enumeration.
     */
    public function getCardinality(): int
    {
        return $this->cardinality;
    }

    /**
     * Set the cardinality of the variable.
     *
     * @param int $cardinality A value from the Cardinality enumeration.
     * @throws InvalidArgumentException If $cardinality is not a value from the Cardinality enumeration.
     */
    public function setCardinality($cardinality): void
    {
        if (in_array($cardinality, Cardinality::asArray())) {
            $this->cardinality = $cardinality;
        } else {
            $msg = 'The cardinality must be a value from the Cardinality enumeration.';
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'variableDeclaration';
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents(): QtiComponentCollection
    {
        $comp = [];

        if ($this->getDefaultValue() !== null) {
            $comp[] = $this->getDefaultValue();
        }

        return new QtiComponentCollection($comp);
    }

    public function __clone()
    {
        $this->setObservers(new SplObjectStorage());
    }
}
