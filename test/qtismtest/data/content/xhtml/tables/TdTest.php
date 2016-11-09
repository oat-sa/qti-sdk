<?php
namespace qtismtest\data\content\xhtml\tables;

use qtismtest\QtiSmTestCase;
use qtism\data\content\xhtml\tables\Td;
use qtism\data\content\xhtml\tables\TableCellScope;

class TdTest extends QtiSmTestCase
{
    public function testSetScopeWrongValue()
    {
        $td = new Td();
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'scope' argument must be a value from the TableCellScope enumeration, '1' given."
        );
        
        $td->setScope(true);
    }
    
    public function testSetAbbrWrongType()
    {
        $td = new Td();
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'abbr' attribute must be a string, 'boolean' given."
        );
        
        $td->setAbbr(true);
    }
    
    public function testSetAxisWrongType()
    {
        $td = new Td();
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'axis' argument must be a string, 'boolean' given."
        );
        
        $td->setAxis(true);
    }
    
    public function testSetRowspanWrongType()
    {
        $td = new Td();
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'rowspan' argument must be an integer, 'boolean' given."
        );
        
        $td->setRowspan(true);
    }
    
    public function testSetColspanWrongType()
    {
        $td = new Td();
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'colspan' argument must be an integer, 'boolean' given."
        );
        
        $td->setColspan(true);
    }
}
