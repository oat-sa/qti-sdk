<?php

namespace qtism\runtime\tests;

use qtism\common\collections\AbstractCollection;
use \InvalidArgumentException;

/**
 * The AssessmentItemSession collection class aims at storing AssessmentItemSession
 * objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class AssessmentItemSessionCollection extends AbstractCollection {
    
    protected function checkType($value) {
        if (!$value instanceof AssessmentItemSession) {
            $msg = "The AssessmentItemSessionCollection class only accepts to store AssessmentItemSession objects.";
            throw new InvalidArgumentException($msg);
        }
    }
    
}