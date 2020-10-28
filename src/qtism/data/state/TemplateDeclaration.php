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
use qtism\common\enums\Cardinality;

/**
 * From IMS QTI:
 *
 * Template declarations declare item variables that are to be used specifically for
 * the purposes of cloning items. They can have their value set only during templateProcessing.
 * They are referred to within the itemBody in order to individualize the clone and possibly
 * also within the responseProcessing rules if the cloning process affects the way the item is scored.
 *
 * Template variables are instantiated as part of an item session. Their values are initialized
 * during templateProcessing and thereafter behave as constants within the session.
 */
class TemplateDeclaration extends VariableDeclaration
{
    /**
     * From IMS QTI:
     *
     * This attribute determines whether or not the template variable's
     * value should be substituted for object parameter values that match its name.
     * See param for more information.
     *
     * @var bool
     * @qtism-bean-property
     */
    private $paramVariable = false;

    /**
     * From IMS QTI:
     *
     * This attribute determines whether or not the template variable's value should
     * be substituted for identifiers that match its name in MathML expressions.
     * See Combining Template Variables and MathML for more information.
     *
     * @var bool
     * @qtism-bean-property
     */
    private $mathVariable = false;

    /**
     * TemplateDeclaration constructor.
     *
     * @param $identifier
     * @param int $baseType
     * @param int $cardinality
     * @param DefaultValue|null $defaultValue
     */
    public function __construct($identifier, $baseType = -1, $cardinality = Cardinality::SINGLE, DefaultValue $defaultValue = null)
    {
        parent::__construct($identifier, $baseType, $cardinality, $defaultValue);
    }

    /**
     * Set whether or not the template variable's value should be substituted for
     * object parameters.
     *
     * @param bool $paramVariable A boolean value.
     * @throws InvalidArgumentException If $paramVariable is not a boolean value.
     */
    public function setParamVariable($paramVariable)
    {
        if (is_bool($paramVariable)) {
            $this->paramVariable = $paramVariable;
        } else {
            $msg = "The 'paramVariable' argument must be a boolean value, '" . gettype($paramVariable) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Lets you know whether or not the template variable's value should be substituted for
     * object parameters.
     *
     * @return bool
     */
    public function isParamVariable()
    {
        return $this->paramVariable;
    }

    /**
     * Set whether or not the template variable's value should be substituted for identifiers
     * that match its name in MathML.
     *
     * @param bool $mathVariable A boolean value.
     * @throws InvalidArgumentException If $mathVariable is not a boolean value.
     */
    public function setMathVariable($mathVariable)
    {
        if (is_bool($mathVariable)) {
            $this->mathVariable = $mathVariable;
        } else {
            $msg = "The 'mathVariable' argument must be a boolean value, '" . gettype($mathVariable) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Lets you know whether or not the template variable's value should be substitued for identifiers
     * that match its name in MathML.
     *
     * @return bool
     */
    public function isMathVariable()
    {
        return $this->mathVariable;
    }

    /**
     * @return string
     */
    public function getQtiClassName()
    {
        return 'templateDeclaration';
    }
}
