<?php

namespace qtismtest\runtime\tests;

use qtismtest\QtiSmTestCase;
use qtism\common\enums\BaseType;
use qtism\common\datatypes\QtiDatatype;
use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\RecordContainer;
use qtism\data\state\ResponseValidityConstraint;
use qtism\data\state\AssociationValidityConstraint;
use qtism\runtime\tests\Utils as TestUtils;

class TestUtilsTest extends QtiSmTestCase
{
    
    /**
     * @dataProvider isResponseValidProvider
     */
    public function testIsResponseValid($expected, QtiDatatype $response = null, ResponseValidityConstraint $constraint)
    {
        $this->assertEquals($expected, TestUtils::isResponseValid($response, $constraint));
    }
    
    public function isResponseValidProvider()
    {
        $tests = array(
            // Null values tests.
            array(true, null, new ResponseValidityConstraint('RESPONSE', 0, 0)),
            array(true, null, new ResponseValidityConstraint('RESPONSE', 0, 1)),
            array(true, null, new ResponseValidityConstraint('RESPONSE', 0, 3)),
            array(false, null, new ResponseValidityConstraint('RESPONSE', 1, 3)),
            array(false, null, new ResponseValidityConstraint('RESPONSE', 2, 3)),
            array(false, null, new ResponseValidityConstraint('RESPONSE', 1, 1)),
            array(false, new QtiString(''), new ResponseValidityConstraint('RESPONSE', 1, 1)),
            array(false, new MultipleContainer(BaseType::INTEGER), new ResponseValidityConstraint('RESPONSE', 1, 5)),
            array(false, new OrderedContainer(BaseType::INTEGER), new ResponseValidityConstraint('RESPONSE', 1, 5)),
            array(false, new RecordContainer(), new ResponseValidityConstraint('RESPONSE', 1, 1)),
            array(true, new RecordContainer(array('key' => new QtiInteger(1337))), new ResponseValidityConstraint('RESPONSE', 1, 1)),
            
            // Single cardinality tests.
            array(true, new QtiString('string!'), new ResponseValidityConstraint('RESPONSE', 1, 1)),
            array(true, new QtiInteger(1337), new ResponseValidityConstraint('RESPONSE', 1, 0)),
            
            // Multiple cardinality tests.
            array(true, new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(1337))), new ResponseValidityConstraint('RESPONSE', 1, 1)),
            array(true, new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(1337), new QtiInteger(1337))), new ResponseValidityConstraint('RESPONSE', 1, 2)),
            array(true, new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(1337), new QtiInteger(1337))), new ResponseValidityConstraint('RESPONSE', 1, 2)),
            array(false, new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(1337), new QtiInteger(1337))), new ResponseValidityConstraint('RESPONSE', 1, 1)),
            array(false, new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(1337), new QtiInteger(1337))), new ResponseValidityConstraint('RESPONSE', 0, 1)),
            array(true, new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(1337), new QtiInteger(1337))), new ResponseValidityConstraint('RESPONSE', 0, 0)),
            array(true, new RecordContainer(array('key' => new QtiInteger(1337))), new ResponseValidityConstraint('RESPONSE', 1, 1)),
            
            // PatternMask tests.
            array(false, null, new ResponseValidityConstraint('RESPONSE', 1, 1, 'string')),
            array(false, null, new ResponseValidityConstraint('RESPONSE', 1, 1, '/sd$[a-(')),
            array(true, new QtiString('string'), new ResponseValidityConstraint('RESPONSE', 1, 1, 'string')),
            array(false, new QtiString('strong'), new ResponseValidityConstraint('RESPONSE', 1, 1, 'string')),
            array(true, new MultipleContainer(BaseType::STRING, array(new QtiString('string'), new QtiString('string'))), new ResponseValidityConstraint('RESPONSE', 2, 2, 'string')),
            array(false, new MultipleContainer(BaseType::STRING, array(new QtiString('strong'), new QtiString('string'))), new ResponseValidityConstraint('RESPONSE', 2, 2, 'string')),
            array(false, new MultipleContainer(BaseType::STRING, array(new QtiString('string'), new QtiString('strong'))), new ResponseValidityConstraint('RESPONSE', 2, 2, 'string')),
            array(false, new OrderedContainer(BaseType::STRING, array(new QtiString('strong'))), new ResponseValidityConstraint('RESPONSE', 0, 1, 'string')),
            array(true, new MultipleContainer(BaseType::STRING), new ResponseValidityConstraint('RESPONSE', 0, 1, 'string')),
            array(true, new RecordContainer(), new ResponseValidityConstraint('RESPONSE', 0, 1, 'string')),
            array(true, new RecordContainer(array('key' => new QtiString('strong'))), new ResponseValidityConstraint('RESPONSE', 0, 1, 'string')),
            // -> Extreme edge-cases where the engine detects that the Record is in use with a stringInteraction (the 'stringValue' key is set).
            array(false, new RecordContainer(array('stringValue' => new QtiString('strong'))), new ResponseValidityConstraint('RESPONSE', 0, 1, 'string')),
            array(false, new RecordContainer(array('stringValue' => new QtiString(''))), new ResponseValidityConstraint('RESPONSE', 0, 1, 'string')),
            array(true, new RecordContainer(array('stringValue' => null)), new ResponseValidityConstraint('RESPONSE', 0, 1, 'string')),
        );
            
        // Associations tests.
        $constraint1 = new ResponseValidityConstraint('RESPONSE', 1, 1);
        $constraint1->addAssociationValidityConstraint(new AssociationValidityConstraint('ID1', 1, 1));
        
        $tests[] = array(true, new QtiPair('ID1', 'ID2'), $constraint1);
        $tests[] = array(true, new MultipleContainer(BaseType::PAIR, array(new QtiPair('ID1', 'ID2'))), $constraint1);
        $tests[] = array(false, new MultipleContainer(BaseType::PAIR, array(new QtiPair('ID1', 'ID2'), new QtiPair('ID1', 'ID3'))), $constraint1);
        $tests[] = array(false, null, $constraint1);
        $tests[] = array(false, new MultipleContainer(BaseType::PAIR), $constraint1);
        
        $tests[] = array(true, new QtiDirectedPair('ID1', 'ID2'), $constraint1);
        $tests[] = array(true, new MultipleContainer(BaseType::DIRECTED_PAIR, array(new QtiDirectedPair('ID1', 'ID2'))), $constraint1);
        $tests[] = array(false, new MultipleContainer(BaseType::DIRECTED_PAIR, array(new QtiDirectedPair('ID1', 'ID2'), new QtiDirectedPair('ID1', 'ID3'))), $constraint1);
        
        $constraint2 = new ResponseValidityConstraint('RESPONSE', 1, 0);
        $constraint2->addAssociationValidityConstraint(new AssociationValidityConstraint('ID1', 2, 0));
        $tests[] = array(false, null, $constraint2);
        $tests[] = array(false, new QtiPair('ID1', 'ID2'), $constraint2);
        $tests[] = array(true, new MultipleContainer(BaseType::DIRECTED_PAIR, array(new QtiDirectedPair('ID1', 'ID1'), new QtiDirectedPair('ID1', 'ID1'))), $constraint2);
        
        $constraint3 = new ResponseValidityConstraint('RESPONSE', 1, 0);
        $constraint3->addAssociationValidityConstraint(new AssociationValidityConstraint('ID1', 4, 0));
        $tests[] = array(true, new MultipleContainer(BaseType::DIRECTED_PAIR, array(new QtiDirectedPair('ID1', 'ID1'), new QtiDirectedPair('ID1', 'ID1'))), $constraint3);
        
        return $tests;
    }
    
    public function testIsResponseValidRuntimeException()
    {
        $this->setExpectedException('\\RuntimeException', "PCRE Engine error");
        
        $valid = TestUtils::isResponseValid(
            new QtiString('checkme'),
            new ResponseValidityConstraint(
                'RESPONSE',
                1,
                1,
                '/abc[A-'
            )
        );
    }
}
