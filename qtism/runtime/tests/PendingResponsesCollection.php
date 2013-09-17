<?php

namespace qtism\runtime\tests;

use qtism\common\collections\AbstractCollection;
use \InvalidArgumentException;

/**
 * A Collection aiming at storing PendingResponses objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class PendingResponsesCollection extends AbstractCollection {
    
    protected function checkType($value) {
        if (!$value instanceof PendingResponses) {
            $msg = "PendingResponsesCollection objects only accept to store PendingResponses objects.";
            throw new InvalidArgumentException($msg);
        }
    }
}