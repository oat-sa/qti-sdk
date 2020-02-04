<?php

namespace qtismtest\data\content\xhtml;

use qtismtest\QtiSmEnumTestCase;
use qtism\data\content\xhtml\ParamType;

class ParamTypeTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return ParamType::class;
    }
    
    protected function getNames()
    {
        return array(
            'DATA',
            'REF'
        );
    }
    
    protected function getKeys()
    {
        return array(
            'DATA',
            'REF',
        );
    }
    
    protected function getConstants()
    {
        return array(
            ParamType::DATA,
            ParamType::REF
        );
    }
}
