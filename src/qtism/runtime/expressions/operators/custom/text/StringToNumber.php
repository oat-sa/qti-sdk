<?php

namespace qti\customOperators\text;

use qtism\common\datatypes\QtiString as QtismString;
use qtism\common\datatypes\QtiFloat as QtismFloat;
use qtism\runtime\expressions\operators\CustomOperatorProcessor;

class StringToNumber extends CustomOperatorProcessor
{
    public function process() 
    {
        $returnValue = null;
        
        $operands = $this->getOperands();
        if (count($operands) > 0) {
            $operand = $operands[0];
            
            if ($operand !== null && $operand instanceof QtismString) {
                $str = str_replace(',', '', $operand->getValue());
                $float = @floatval($str);
                
                $returnValue = new QtismFloat($float);
            }
        }
        
        return $returnValue;
    }
}
