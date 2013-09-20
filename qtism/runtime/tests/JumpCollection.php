<?php

namespace qtism\runtime\tests;

use qtism\common\collections\AbstractCollection;

/**
 * A collection implementation aiming at storing Jump objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class JumpCollection extends AbstractCollection {
    
    /**
     * Check the type of $value to ensure it has the correct datatype.
     * 
     * @throws InvalidArgumentException If $value is not a Jump object.
     */
    protected function checkType($value) {
        if (!$value instanceof Jump) {
            $msg = "JumpCollection objects only accept to store Jump objects.";
            throw new InvalidArgumentException($msg);
        }
    }
}