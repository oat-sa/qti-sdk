<?php
/**
 * Created by PhpStorm.
 * User: siwane
 * Date: 21/06/18
 * Time: 14:58
 */

namespace qtism\data\result;


use qtism\data\QtiComponentCollection;

class TestResultCollection extends QtiComponentCollection
{
    /**
     * Check if a given $value is an instance of ItemResult.
     *
     * @throws InvalidArgumentException If the given $value is not an instance of ItemResult.
     */
    protected function checkType($value)
    {
        if (!$value instanceof TestResult) {
            $msg = "TestResultCollection only accepts to store TestResult objects, '" . gettype($value) . "' given.";
            throw new \InvalidArgumentException($msg);
        }
    }
}