<?php

namespace qtism\data\storage\php;

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
        if (is_scalar($value) === true || is_null($var) === true) {
            $this->value = $value;
        }
        else {
            $msg = "The 'value' argument must be a PHP scalar value or null, '" . gettype($value) . "' given.";
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
}