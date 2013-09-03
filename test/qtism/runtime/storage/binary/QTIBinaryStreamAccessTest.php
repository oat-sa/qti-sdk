<?php

use qtism\common\datatypes\Point;

use qtism\common\datatypes\DirectedPair;
use qtism\common\datatypes\Pair;
use qtism\common\Comparable;
use qtism\common\datatypes\Duration;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\ResponseVariable;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\Container;
use qtism\runtime\common\Variable;
use qtism\runtime\storage\binary\BinaryStream;
use qtism\runtime\storage\binary\QTIBinaryStreamAccess;
use qtism\runtime\storage\binary\QTIBinaryStreamAccessException;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

class QTIBinaryStreamAccessTest extends QtiSmTestCase {
	
    /**
     * @dataProvider readVariableValueProvider
     * 
     * @param Variable $variable
     * @param string $binary
     * @param mixed $expectedValue
     */
    public function testReadVariableValue(Variable $variable, $binary, $expectedValue) {
        $stream = new BinaryStream($binary);
        $stream->open();
        $access = new QTIBinaryStreamAccess($stream);
        $access->readVariableValue($variable);
        
        if (is_scalar($expectedValue) === true) {
            $this->assertEquals($expectedValue, $variable->getValue());
        }
        else if (is_null($expectedValue) === true) {
            $this->assertSame($expectedValue, $variable->getValue());
        }
        else if ($expectedValue instanceof RecordContainer) {
            $this->assertEquals($expectedValue->getCardinality(), $variable->getCardinality());
            $this->assertTrue($expectedValue->equals($variable->getValue()));
        }
        else if ($expectedValue instanceof Container) {
            $this->assertEquals($expectedValue->getCardinality(), $variable->getCardinality());
            $this->assertEquals($expectedValue->getBaseType(), $variable->getBaseType());
            $this->assertTrue($expectedValue->equals($variable->getValue()));
        }
        else if ($expectedValue instanceof Comparable) {
            // Duration, Point, Pair, ...
            $this->assertTrue($expectedValue->equals($variable->getValue()));
        }
        else {
            // can't happen.
            $this->assertTrue(false);
        }
    }
    
