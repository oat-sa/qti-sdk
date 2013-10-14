<?php

namespace qtism\data\storage\php;

use qtism\common\collections\AbstractCollection;

/**
 * This class aims at storing PhpArgument objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class PhpArgumentCollection extends AbstractCollection {
    
    /**
     * Checks wether $value is an instance of PhpArgumentCollection.
     * 
     * @throws InvalidArgumentException If $value is not an instance of PhpArgumentCollection.
     */
    protected function checkType($value) {
        if (!$value instanceof PhpArgument) {
            $msg = "A PhpArgumentCollection only accepts PhpArgument objects to be stored.";
            throw new InvalidArgumentException($msg);
        }
    }
}