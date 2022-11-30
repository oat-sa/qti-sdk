<?php

declare(strict_types=1);

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
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiPoint;
use qtism\data\state\TemplateDeclaration;
use qtism\data\state\VariableDeclaration;

/**
 * This class represents a Template Variable in a QTI Runtime context.
 */
class TemplateVariable extends Variable
{
    /**
     * From IMS QTI:
     *
     * This attribute determines whether or not the template variable's value should be substituted for
     * object parameter values that match its name. See param for more information.
     *
     * @var bool
     */
    private $paramVariable = false;

    /**
     * From IMS QTI:
     *
     * This attribute determines whether or not the template variable's value should be substituted
     * for identifiers that match its name in MathML expressions. See Combining Template Variables
     * and MathML for more information.
     *
     * @var bool
     */
    private $mathVariable = false;

    /**
     * Create a new TemplateVariable object. If the cardinality is multiple, ordered or record,
     * the appropriate container will be instantiated internally as the $value argument.
     *
     * @param string $identifier An identifier for the variable.
     * @param int $cardinality A value from the Cardinality enumeration.
     * @param int $baseType A value from the BaseType enumeration. -1 can be given to state there is no particular baseType if $cardinality is Cardinality::RECORD.
     * @param int|float|bool|string|QtiDuration|QtiPoint|QtiPair|QtiDirectedPair|null $value A value which is compliant with the QTI Runtime Model.
     * @throws InvalidArgumentException If $identifier is not a string, if $baseType is not a value from the BaseType enumeration, if $cardinality is not a value from the Cardinality enumeration, if $value is not compliant with the QTI Runtime Model.
     */
    public function __construct($identifier, $cardinality, $baseType = -1, $value = null)
    {
        parent::__construct($identifier, $cardinality, $baseType, $value);
    }

    /**
     * Set whether or not the template's value should be substituted for object
     * parameter values.
     *
     * @param bool $paramVariable
     * @throws InvalidArgumentException
     */
    public function setParamVariable($paramVariable): void
    {
        if (is_bool($paramVariable)) {
            $this->paramVariable = $paramVariable;
        } else {
            $msg = "The 'paramVariable' argument must be a boolean value, '" . gettype($paramVariable) . "'.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Let you know whether or not the template variable's value should be substituted
     * for object parameter values.
     *
     * @return bool
     */
    public function isParamVariable(): bool
    {
        return $this->paramVariable;
    }

    /**
     * Set whether or not the template variable's value should be substituted for identifiers that match
     * its name in MathML expressions.
     *
     * @param bool $mathVariable
     * @throws InvalidArgumentException
     */
    public function setMathVariable($mathVariable): void
    {
        if (is_bool($mathVariable)) {
            $this->mathVariable = $mathVariable;
        } else {
            $msg = "The 'mathVariable' argument must be a boolean value, '" . gettype($mathVariable) . "'.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Let you know whether or not the template variable's value should be substituted for identifiers
     * that match its name in MathML expressions.
     *
     * @return bool
     */
    public function isMathVariable(): bool
    {
        return $this->mathVariable;
    }

    /**
     * Create a TemplateVariable object from its data model representation.
     *
     * @param VariableDeclaration $variableDeclaration
     * @return TemplateVariable
     * @throws InvalidArgumentException
     */
    public static function createFromDataModel(VariableDeclaration $variableDeclaration): self
    {
        $variable = parent::createFromDataModel($variableDeclaration);

        if ($variableDeclaration instanceof TemplateDeclaration) {
            $variable->setParamVariable($variableDeclaration->isParamVariable());
            $variable->setMathVariable($variableDeclaration->isMathVariable());

            return $variable;
        } else {
            $msg = "TemplateVariable::createFromDataModel only accept '" . TemplateDeclaration::class . "' objects, '" . get_class($variableDeclaration) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    public function __clone()
    {
        parent::__clone();
    }
}
