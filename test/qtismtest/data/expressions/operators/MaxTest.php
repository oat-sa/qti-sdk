<?php
namespace qtismtest\data\expressions\operators;

use qtismtest\QtiSmTestCase;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\Max;
use qtism\data\expressions\BaseValue;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;

class MaxTest extends QtiSmTestCase
{
	public function testInstantiation() {
		$expressions = new ExpressionCollection();
		$expressions[] = new BaseValue(BaseType::INTEGER, 15);
		$expressions[] = new BaseValue(BaseType::INTEGER, 16); 
		$max = new Max($expressions);
		
		$this->assertInstanceOf('qtism\\data\\expressions\\operators\\Max', $max);
		$this->assertTrue(in_array(Cardinality::SINGLE, $max->getAcceptedCardinalities()));
		$this->assertTrue(in_array(Cardinality::MULTIPLE, $max->getAcceptedCardinalities()));
		$this->assertTrue(in_array(Cardinality::ORDERED, $max->getAcceptedCardinalities()));
		$this->assertTrue(in_array(BaseType::INTEGER, $max->getAcceptedBaseTypes()));
		$this->assertTrue(in_array(BaseType::FLOAT, $max->getAcceptedBaseTypes()));
		$this->assertEquals(1, $max->getMinOperands());
		$this->assertEquals(-1, $max->getMaxOperands());
	}
    
    /**
     * @depends testInstantiation
     */
	public function testSetMinOperandsWrongType()
    {
        $expressions = new ExpressionCollection();
        $expressions[] = new BaseValue(BaseType::INTEGER, 15);
        $expressions[] = new BaseValue(BaseType::INTEGER, 16);
        $max = new Max($expressions);
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The minOperands argument must be an integer >= 0, '1' given."
        );
        
        $max->setMinOperands(true);
    }
    
    /**
     * @depends testInstantiation
     */
    public function testSetMaxOperandsWrongType()
    {
        $expressions = new ExpressionCollection();
        $expressions[] = new BaseValue(BaseType::INTEGER, 15);
        $expressions[] = new BaseValue(BaseType::INTEGER, 16);
        $max = new Max($expressions);
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The maxOperands argument must be an integer, 'boolean' given."
        );
        
        $max->setMaxOperands(true);
    }
    
    /**
     * @depends testInstantiation
     */
    public function testSetAcceptedCardinalitiesWrongValue()
    {
        $expressions = new ExpressionCollection();
        $expressions[] = new BaseValue(BaseType::INTEGER, 15);
        $expressions[] = new BaseValue(BaseType::INTEGER, 16);
        $max = new Max($expressions);
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "Accepted cardinalities must be values from the Cardinality enumeration, '1' given"
        );
        
        $max->setAcceptedCardinalities(array(true));
    }
    
    /**
     * @depends testInstantiation
     */
    public function testSetAcceptedBaseTypesWrongValue()
    {
        $expressions = new ExpressionCollection();
        $expressions[] = new BaseValue(BaseType::INTEGER, 15);
        $expressions[] = new BaseValue(BaseType::INTEGER, 16);
        $max = new Max($expressions);
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "Accepted baseTypes must be values from the OperatorBaseType enumeration, '1' given."
        );
        
        $max->setAcceptedBaseTypes(array(true));
    }
}