    public function readVariableValueProvider() {
        $returnValue = array();
        
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::INTEGER, 45), "\x01", null);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::INTEGER), "\x00" . "\x01" . pack('l', 45), 45);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INTEGER), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('l', 0) . "\x00" . pack('l', -20) . "\x00" . pack('l', 65000), new MultipleContainer(BaseType::INTEGER, array(0, -20, 65000)));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INTEGER), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('l', 0) . "\x01" . "\x00" . pack('l', 65000), new MultipleContainer(BaseType::INTEGER, array(0, null, 65000)));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::INTEGER), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('l', 1337), new OrderedContainer(BaseType::INTEGER, array(1337)));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INTEGER), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::INTEGER));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INTEGER, new OrderedContainer(BaseType::INTEGER, array(1))), "\x01", null);
        
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::FLOAT, 45.5), "\x01", null);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::FLOAT), "\x00" . "\x01" . pack('d', 45.5), 45.5);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::FLOAT), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('d', 0.0) . "\x00" . pack('d', -20.666) . "\x00" . pack('d', 65000.56), new MultipleContainer(BaseType::FLOAT, array(0.0, -20.666, 65000.56)));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::FLOAT), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('d', 1337.666), new OrderedContainer(BaseType::FLOAT, array(1337.666)));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::FLOAT), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::FLOAT));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::FLOAT, new OrderedContainer(BaseType::FLOAT, array(0.0))), "\x01", null);
        
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::BOOLEAN, true), "\x01", null);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::BOOLEAN), "\x00" . "\x01" . "\x01", true);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::BOOLEAN), "\x00" . "\x01" . "\x00", false);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::BOOLEAN), "\x00" . "\x00" . pack('S', 3) . "\x00\x00\x00\x01\x00\x00", new MultipleContainer(BaseType::BOOLEAN, array(false, true, false)));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::BOOLEAN), "\x00" . "\x00" . pack('S', 1) . "\x00\x01", new OrderedContainer(BaseType::BOOLEAN, array(true)));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::BOOLEAN), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::BOOLEAN));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::BOOLEAN, new OrderedContainer(BaseType::BOOLEAN, array(true))), "\x01", null);
        
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::STRING, 'String!'), "\x01", null);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::STRING), "\x00" . "\x01" . pack('S', 0) . '', '');
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::STRING), "\x00" . "\x01" . pack('S', 7) . 'String!', 'String!');
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::STRING), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 3) . 'ABC' . "\x00" . pack('S', 0) . '' . "\x00" . pack('S', 7) . 'String!', new MultipleContainer(BaseType::STRING, array('ABC', '', 'String!')));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::STRING), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 7) . 'String!', new OrderedContainer(BaseType::STRING, array('String!')));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::STRING), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::STRING));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::STRING, new OrderedContainer(BaseType::STRING, array('pouet'))), "\x01", null);
        
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::IDENTIFIER, 'Identifier'), "\x01", null);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::IDENTIFIER), "\x00" . "\x01" . pack('S', 1) . 'A', 'A');
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::IDENTIFIER), "\x00" . "\x01" . pack('S', 10) . 'Identifier', 'Identifier');
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::IDENTIFIER), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 3) . 'Q01' . "\x00" . pack('S', 1) . 'A' . "\x00" . pack('S', 3) . 'Q02', new MultipleContainer(BaseType::IDENTIFIER, array('Q01', 'A', 'Q02')));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::IDENTIFIER), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 10) . 'Identifier', new OrderedContainer(BaseType::IDENTIFIER, array('Identifier')));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::IDENTIFIER), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::IDENTIFIER));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::IDENTIFIER, new OrderedContainer(BaseType::IDENTIFIER, array('OUTCOMEX'))), "\x01", null);
        
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::DURATION, new Duration('PT1S')), "\x01", null);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::DURATION), "\x00" . "\x01" . pack('S', 4) . 'PT1S', new Duration('PT1S'));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DURATION), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 4) . 'PT0S' . "\x00" . pack('S', 4) . 'PT1S' . "\x00" . pack('S', 4) . 'PT2S', new MultipleContainer(BaseType::DURATION, array(new Duration('PT0S'), new Duration('PT1S'), new Duration('PT2S'))));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::DURATION), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 6) . 'PT2M2S', new OrderedContainer(BaseType::DURATION, array(new Duration('PT2M2S'))));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DURATION), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::DURATION));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DURATION, new OrderedContainer(BaseType::DURATION, array(new Duration('PT1S')))), "\x01", null);
        
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::PAIR, new Pair('A', 'B')), "\x01", null);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::PAIR), "\x00" . "\x01" . pack('S', 1) . 'A' . pack('S', 1) . 'B', new Pair('A', 'B'));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::PAIR), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 1) . 'A' . pack('S', 1) . 'B' . "\x00" . pack('S', 1) . 'C' . pack('S', 1) . 'D' . "\x00" . pack('S', 1) . 'E' . pack('S', 1) . 'F', new MultipleContainer(BaseType::PAIR, array(new Pair('A', 'B'), new Pair('C', 'D'), new Pair('E', 'F'))));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::PAIR), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 2) . 'P1' . pack('S', 2) . 'P2', new OrderedContainer(BaseType::PAIR, array(new Pair('P1', 'P2'))));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::PAIR), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::PAIR));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::PAIR, new OrderedContainer(BaseType::PAIR, array(new Pair('my', 'pair')))), "\x01", null);
        
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::DIRECTED_PAIR, new DirectedPair('A', 'B')), "\x01", null);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::DIRECTED_PAIR), "\x00" . "\x01" . pack('S', 1) . 'A' . pack('S', 1) . 'B', new DirectedPair('A', 'B'));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 1) . 'A' . pack('S', 1) . 'B' . "\x00" . pack('S', 1) . 'C' . pack('S', 1) . 'D' . "\x00" . pack('S', 1) . 'E' . pack('S', 1) . 'F', new MultipleContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('A', 'B'), new DirectedPair('C', 'D'), new DirectedPair('E', 'F'))));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::DIRECTED_PAIR), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 2) . 'P1' . pack('S', 2) . 'P2', new OrderedContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('P1', 'P2'))));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::DIRECTED_PAIR));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DIRECTED_PAIR, new OrderedContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('my', 'pair')))), "\x01", null);
        
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::POINT, new Point(0, 1)), "\x01", null);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::POINT), "\x00" . "\x01" . pack('S', 0) . pack('S', 0), new Point(0, 0));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::POINT), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 4) . pack('S', 3) . "\x01" . "\x00" . pack('S', 2) . pack('S', 1), new MultipleContainer(BaseType::POINT, array(new Point(4, 3), null, new Point(2, 1))));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::POINT), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 6) . pack('S', 1234), new OrderedContainer(BaseType::POINT, array(new Point(6, 1234))));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::POINT), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::POINT));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::POINT, new OrderedContainer(BaseType::POINT, array(new Point(1, 1)))), "\x01", null);
        
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::INT_OR_IDENTIFIER, 45), "\x01", null);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x01" . "\x01" . pack('l', 45), 45);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x01" . "\x00" . pack('S', 10) . 'Identifier', 'Identifier');
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x00" . pack('S', 3) . "\x00" . "\x01" . pack('l', 0) . "\x00" . "\x01" . pack('l', -20) . "\x00" . "\x01" . pack('l', 65000), new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array(0, -20, 65000)));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x00" . pack('S', 3) . "\x00" . "\x00" . pack('S', 1) . 'A' . "\x00" . "\x00" . pack('S', 1) . 'B' . "\x00" . "\x00" . pack('S', 1) . 'C', new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array('A', 'B', 'C')));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x00" . pack('S', 3) . "\x00" . "\x00" . pack('S', 1) . 'A' . "\x00" . "\x01" . pack('l', 1337) . "\x00" . "\x00" . pack('S', 1) . 'C', new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array('A', 1337, 'C')));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x00" . pack('S', 3) . "\x00" . "\x01" . pack('l', 0) . "\x01" . "\x00" . "\x01" . pack('l', 65000), new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array(0, null, 65000)));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x00" . pack('S', 1) . "\x00" . "\x01" . pack('l', 1337), new OrderedContainer(BaseType::INT_OR_IDENTIFIER, array(1337)));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::INT_OR_IDENTIFIER));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INT_OR_IDENTIFIER, new OrderedContainer(BaseType::INT_OR_IDENTIFIER, array(1))), "\x01", null);
        
        // Records
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::RECORD), "\x00" . "\x00" . pack('S', 0), new RecordContainer());
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::RECORD), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 4) . 'key1' . "\x02" . pack('l', 1337), new RecordContainer(array('key1' => 1337)));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::RECORD), "\x00" . "\x00" . pack('S', 2) . "\x00" . pack('S', 4) . 'key1' . "\x02" . pack('l', 1337) . "\x00" . pack('S', 4) . 'key2' . "\x04" . pack('S', 7) . 'String!', new RecordContainer(array('key1' => 1337, 'key2' => 'String!')));
        
        return $returnValue;
    }
}