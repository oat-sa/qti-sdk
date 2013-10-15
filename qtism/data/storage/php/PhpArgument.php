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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 * @license GPLv2
 * @package
 */

namespace qtism\data\storage\php;

use \InvalidArgumentException;

/**
 * Represents a PhpArgument. Two kind of arguments can be represented using
 * this class.
 * 
 * * A PHP scalar value to be marshalled into PHP source code.
 * * A PHP variable name e.g. '$foo'.
 * 
 * The PhpArgument class will automatically write PHP variable names if the given
 * value is prefixed by '$'. Otherwise, it will be written as a marshalled scalar
 * value (bool:true becomes true, string:text becomes "text", ...
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class PhpArgument {
    
    /**
     * The PHP scalar value or variable name to be written.
     * 
     * @var mixed
     */
    private $value;
    
    /**
     * Creates a new PhpArgument object.
     * 
     * @param mixed $value A PHP scalar value or null.
     * @throws InvalidArgumentException If $value is not a PHP scalar value nor null.
     */
    public function __construct($value) {
        $this->setValue($value);
    }
    
    /**
     * Set the value of the argument. It can be a PHP variable name e.g. '$foo' or 
     * any kind of PHP scalar value or null.
     * 
     * @param mixed $value A PHP scalar value or a variable name or null.
     * @throws InvalidArgumentException If $value is not a PHP scalar value nor a variable name nor null.
     */
    public function setValue($value) {
        if (Utils::isVariableReference($value) || Utils::isScalar($value)) {
            $this->value = $value;
        }
        else {
            $msg = "The 'value' argument must be a PHP scalar value, a variable reference string e.g. '\$foo' or null, '" . gettype($value) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }
    
    /**
     * Get the value of the argument. It can be a PHP variable name e.g. '$foo' or any
     * kind of PHP scalar value or null.
     * 
     * @return mixed A variable name or a PHP scalar value or null.
     */
    public function getValue() {
        return $this->value;
    }
    
    /**
     * Whether the represented argument is a reference to a variable or a plain
     * PHP scalar value.
     * 
     * @return boolean
     */
    public function isVariableReference() {
        return Utils::isVariableReference($this->getValue());
    }
    
    /**
     * Whether the represented argument is a PHP scalar value.
     * 
     * @return boolean
     */
    public function isScalar() {
        $value = $this->getValue();
        return Utils::isVariableReference($value) === false && Utils::isScalar($value);
    }
}