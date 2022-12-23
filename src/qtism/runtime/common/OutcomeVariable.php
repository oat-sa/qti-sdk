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

namespace qtism\runtime\common;

use InvalidArgumentException;
use qtism\common\datatypes\QtiDatatype;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\state\LookupTable;
use qtism\data\state\OutcomeDeclaration;
use qtism\data\state\VariableDeclaration;
use qtism\data\ViewCollection;

/**
 * This class represents an Outcome Variable in a QTI Runtime context.
 */
class OutcomeVariable extends Variable
{
    /**
     * The intended audiance of the OutcomeVariable.
     *
     * @var ViewCollection
     */
    private $views;

    /**
     * The normal maximum.
     *
     * @var bool|float
     */
    private $normalMaximum = false;

    /**
     * The normal minimum.
     *
     * @var bool|float
     */
    private $normalMinimum = false;

    /**
     * The mastery value.
     *
     * @var bool|float
     */
    private $masteryValue = false;

    /**
     * The QTI Data Model lookupTable bound to the OutcomeVariable.
     *
     * @var LookupTable
     */
    private $lookupTable = null;

    /**
     * Create a new OutcomeVariable object. If the cardinality is multiple, ordered or record,
     * the appropriate container will be instantiated internally as the $value argument.
     *
     * @param string $identifier An identifier for the variable.
     * @param int $cardinality A value from the Cardinality enumeration.
     * @param int $baseType A value from the BaseType enumeration. -1 can be given to state there is no particular baseType if $cardinality is Cardinality::RECORD.
     * @param QtiDatatype|null $value A QtiDatatype object or null.
     * @throws InvalidArgumentException If $identifier is not a string, if $baseType is not a value from the BaseType enumeration, if $cardinality is not a value from the Cardinality enumeration, if $value is not compliant with the QTI Runtime Model.
     */
    public function __construct($identifier, $cardinality, $baseType = -1, QtiDatatype $value = null)
    {
        parent::__construct($identifier, $cardinality, $baseType, $value);
    }

    /**
     * Get the value of the views attribute.
     *
     * @return ViewCollection
     */
    public function getViews(): ?ViewCollection
    {
        return $this->views;
    }

    /**
     * Set the value of the views attribute.
     *
     * @param ViewCollection $views
     */
    public function setViews(ViewCollection $views): void
    {
        $this->views = $views;
    }

    /**
     * Set the normal maximum.
     *
     * @param float|bool $normalMaximum The normal maximum or false if not defined.
     * @throws InvalidArgumentException If $normalMaximum is not false nor a floating point value.
     */
    public function setNormalMaximum($normalMaximum): void
    {
        if ((is_bool($normalMaximum) && $normalMaximum === false) || is_float($normalMaximum)) {
            $this->normalMaximum = $normalMaximum;
        } else {
            $msg = "The normalMaximum argument must be a floating point value or false, '" . gettype($normalMaximum) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the normal maximum.
     *
     * @return bool|float False if not defined, otherwise a floating point value.
     */
    public function getNormalMaximum()
    {
        return $this->normalMaximum;
    }

    /**
     * Set the normal minimum.
     *
     * @param float|bool $normalMinimum The normal minimum or false if not defined.
     * @throws InvalidArgumentException If $normalMinimum is not false nor a floating point value.
     */
    public function setNormalMinimum($normalMinimum): void
    {
        if ((is_bool($normalMinimum) && $normalMinimum === false) || is_float($normalMinimum)) {
            $this->normalMinimum = $normalMinimum;
        } else {
            $msg = "The normalMinimum argument must be a floating point value or false, '" . gettype($normalMinimum) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the normal minimum.
     *
     * @return bool|float|double False if not defined, otherwise a floating point value.
     */
    #[\ReturnTypeWillChange]
    public function getNormalMinimum()
    {
        return $this->normalMinimum;
    }

    /**
     * Set the mastery value.
     *
     * @param float|double|bool $masteryValue A floating point value or false if not defined.
     * @throws InvalidArgumentException If $masteryValue is not a floating point value nor false.
     */
    public function setMasteryValue($masteryValue): void
    {
        if ((is_bool($masteryValue) && $masteryValue === false) || is_float($masteryValue)) {
            $this->masteryValue = $masteryValue;
        } else {
            $msg = "The masteryValue argument must be a floating point value or false, '" . gettype($masteryValue) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the mastery value.
     *
     * @return float|double|bool False if not defined, otherwise a floating point value.
     */
    #[\ReturnTypeWillChange]
    public function getMasteryValue()
    {
        return $this->masteryValue;
    }

    /**
     * Set the lookup table.
     *
     * @param LookupTable $lookupTable A QTI Data Model LookupTable object or null if not specified.
     */
    public function setLookupTable(LookupTable $lookupTable = null): void
    {
        $this->lookupTable = $lookupTable;
    }

    /**
     * Get the lookup table.
     *
     * @return LookupTable A QTI Data Model LookupTable object or null if not defined.
     */
    public function getLookupTable(): ?LookupTable
    {
        return $this->lookupTable;
    }

    /**
     * Create an OutcomeVariable object from a data model VariableDeclaration object.
     *
     * @param VariableDeclaration $variableDeclaration
     * @return OutcomeVariable
     * @throws InvalidArgumentException
     */
    public static function createFromDataModel(VariableDeclaration $variableDeclaration): self
    {
        $variable = parent::createFromDataModel($variableDeclaration);

        if ($variableDeclaration instanceof OutcomeDeclaration) {
            $variable->setViews($variableDeclaration->getViews());
            $variable->setNormalMaximum($variableDeclaration->getNormalMaximum());
            $variable->setNormalMinimum($variableDeclaration->getNormalMinimum());
            $variable->setMasteryValue($variableDeclaration->getMasteryValue());
            $variable->setLookupTable($variableDeclaration->getLookupTable());

            return $variable;
        } else {
            $msg = "OutcomeVariable::createFromDataModel only accept '" . OutcomeDeclaration::class . "' objects, '" . get_class($variableDeclaration) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Apply the default value to the OutcomeVariable.
     *
     * If no default value is described, and the cardinality is single and the baseType
     * is integer or float, the value of the variable becomes 0.
     */
    public function applyDefaultValue(): void
    {
        parent::applyDefaultValue();

        if ($this->getDefaultValue() === null && $this->getCardinality() === Cardinality::SINGLE) {
            if ($this->getBaseType() === BaseType::INTEGER) {
                $this->setValue(new QtiInteger(0));
            } elseif ($this->getBaseType() === BaseType::FLOAT) {
                $this->setValue(new QtiFloat(0.0));
            }
        }

        $this->isInitializedFromDefaultValue = true;
    }

    public function __clone()
    {
        parent::__clone();
    }
}
