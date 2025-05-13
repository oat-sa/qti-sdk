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
use OutOfBoundsException;
use OutOfRangeException;
use qtism\common\collections\AbstractCollection;

/**
 * The State class represents a state composed by a set of Variable objects.
 *
 * This class implements Countable, Iterator and ArrayAccess thanks to its inheritance from the
 * qtism\common\collections\AbstractCollection.
 *
 * @see Variable For a description of the Variable class.
 * @see OutOfRangeException For a description of the SPL OutOfRangeException class.
 * @see OutOfBoundsException For a description of the SPL OutOfRangeException class.
 * @see InvalidArgumentException For a description of the SPL InvalidArgumentException class.
 */
class State extends AbstractCollection
{
    /**
     * Create a new State object.
     *
     * @param array $array An optional array of Variable objects.
     * @throws InvalidArgumentException If an object of $array is not a Variable object.
     */
    public function __construct(array $array = [])
    {
        parent::__construct();
        foreach ($array as $a) {
            $this->checkType($a);
            $this->setVariable($a);
        }
    }

    /**
     * Set a variable to the state. It will be accessible by it's variable name.
     *
     * @param Variable $variable
     */
    public function setVariable(Variable $variable): void
    {
        $this->checkType($variable);
        $data = &$this->getDataPlaceHolder();
        $data[$variable->getIdentifier()] = $variable;
    }

    /**
     * Get a variable with the identifier $variableIdentifier.
     *
     * @param string $variableIdentifier A QTI identifier.
     * @return Variable|null A Variable object or null if the $variableIdentifier does not match any Variable object stored in the State.
     */
    public function getVariable($variableIdentifier): ?Variable
    {
        $data = &$this->getDataPlaceHolder();
        return $data[$variableIdentifier] ?? null;
    }

    /**
     * Get all the Variable objects that compose the State.
     *
     * @return VariableCollection A collection of Variable objects.
     */
    public function getAllVariables(): VariableCollection
    {
        return new VariableCollection($this->getDataPlaceHolder());
    }

    /**
     * Unset a variable from the current state. In other words
     * the relevant Variable object is removed from the state.
     *
     * @param string|Variable $variable The identifier of the variable or a Variable object to unset.
     * @throws InvalidArgumentException If $variable is not a string nor a Variable object.
     * @throws OutOfBoundsException If no variable in the current state matches $variable.
     */
    public function unsetVariable($variable): void
    {
        $data = &$this->getDataPlaceHolder();

        if (is_string($variable)) {
            $variableIdentifier = $variable;
        } elseif ($variable instanceof Variable) {
            $variableIdentifier = $variable->getIdentifier();
        } else {
            $msg = "The variable argument must be a Variable object or a string, '" . gettype($variable) . "' given";
            throw new InvalidArgumentException($msg);
        }

        if (isset($data[$variableIdentifier])) {
            unset($data[$variableIdentifier]);
        } else {
            $msg = "No Variable object with identifier '{$variableIdentifier}' found in the current State object.";
            throw new OutOfBoundsException($msg);
        }
    }

    /**
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        if (is_string($offset) && empty($offset) === false) {
            $placeholder = &$this->getDataPlaceHolder();

            if (isset($placeholder[$offset])) {
                $placeholder[$offset]->setValue($value);
            } else {
                $msg = "No Variable object with identifier '{$offset}' found in the current State object.";
                throw new OutOfBoundsException($msg);
            }
        } else {
            $msg = 'A State object can only be addressed by a valid string.';
            throw new OutOfRangeException($msg);
        }
    }

    /**
     * @param string $offset
     * @return mixed|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if (is_string($offset) && $offset !== '') {
            $data = &$this->getDataPlaceHolder();
            if (isset($data[$offset])) {
                return $data[$offset]->getValue();
            } else {
                return null;
            }
        } else {
            $msg = 'A State object can only be addressed by a valid string.';
            throw new OutOfRangeException($msg);
        }
    }

    /**
     * Reset all outcome variables to their defaults.
     *
     * @param bool $preserveBuiltIn Whether the built-in outcome variable 'completionStatus' should be preserved.
     */
    public function resetOutcomeVariables($preserveBuiltIn = true): void
    {
        $data = &$this->getDataPlaceHolder();

        foreach (array_keys($data) as $k) {
            if ($data[$k] instanceof OutcomeVariable) {
                if ($preserveBuiltIn === true && $k === 'completionStatus') {
                    continue;
                } else {
                    $data[$k]->applyDefaultValue();
                }
            }
        }
    }

    /**
     * Reset all template variables to their defaults.
     *
     * @return void
     */
    public function resetTemplateVariables(): void
    {
        $data = &$this->getDataPlaceHolder();

        foreach (array_keys($data) as $k) {
            if ($data[$k] instanceof TemplateVariable) {
                $data[$k]->applyDefaultValue();
            }
        }
    }

    /**
     * Whether the State contains NULL only values.
     *
     * Please note that in QTI terms, empty containers and empty strings are considered
     * to be NULL as well. Moreover, if the State is empty of any variable, the method
     * will return true.
     *
     * @return bool
     */
    public function containsNullOnly(): bool
    {
        $data = $this->getDataPlaceHolder();

        foreach ($data as $variable) {
            $value = $variable->getValue();

            if ($variable->isNull() === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether the State contains only values that are equals to their variable default value only.
     *
     * @return bool
     */
    public function containsValuesEqualToVariableDefaultOnly(): bool
    {
        $data = $this->getDataPlaceHolder();

        foreach ($data as $variable) {
            $value = $variable->getValue();
            $default = $variable->getDefaultValue();

            if (Utils::isNull($value) === true) {
                if (Utils::isNull($default) === false) {
                    return false;
                }
            } elseif ($value->equals($default) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param mixed $value
     */
    protected function checkType($value): void
    {
        if (!$value instanceof Variable) {
            $msg = 'A State object stores Variable objects only.';
            throw new InvalidArgumentException($msg);
        }
    }
}
