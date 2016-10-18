<?php
namespace qtismtest\data\state;

use qtismtest\QtiSmTestCase;
use qtism\data\state\OutcomeDeclaration;
use qtism\data\state\MatchTable;
use qtism\data\state\MatchTableEntryCollection;
use qtism\data\state\MatchTableEntry;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;

class OutcomeDeclarationTest extends QtiSmTestCase
{
    public function testSetInterpretationWrongType()
    {
        $outcomeDeclaration = new OutcomeDeclaration('SCORE', BaseType::FLOAT, Cardinality::SINGLE);
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "Interpretation must be a string, 'integer' given."
        );
        
        $outcomeDeclaration->setInterpretation(999);
    }
    
    public function testSetLongInterpretationWrongType()
    {
        $outcomeDeclaration = new OutcomeDeclaration('SCORE', BaseType::FLOAT, Cardinality::SINGLE);
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "LongInterpretation must be a string, 'integer' given."
        );
        
        $outcomeDeclaration->setLongInterpretation(999);
    }
    
    public function testSetNormalMinimumWrongType()
    {
        $outcomeDeclaration = new OutcomeDeclaration('SCORE', BaseType::FLOAT, Cardinality::SINGLE);
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "NormalMinimum must be a number or (boolean) false, 'string' given."
        );
        
        $outcomeDeclaration->setNormalMinimum('string');
    }
    
    public function testSetNormalMaximumWrongType()
    {
        $outcomeDeclaration = new OutcomeDeclaration('SCORE', BaseType::FLOAT, Cardinality::SINGLE);
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "NormalMaximum must be a number or (boolean) false, 'string' given."
        );
        
        $outcomeDeclaration->setNormalMaximum('string');
    }
    
    public function testSetMasteryValueWrongType()
    {
        $outcomeDeclaration = new OutcomeDeclaration('SCORE', BaseType::FLOAT, Cardinality::SINGLE);
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "MasteryValue must be a number or (boolean) false, 'string' given."
        );
        
        $outcomeDeclaration->setMasteryValue('string');
    }
    
    public function getComponentsWithLookupTable()
    {
        $outcomeDeclaration = new OutcomeDeclaration('SCORE', BaseType::FLOAT, Cardinality::SINGLE);
        $outcomeDeclaration->setLookupTable(
            new MatchTable(
                new MatchTableEntryCollection(
                    new MatchTableEntry(3, 3.33)
                )
            )
        );
        
        $components = $this->getComponents();
        $last = $components[count($components) - 1];
        $this->assertInstanceOf('qtism\\data\\state\\MatchTable', $last);
    }
}
