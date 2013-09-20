<?php

namespace qtism\runtime\tests;

use qtism\common\collections\AbstractCollection;
use \InvalidArgumentException;

/**
 * A collection implementation aiming at storing RouteItem objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class RouteItemCollection extends AbstractCollection {
    
    /**
     * Check whether $value is an instance of RouteItem.
     * 
     * @throws InvalidArgumentException If $value is not an instance of RouteItem.
     */
    protected function checkType($value) {
        if (!$value instanceof RouteItem) {
            $msg = "RoutItemCollection objects only accept to store RouteItem objects.";
            throw new InvalidArgumentException($msg);
        }
    }
}