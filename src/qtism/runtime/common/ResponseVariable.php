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
use qtism\common\Comparable;
use qtism\common\datatypes\QtiDatatype;
use qtism\data\state\AreaMapping;
use qtism\data\state\Mapping;
use qtism\data\state\ResponseDeclaration;
use qtism\data\state\VariableDeclaration;

/**
 * This class represents a Response Variable in the QTI Runtime Model.
 *
 * A note from IMS about response variables initialization:
 *
 * At runtime, response variables are instantiated as part of an item session. Their values are always initialized to
 * NULL (no value) regardless of whether or not a default value is given in the declaration. A response variable with a NULL
 * value indicates that the candidate has not offered a response, either because they have not attempted the item at all or
 * because they have attempted it and chosen not to provide a response.
 *
 * If a default value has been provided for a response variable then the variable is set to this value at the start of the
 * first attempt. If the candidate never attempts the item, in other words, the item session passes straight from the initial
 * state to the closed state without going through the interacting state, then the response variable remains NULL and the
 * default value is never used.
 */
class ResponseVariable extends Variable
{
    /**
     * The correct response from the QTI Data Model as QTI Runtime value (primitive or container).
     *
     * @var mixed
     */
    private $correctResponse = null;

    /**
     * The mapping from the QTI Data Model.
     *
     * @var Mapping
     */
    private $mapping = null;

    /**
     * The AreaMapping from the QTI Data Model.
     *
     * @var AreaMapping
     */
    private $areaMapping = null;

    /**
     * Create a new ResponseVariable object. If the cardinality is multiple, ordered or record,
     * the appropriate container will be instantiated interally as the $value argument.
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
     * Set the correct response.
     *
     * @param QtiDatatype|null $correctResponse A QtiDatatype object or null.
     * @throws InvalidArgumentException If $correctResponse does not match baseType and/or cardinality of the variable.
     */
    public function setCorrectResponse(QtiDatatype $correctResponse = null): void
    {
        if ($correctResponse !== null 
            && (!Utils::isBaseTypeCompliant($this->getBaseType(), $correctResponse)
                || !Utils::isCardinalityCompliant($this->getCardinality(), $correctResponse)
            )
        ) {
            $msg = 'The given correct response is not compliant with the associated response variable.';
            throw new InvalidArgumentException($msg);
        }

        $this->correctResponse = $correctResponse;
    }

    /**
     * Get the correct response.
     *
     * @return QtiDatatype|null A QTI Runtime value (primitive or container).
     */
    public function getCorrectResponse(): ?QtiDatatype
    {
        return $this->correctResponse;
    }

    /**
     * Whether the ResponseVariable holds a CorrectResponse object.
     *
     * @return bool
     */
    public function hasCorrectResponse(): bool
    {
        return $this->getCorrectResponse() !== null;
    }

    /**
     * Set the mapping.
     *
     * @param Mapping $mapping A Mapping object from the QTI Data Model.
     */
    public function setMapping(Mapping $mapping = null): void
    {
        $this->mapping = $mapping;
    }

    /**
     * Get the mapping.
     *
     * @return Mapping A mapping object from the QTI Data Model.
     */
    public function getMapping(): ?Mapping
    {
        return $this->mapping;
    }

    /**
     * Set the area mapping.
     *
     * @param AreaMapping $areaMapping An AreaMapping object from the QTI Data Model.
     */
    public function setAreaMapping(AreaMapping $areaMapping = null): void
    {
        $this->areaMapping = $areaMapping;
    }

    /**
     * Get the area mapping.
     *
     * @return AreaMapping An AreaMapping object from the QTI Data Model.
     */
    public function getAreaMapping(): ?AreaMapping
    {
        return $this->areaMapping;
    }

    /**
     * Whether the value of the ResponseVariable matches its
     * correct response.
     *
     * @return bool
     */
    public function isCorrect(): bool
    {
        if ($this->hasCorrectResponse() === true) {
            $correctResponse = $this->getCorrectResponse();

            if ($correctResponse instanceof Comparable) {
                return $correctResponse->equals($this->getValue());
            } else {
                return $correctResponse === $this->getValue();
            }
        }

        return false;
    }

    /**
     * Create a ResponseVariable object from its data model representation.
     *
     * @param VariableDeclaration $variableDeclaration
     * @return ResponseVariable
     * @throws InvalidArgumentException
     */
    public static function createFromDataModel(VariableDeclaration $variableDeclaration): ResponseVariable
    {
        $variable = parent::createFromDataModel($variableDeclaration);

        if ($variableDeclaration instanceof ResponseDeclaration) {
            $variable->setMapping($variableDeclaration->getMapping());
            $variable->setAreaMapping($variableDeclaration->getAreaMapping());

            $dataModelCorrectResponse = $variableDeclaration->getCorrectResponse();
            if (!empty($dataModelCorrectResponse)) {
                $baseType = $variable->getBaseType();
                $cardinality = $variable->getCardinality();
                $dataModelValues = $dataModelCorrectResponse->getValues();
                $correctResponse = static::dataModelValuesToRuntime($dataModelValues, $baseType, $cardinality);

                $variable->setCorrectResponse($correctResponse);
            }

            return $variable;
        } else {
            $msg = "ResponseVariable::createFromDataModel only accepts '" . ResponseDeclaration::class . "' objects, '" . get_class($variableDeclaration) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    public function __clone()
    {
        parent::__clone();
        if (($cv = $this->getCorrectResponse()) !== null) {
            $this->correctResponse = clone $cv;
        }
    }
}
