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

namespace qtism\data\storage\php\marshalling;

use qtism\data\storage\php\PhpStreamAccess;
use \SplStack;
use \InvalidArgumentException;
use \RuntimeException;

/**
 * This class represents the running context of the marshalling process
 * of a QtiComponent into PHP source code.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class PhpMarshallingContext {
    
    /**
     * The stack of object variable names.
     * 
     * @var SplStack
     */
    private $variableStack;
    
    /**
     * Whether to format the output PHP source code.
     * 
     * @var boolean
     */
    private $formatOutput;
    
    /**
     * The stream where the output PHP source code must be written.
     * 
     * @var PhpStreamAccess
     */
    private $streamAccess;
    
    /**
     * Create a new MarshallingContext object.
     * 
     * @param PhpStreamAccess An access to a PHP source code stream for output.
     */
    public function __construct(PhpStreamAccess $streamAccess) {
        $this->setVariableStack(new SplStack());
        $this->setFormatOutput(false);
        $this->setStreamAccess($streamAccess);
    }
    
    /**
     * Set the variables name stack.
     * 
     * @param SplStack $variableStack
     */
    protected function setVariableStack(SplStack $variableStack) {
        $this->variableStack = $variableStack;
    }
    
    /**
     * Get the variables name stack.
     * 
     * @return SplStack
     */
    protected function getVariableStack() {
        return $this->variableStack;
    }
    
    /**
     * Set whether to format the output PHP source code.
     * 
     * @param boolean $formatOutput
     */
    public function setFormatOutput($formatOutput) {
        $this->formatOutput = $formatOutput;
    }
    
    /**
     * Whether to format the output PHP source code.
     * 
     * @return boolean
     */
    public function mustFormatOutput() {
        return $this->formatOutput;
    }
    
    /**
     * Set the PHP source code stream access to be used at marshalling time.
     * 
     * @param PhpStreamAccess $streamAccess An access to a PHP source code stream.
     */
    protected function setStreamAccess(PhpStreamAccess $streamAccess) {
        $this->streamAccess = $streamAccess;
    }
    
    /**
     * Get the PHP source code stream access to be used at marshalling time for output.
     * 
     * @return PhpStreamAccess An access to a PHP source code stream.
     */
    public function getStreamAccess() {
        return $this->streamAccess;
    }
    
    /**
     * Push some value(s) on the variable names stack.
     * 
     * @param string|array $values A string or an array of strings to be pushed on the variable names stack.
     * @throws InvalidArgumentException If $value or an item of $value is not a non-empty string.
     */
    public function pushOnVariableStack($values) {
        if (is_array($values) === false) {
            $values = array($values);
        }
        
        foreach ($values as $value) {
            if (is_string($value) === false) {
                $msg = "The pushOnVariableStack method only accepts non-empty string values.";
                throw new InvalidArgumentException($msg);
            }
            
            $this->getVariableStack()->push($value);
        }
    }
    
    /**
     * Pop a given $quantity of values from the variable names stack.
     * 
     * @param integer $quantity
     * @return string|array A string ($quantity = 1) or an array of strings ($quantity > 1).
     * @throws RuntimeException If the the quantity of elements in the stack before popping is less than $quantity.
     * @throws InvalidArgumentException If $quantity < 1.
     */
    public function popFromVariableStack($quantity = 1) {
        
        $quantity = intval($quantity);
        if ($quantity < 1) {
            $msg = "The 'quantity' argument must be >= 1, '${quantity}' given.";
            throw new InvalidArgumentException($msg);
        }
        
        $stack = $this->getVariableStack();
        $stackCount = count($stack);
        
        if ($stackCount < $quantity) {
            $msg = "The number of elements in the variable names stack (${stackCount}) exceeds the requested quantity (${quantity}).";
            throw new RuntimeException($msg);
        }
        
        $values = array();
        for ($i = 0; $i < $quantity; $i++) {
            $values[] = $stack->pop();
        }
        
        return (count($values) === 1) ? $values[0] : $values;
    }
}