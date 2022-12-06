<?php

namespace qti\customOperators\math\fraction;

use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiInteger;
use qtism\runtime\expressions\operators\CustomOperatorProcessor;

abstract class NumeratorDenominator extends CustomOperatorProcessor
{
    public function process() 
    {
        $returnValue = null;
        
        $operands = $this->getOperands();
        if (count($operands) > 0) {
            $operand = $operands[0];
            
            if ($operand !== null && $operand instanceof QtiString && preg_match('@[0-9]+/[0-9]+@', $operand->getValue()) === 1) {
                $values = explode('/', $operand->getValue());
                return new QtiInteger(intval($this->extract($values)));
            }
        }
        
        return $returnValue;
    }
    
    abstract protected function extract(array $values);
}
