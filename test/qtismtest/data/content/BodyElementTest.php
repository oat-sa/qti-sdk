<?php

namespace qtismtest\data\content;

use qtismtest\QtiSmTestCase;
use qtism\data\content\xhtml\text\Span;

class BodyElementTest extends QtiSmTestCase
{
    public function testSetId()
    {
        $span = new Span();
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'id' argument of a body element must be a valid identifier or an empty string"
        );
        
        $span->setId(999);
    }
    
    public function testClassWrongType()
    {
        $span = new Span();
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'class' argument must be a valid class name, '999' given"
        );
        
        $span->setClass(999);
    }
    
    public function testSetLabelWrongType()
    {
        $span = new Span();
    
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'label' argument must be a string that does not exceed 256 characters."
        );
    
        $span->setLabel(999);
    }
    
    public function testSetDirectionWrongLabel()
    {
        $span = new Span();
    
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'dir' argument must be a value from the Direction enumeration."
        );
    
        $span->setDir(true);
    }
}
