<?php
namespace qtismtest\runtime\storage\binary;

use qtismtest\QtiSmTestCase;
use qtism\runtime\tests\SessionManager;
use qtism\common\datatypes\files\DefaultFileManager;
use qtism\common\datatypes\files\FileSystemFile;
use qtism\common\datatypes\files\FileSystemFileManager;
use qtism\common\datatypes\QtiUri;
use qtism\common\datatypes\QtiIntOrIdentifier;
use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiInteger;
use qtism\common\collections\IdentifierCollection;
use qtism\data\SubmissionMode;
use qtism\data\NavigationMode;
use qtism\data\state\ShufflingGroup;
use qtism\data\state\ShufflingGroupCollection;
use qtism\data\state\Shuffling;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\runtime\tests\AssessmentItemSession;
use qtism\runtime\tests\AssessmentItemSessionState;
use qtism\runtime\storage\common\AssessmentTestSeeker;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiPair;
use qtism\common\Comparable;
use qtism\common\datatypes\QtiDuration;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\TemplateVariable;
use qtism\common\collections\Container;
use qtism\runtime\common\Variable;
use qtism\common\storage\MemoryStream;
use qtism\runtime\storage\binary\QtiBinaryStreamAccess;
use qtism\runtime\storage\binary\QtiBinaryStreamAccessException;

class QtiBinaryStreamAccessTest extends QtiSmTestCase {
	
    /**
     * @dataProvider readVariableValueProvider
     * 
     * @param Variable $variable
     * @param string $binary
     * @param mixed $expectedValue
     */
    public function testReadVariableValue(Variable $variable, $binary, $expectedValue, $valueType = QtiBinaryStreamAccess::RW_VALUE) {
        $stream = new MemoryStream($binary);
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
        $access->readVariableValue($variable, $valueType);
        
        switch ($valueType) {
            case QtiBinaryStreamAccess::RW_DEFAULTVALUE:
                $getterToCall = 'getDefaultValue';
                break;
                
            case QtiBinaryStreamAccess::RW_CORRECTRESPONSE:
                $getterToCall = 'getCorrectResponse';
                break;
                
            default:
                $getterToCall = 'getValue';
                break;
        }
        
        if (is_scalar($expectedValue) === true) {
            $this->assertEquals($expectedValue, call_user_func(array($variable, $getterToCall))->getValue());
        }
        else if (is_null($expectedValue) === true) {
            $this->assertSame($expectedValue, call_user_func(array($variable, $getterToCall)));
        }
        else if ($expectedValue instanceof RecordContainer) {
            $this->assertEquals($expectedValue->getCardinality(), $variable->getCardinality());
            $this->assertTrue($expectedValue->equals(call_user_func(array($variable, $getterToCall))));
        }
        else if ($expectedValue instanceof Container) {
            $this->assertEquals($expectedValue->getCardinality(), $variable->getCardinality());
            $this->assertEquals($expectedValue->getBaseType(), $variable->getBaseType());
            $this->assertTrue($expectedValue->equals(call_user_func(array($variable, $getterToCall))));
        }
        else if ($expectedValue instanceof Comparable) {
            // Duration, Point, Pair, ...
            $this->assertTrue($expectedValue->equals(call_user_func(array($variable, $getterToCall))));
        }
        else {
            // can't happen.
            $this->assertTrue(false);
        }
    }
    
    public function readVariableValueProvider() {
        $returnValue = array();
        
        $rw_value = QtiBinaryStreamAccess::RW_VALUE;
        $rw_defaultValue = QtiBinaryStreamAccess::RW_DEFAULTVALUE;
        $rw_correctResponse = QtiBinaryStreamAccess::RW_CORRECTRESPONSE;
        
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(45)), "\x01", null);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(45)), "\x01", null, $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::INTEGER), "\x00" . "\x01" . pack('l', 45), 45);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::INTEGER), "\x00" . "\x01" . pack('l', 45), 45, $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::INTEGER), "\x00" . "\x01" . pack('l', 45), 45, $rw_correctResponse);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INTEGER), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('l', 0) . "\x00" . pack('l', -20) . "\x00" . pack('l', 65000), new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(0), new QtiInteger(-20), new QtiInteger(65000))));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INTEGER), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('l', 0) . "\x00" . pack('l', -20) . "\x00" . pack('l', 65000), new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(0), new QtiInteger(-20), new QtiInteger(65000))), $rw_defaultValue);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INTEGER), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('l', 0) . "\x01" . "\x00" . pack('l', 65000), new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(0), null, new QtiInteger(65000))));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INTEGER), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('l', 0) . "\x01" . "\x00" . pack('l', 65000), new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(0), null, new QtiInteger(65000))), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::INTEGER), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('l', 1337), new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(1337))));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::INTEGER), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('l', 1337), new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(1337))), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::INTEGER), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('l', 1337), new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(1337))), $rw_correctResponse);
        $returnValue[] = array(new TemplateVariable('VAR', Cardinality::MULTIPLE, BaseType::INTEGER), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::INTEGER));
        $returnValue[] = array(new TemplateVariable('VAR', Cardinality::MULTIPLE, BaseType::INTEGER), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::INTEGER), $rw_defaultValue);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INTEGER, new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(1)))), "\x01", null);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INTEGER, new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(1)))), "\x01", null, $rw_defaultValue);
        
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(45.5)), "\x01", null);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(45.5)), "\x01", null, $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::FLOAT), "\x00" . "\x01" . pack('d', 45.5), 45.5);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::FLOAT), "\x00" . "\x01" . pack('d', 45.5), 45.5, $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::FLOAT), "\x00" . "\x01" . pack('d', 45.5), 45.5, $rw_correctResponse);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::FLOAT), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('d', 0.0) . "\x00" . pack('d', -20.666) . "\x00" . pack('d', 65000.56), new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(0.0), new QtiFloat(-20.666), new QtiFloat(65000.56))));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::FLOAT), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('d', 0.0) . "\x00" . pack('d', -20.666) . "\x00" . pack('d', 65000.56), new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(0.0), new QtiFloat(-20.666), new QtiFloat(65000.56))), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::FLOAT), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('d', 1337.666), new OrderedContainer(BaseType::FLOAT, array(new QtiFloat(1337.666))));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::FLOAT), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('d', 1337.666), new OrderedContainer(BaseType::FLOAT, array(new QtiFloat(1337.666))), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::FLOAT), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('d', 1337.666), new OrderedContainer(BaseType::FLOAT, array(new QtiFloat(1337.666))), $rw_correctResponse);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::FLOAT), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::FLOAT));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::FLOAT), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::FLOAT), $rw_defaultValue);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::FLOAT, new OrderedContainer(BaseType::FLOAT, array(new QtiFloat(0.0)))), "\x01", null);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::FLOAT, new OrderedContainer(BaseType::FLOAT, array(new QtiFloat(0.0)))), "\x01", null, $rw_defaultValue);
        
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(true)), "\x01", null);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(true)), "\x01", null, $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::BOOLEAN), "\x00" . "\x01" . "\x01", true);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::BOOLEAN), "\x00" . "\x01" . "\x01", true, $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::BOOLEAN), "\x00" . "\x01" . "\x01", true, $rw_correctResponse);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::BOOLEAN), "\x00" . "\x01" . "\x00", false);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::BOOLEAN), "\x00" . "\x01" . "\x00", false, $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::BOOLEAN), "\x00" . "\x01" . "\x00", false, $rw_correctResponse);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::BOOLEAN), "\x00" . "\x00" . pack('S', 3) . "\x00\x00\x00\x01\x00\x00", new MultipleContainer(BaseType::BOOLEAN, array(new QtiBoolean(false), new QtiBoolean(true), new QtiBoolean(false))));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::BOOLEAN), "\x00" . "\x00" . pack('S', 3) . "\x00\x00\x00\x01\x00\x00", new MultipleContainer(BaseType::BOOLEAN, array(new QtiBoolean(false), new QtiBoolean(true), new QtiBoolean(false))), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::BOOLEAN), "\x00" . "\x00" . pack('S', 1) . "\x00\x01", new OrderedContainer(BaseType::BOOLEAN, array(new QtiBoolean(true))));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::BOOLEAN), "\x00" . "\x00" . pack('S', 1) . "\x00\x01", new OrderedContainer(BaseType::BOOLEAN, array(new QtiBoolean(true))), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::BOOLEAN), "\x00" . "\x00" . pack('S', 1) . "\x00\x01", new OrderedContainer(BaseType::BOOLEAN, array(new QtiBoolean(true))), $rw_correctResponse);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::BOOLEAN), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::BOOLEAN));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::BOOLEAN), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::BOOLEAN), $rw_defaultValue);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::BOOLEAN, new OrderedContainer(BaseType::BOOLEAN, array(new QtiBoolean(true)))), "\x01", null);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::BOOLEAN, new OrderedContainer(BaseType::BOOLEAN, array(new QtiBoolean(true)))), "\x01", null, $rw_defaultValue);
        
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::STRING, new QtiString('String!')), "\x01", null);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::STRING, new QtiString('String!')), "\x01", null, $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::STRING), "\x00" . "\x01" . pack('S', 0) . '', '');
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::STRING), "\x00" . "\x01" . pack('S', 0) . '', '', $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::STRING), "\x00" . "\x01" . pack('S', 0) . '', '', $rw_correctResponse);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::STRING), "\x00" . "\x01" . pack('S', 7) . 'String!', 'String!');
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::STRING), "\x00" . "\x01" . pack('S', 7) . 'String!', 'String!', $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::STRING), "\x00" . "\x01" . pack('S', 7) . 'String!', 'String!', $rw_correctResponse);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::STRING), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 3) . 'ABC' . "\x00" . pack('S', 0) . '' . "\x00" . pack('S', 7) . 'String!', new MultipleContainer(BaseType::STRING, array(new QtiString('ABC'), new QtiString(''), new QtiString('String!'))));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::STRING), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 3) . 'ABC' . "\x00" . pack('S', 0) . '' . "\x00" . pack('S', 7) . 'String!', new MultipleContainer(BaseType::STRING, array(new QtiString('ABC'), new QtiString(''), new QtiString('String!'))), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::STRING), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 7) . 'String!', new OrderedContainer(BaseType::STRING, array(new QtiString('String!'))));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::STRING), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 7) . 'String!', new OrderedContainer(BaseType::STRING, array(new QtiString('String!'))), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::STRING), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 7) . 'String!', new OrderedContainer(BaseType::STRING, array(new QtiString('String!'))), $rw_correctResponse);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::STRING), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::STRING));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::STRING), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::STRING), $rw_defaultValue);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::STRING, new OrderedContainer(BaseType::STRING, array(new QtiString('pouet')))), "\x01", null, $rw_defaultValue);
        
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('Identifier')), "\x01", null);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('Identifier')), "\x01", null, $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::IDENTIFIER), "\x00" . "\x01" . pack('S', 1) . 'A', 'A');
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::IDENTIFIER), "\x00" . "\x01" . pack('S', 1) . 'A', 'A', $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::IDENTIFIER), "\x00" . "\x01" . pack('S', 1) . 'A', 'A', $rw_correctResponse);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::IDENTIFIER), "\x00" . "\x01" . pack('S', 10) . 'Identifier', 'Identifier');
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::IDENTIFIER), "\x00" . "\x01" . pack('S', 10) . 'Identifier', 'Identifier', $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::IDENTIFIER), "\x00" . "\x01" . pack('S', 10) . 'Identifier', 'Identifier', $rw_correctResponse);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::IDENTIFIER), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 3) . 'Q01' . "\x00" . pack('S', 1) . 'A' . "\x00" . pack('S', 3) . 'Q02', new MultipleContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('Q01'), new QtiIdentifier('A'), new QtiIdentifier('Q02'))));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::IDENTIFIER), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 3) . 'Q01' . "\x00" . pack('S', 1) . 'A' . "\x00" . pack('S', 3) . 'Q02', new MultipleContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('Q01'), new QtiIdentifier('A'), new QtiIdentifier('Q02'))), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::IDENTIFIER), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 10) . 'Identifier', new OrderedContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('Identifier'))));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::IDENTIFIER), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 10) . 'Identifier', new OrderedContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('Identifier'))), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::IDENTIFIER), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 10) . 'Identifier', new OrderedContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('Identifier'))), $rw_correctResponse);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::IDENTIFIER), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::IDENTIFIER));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::IDENTIFIER), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::IDENTIFIER), $rw_defaultValue);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::IDENTIFIER, new OrderedContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('OUTCOMEX')))), "\x01", null);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::IDENTIFIER, new OrderedContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('OUTCOMEX')))), "\x01", null, $rw_defaultValue);
        
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT1S')), "\x01", null);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT1S')), "\x01", null, $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::DURATION), "\x00" . "\x01" . pack('S', 4) . 'PT1S', new QtiDuration('PT1S'));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::DURATION), "\x00" . "\x01" . pack('S', 4) . 'PT1S', new QtiDuration('PT1S'), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::DURATION), "\x00" . "\x01" . pack('S', 4) . 'PT1S', new QtiDuration('PT1S'), $rw_correctResponse);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DURATION), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 4) . 'PT0S' . "\x00" . pack('S', 4) . 'PT1S' . "\x00" . pack('S', 4) . 'PT2S', new MultipleContainer(BaseType::DURATION, array(new QtiDuration('PT0S'), new QtiDuration('PT1S'), new QtiDuration('PT2S'))));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DURATION), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 4) . 'PT0S' . "\x00" . pack('S', 4) . 'PT1S' . "\x00" . pack('S', 4) . 'PT2S', new MultipleContainer(BaseType::DURATION, array(new QtiDuration('PT0S'), new QtiDuration('PT1S'), new QtiDuration('PT2S'))), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::DURATION), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 6) . 'PT2M2S', new OrderedContainer(BaseType::DURATION, array(new QtiDuration('PT2M2S'))));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::DURATION), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 6) . 'PT2M2S', new OrderedContainer(BaseType::DURATION, array(new QtiDuration('PT2M2S'))), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::DURATION), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 6) . 'PT2M2S', new OrderedContainer(BaseType::DURATION, array(new QtiDuration('PT2M2S'))), $rw_correctResponse);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DURATION), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::DURATION));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DURATION), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::DURATION), $rw_defaultValue);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DURATION, new OrderedContainer(BaseType::DURATION, array(new QtiDuration('PT1S')))), "\x01", null);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DURATION, new OrderedContainer(BaseType::DURATION, array(new QtiDuration('PT1S')))), "\x01", null, $rw_defaultValue);
        
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::PAIR, new QtiPair('A', 'B')), "\x01", null);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::PAIR, new QtiPair('A', 'B')), "\x01", null, $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::PAIR), "\x00" . "\x01" . pack('S', 1) . 'A' . pack('S', 1) . 'B', new QtiPair('A', 'B'));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::PAIR), "\x00" . "\x01" . pack('S', 1) . 'A' . pack('S', 1) . 'B', new QtiPair('A', 'B'), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::PAIR), "\x00" . "\x01" . pack('S', 1) . 'A' . pack('S', 1) . 'B', new QtiPair('A', 'B'), $rw_correctResponse);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::PAIR), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 1) . 'A' . pack('S', 1) . 'B' . "\x00" . pack('S', 1) . 'C' . pack('S', 1) . 'D' . "\x00" . pack('S', 1) . 'E' . pack('S', 1) . 'F', new MultipleContainer(BaseType::PAIR, array(new QtiPair('A', 'B'), new QtiPair('C', 'D'), new QtiPair('E', 'F'))));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::PAIR), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 1) . 'A' . pack('S', 1) . 'B' . "\x00" . pack('S', 1) . 'C' . pack('S', 1) . 'D' . "\x00" . pack('S', 1) . 'E' . pack('S', 1) . 'F', new MultipleContainer(BaseType::PAIR, array(new QtiPair('A', 'B'), new QtiPair('C', 'D'), new QtiPair('E', 'F'))), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::PAIR), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 2) . 'P1' . pack('S', 2) . 'P2', new OrderedContainer(BaseType::PAIR, array(new QtiPair('P1', 'P2'))));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::PAIR), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 2) . 'P1' . pack('S', 2) . 'P2', new OrderedContainer(BaseType::PAIR, array(new QtiPair('P1', 'P2'))), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::PAIR), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 2) . 'P1' . pack('S', 2) . 'P2', new OrderedContainer(BaseType::PAIR, array(new QtiPair('P1', 'P2'))), $rw_correctResponse);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::PAIR), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::PAIR));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::PAIR), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::PAIR), $rw_defaultValue);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::PAIR, new OrderedContainer(BaseType::PAIR, array(new QtiPair('my', 'pair')))), "\x01", null);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::PAIR, new OrderedContainer(BaseType::PAIR, array(new QtiPair('my', 'pair')))), "\x01", null, $rw_defaultValue);
        
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::DIRECTED_PAIR, new QtiDirectedPair('A', 'B')), "\x01", null);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::DIRECTED_PAIR, new QtiDirectedPair('A', 'B')), "\x01", null, $rw_defaultValue);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::DIRECTED_PAIR, new QtiDirectedPair('A', 'B')), "\x01", null);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::DIRECTED_PAIR, new QtiDirectedPair('A', 'B')), "\x01", null, $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::DIRECTED_PAIR), "\x00" . "\x01" . pack('S', 1) . 'A' . pack('S', 1) . 'B', new QtiDirectedPair('A', 'B'));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::DIRECTED_PAIR), "\x00" . "\x01" . pack('S', 1) . 'A' . pack('S', 1) . 'B', new QtiDirectedPair('A', 'B'), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::DIRECTED_PAIR), "\x00" . "\x01" . pack('S', 1) . 'A' . pack('S', 1) . 'B', new QtiDirectedPair('A', 'B'), $rw_correctResponse);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 1) . 'A' . pack('S', 1) . 'B' . "\x00" . pack('S', 1) . 'C' . pack('S', 1) . 'D' . "\x00" . pack('S', 1) . 'E' . pack('S', 1) . 'F', new MultipleContainer(BaseType::DIRECTED_PAIR, array(new QtiDirectedPair('A', 'B'), new QtiDirectedPair('C', 'D'), new QtiDirectedPair('E', 'F'))));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 1) . 'A' . pack('S', 1) . 'B' . "\x00" . pack('S', 1) . 'C' . pack('S', 1) . 'D' . "\x00" . pack('S', 1) . 'E' . pack('S', 1) . 'F', new MultipleContainer(BaseType::DIRECTED_PAIR, array(new QtiDirectedPair('A', 'B'), new QtiDirectedPair('C', 'D'), new QtiDirectedPair('E', 'F'))), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::DIRECTED_PAIR), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 2) . 'P1' . pack('S', 2) . 'P2', new OrderedContainer(BaseType::DIRECTED_PAIR, array(new QtiDirectedPair('P1', 'P2'))));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::DIRECTED_PAIR), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 2) . 'P1' . pack('S', 2) . 'P2', new OrderedContainer(BaseType::DIRECTED_PAIR, array(new QtiDirectedPair('P1', 'P2'))), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::DIRECTED_PAIR), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 2) . 'P1' . pack('S', 2) . 'P2', new OrderedContainer(BaseType::DIRECTED_PAIR, array(new QtiDirectedPair('P1', 'P2'))), $rw_correctResponse);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::DIRECTED_PAIR));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::DIRECTED_PAIR), $rw_defaultValue);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DIRECTED_PAIR, new OrderedContainer(BaseType::DIRECTED_PAIR, array(new QtiDirectedPair('my', 'pair')))), "\x01", null);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DIRECTED_PAIR, new OrderedContainer(BaseType::DIRECTED_PAIR, array(new QtiDirectedPair('my', 'pair')))), "\x01", null, $rw_defaultValue);
        
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(0, 1)), "\x01", null);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(0, 1)), "\x01", null, $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::POINT), "\x00" . "\x01" . pack('S', 0) . pack('S', 0), new QtiPoint(0, 0));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::POINT), "\x00" . "\x01" . pack('S', 0) . pack('S', 0), new QtiPoint(0, 0), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::POINT), "\x00" . "\x01" . pack('S', 0) . pack('S', 0), new QtiPoint(0, 0), $rw_correctResponse);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::POINT), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 4) . pack('S', 3) . "\x01" . "\x00" . pack('S', 2) . pack('S', 1), new MultipleContainer(BaseType::POINT, array(new QtiPoint(4, 3), null, new QtiPoint(2, 1))));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::POINT), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 4) . pack('S', 3) . "\x01" . "\x00" . pack('S', 2) . pack('S', 1), new MultipleContainer(BaseType::POINT, array(new QtiPoint(4, 3), null, new QtiPoint(2, 1))), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::POINT), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 6) . pack('S', 1234), new OrderedContainer(BaseType::POINT, array(new QtiPoint(6, 1234))));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::POINT), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 6) . pack('S', 1234), new OrderedContainer(BaseType::POINT, array(new QtiPoint(6, 1234))), $rw_correctResponse);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::POINT), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::POINT));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::POINT), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::POINT), $rw_defaultValue);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::POINT, new OrderedContainer(BaseType::POINT, array(new QtiPoint(1, 1)))), "\x01", null);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::POINT, new OrderedContainer(BaseType::POINT, array(new QtiPoint(1, 1)))), "\x01", null, $rw_defaultValue);
        
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::INT_OR_IDENTIFIER, new QtiIntOrIdentifier(45)), "\x01", null);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::INT_OR_IDENTIFIER, new QtiIntOrIdentifier(45)), "\x01", null, $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x01" . "\x01" . pack('l', 45), 45);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x01" . "\x01" . pack('l', 45), 45, $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x01" . "\x01" . pack('l', 45), 45, $rw_correctResponse);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x01" . "\x00" . pack('S', 10) . 'Identifier', 'Identifier');
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x01" . "\x00" . pack('S', 10) . 'Identifier', 'Identifier', $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x01" . "\x00" . pack('S', 10) . 'Identifier', 'Identifier', $rw_correctResponse);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x00" . pack('S', 3) . "\x00" . "\x01" . pack('l', 0) . "\x00" . "\x01" . pack('l', -20) . "\x00" . "\x01" . pack('l', 65000), new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array(new QtiIntOrIdentifier(0), new QtiIntOrIdentifier(-20), new QtiIntOrIdentifier(65000))));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x00" . pack('S', 3) . "\x00" . "\x01" . pack('l', 0) . "\x00" . "\x01" . pack('l', -20) . "\x00" . "\x01" . pack('l', 65000), new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array(new QtiIntOrIdentifier(0), new QtiIntOrIdentifier(-20), new QtiIntOrIdentifier(65000))), $rw_defaultValue);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x00" . pack('S', 3) . "\x00" . "\x00" . pack('S', 1) . 'A' . "\x00" . "\x00" . pack('S', 1) . 'B' . "\x00" . "\x00" . pack('S', 1) . 'C', new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array(new QtiIntOrIdentifier('A'), new QtiIntOrIdentifier('B'), new QtiIntOrIdentifier('C'))));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x00" . pack('S', 3) . "\x00" . "\x00" . pack('S', 1) . 'A' . "\x00" . "\x00" . pack('S', 1) . 'B' . "\x00" . "\x00" . pack('S', 1) . 'C', new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array(new QtiIntOrIdentifier('A'), new QtiIntOrIdentifier('B'), new QtiIntOrIdentifier('C'))), $rw_defaultValue);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x00" . pack('S', 3) . "\x00" . "\x00" . pack('S', 1) . 'A' . "\x00" . "\x01" . pack('l', 1337) . "\x00" . "\x00" . pack('S', 1) . 'C', new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array(new QtiIntOrIdentifier('A'), new QtiIntOrIdentifier(1337), new QtiIntOrIdentifier('C'))));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x00" . pack('S', 3) . "\x00" . "\x00" . pack('S', 1) . 'A' . "\x00" . "\x01" . pack('l', 1337) . "\x00" . "\x00" . pack('S', 1) . 'C', new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array(new QtiIntOrIdentifier('A'), new QtiIntOrIdentifier(1337), new QtiIntOrIdentifier('C'))), $rw_defaultValue);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x00" . pack('S', 3) . "\x00" . "\x01" . pack('l', 0) . "\x01" . "\x00" . "\x01" . pack('l', 65000), new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array(new QtiIntOrIdentifier(0), null, new QtiIntOrIdentifier(65000))));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x00" . pack('S', 3) . "\x00" . "\x01" . pack('l', 0) . "\x01" . "\x00" . "\x01" . pack('l', 65000), new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array(new QtiIntOrIdentifier(0), null, new QtiIntOrIdentifier(65000))), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x00" . pack('S', 1) . "\x00" . "\x01" . pack('l', 1337), new OrderedContainer(BaseType::INT_OR_IDENTIFIER, array(new QtiIntOrIdentifier(1337))));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x00" . pack('S', 1) . "\x00" . "\x01" . pack('l', 1337), new OrderedContainer(BaseType::INT_OR_IDENTIFIER, array(new QtiIntOrIdentifier(1337))), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x00" . pack('S', 1) . "\x00" . "\x01" . pack('l', 1337), new OrderedContainer(BaseType::INT_OR_IDENTIFIER, array(new QtiIntOrIdentifier(1337))), $rw_correctResponse);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::INT_OR_IDENTIFIER));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER), "\x00" . "\x00" . pack('S', 0), new MultipleContainer(BaseType::INT_OR_IDENTIFIER), $rw_defaultValue);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INT_OR_IDENTIFIER, new OrderedContainer(BaseType::INT_OR_IDENTIFIER, array(new QtiIntOrIdentifier(1)))), "\x01", null);
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INT_OR_IDENTIFIER, new OrderedContainer(BaseType::INT_OR_IDENTIFIER, array(new QtiIntOrIdentifier(1)))), "\x01", null, $rw_defaultValue);
        
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::FILE), "\x01", null);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::FILE), "\x01", null, $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::FILE), "\x01", null, $rw_correctResponse);
        $path = self::samplesDir() . 'datatypes/file/text-plain_text_data.txt';
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::FILE), "\x00" . "\x01" . pack('S', strlen($path)) . $path , FileSystemFile::retrieveFile($path));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::FILE), "\x00" . "\x01" . pack('S', strlen($path)) . $path , FileSystemFile::retrieveFile($path), $rw_defaultValue);
        $path1 = $path;
        $path2 = self::samplesDir() . 'datatypes/file/text-plain_noname.txt';
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::FILE), "\x00" . "\x00" . pack('S', 2) . "\x00" . pack('S', strlen($path1)) . $path1 . "\x00" . pack('S', strlen($path2)) . $path2, new MultipleContainer(BaseType::FILE, array(FileSystemFile::retrieveFile($path1), FileSystemFile::retrieveFile($path2))));
        $returnValue[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::FILE), "\x00" . "\x00" . pack('S', 2) . "\x00" . pack('S', strlen($path1)) . $path1 . "\x00" . pack('S', strlen($path2)) . $path2, new MultipleContainer(BaseType::FILE, array(FileSystemFile::retrieveFile($path1), FileSystemFile::retrieveFile($path2))), $rw_defaultValue);
        
        // Records
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::RECORD), "\x00" . "\x00" . pack('S', 0), new RecordContainer());
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::RECORD), "\x00" . "\x00" . pack('S', 0), new RecordContainer(), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::RECORD), "\x00" . "\x00" . pack('S', 0), new RecordContainer(), $rw_correctResponse);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::RECORD), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 4) . 'key1' . "\x02" . pack('l', 1337), new RecordContainer(array('key1' => new QtiInteger(1337))));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::RECORD), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 4) . 'key1' . "\x02" . pack('l', 1337), new RecordContainer(array('key1' => new QtiInteger(1337))), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::RECORD), "\x00" . "\x00" . pack('S', 1) . "\x00" . pack('S', 4) . 'key1' . "\x02" . pack('l', 1337), new RecordContainer(array('key1' => new QtiInteger(1337))), $rw_correctResponse);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::RECORD), "\x00" . "\x00" . pack('S', 2) . "\x00" . pack('S', 4) . 'key1' . "\x02" . pack('l', 1337) . "\x00" . pack('S', 4) . 'key2' . "\x04" . pack('S', 7) . 'String!', new RecordContainer(array('key1' => new QtiInteger(1337), 'key2' => new QtiString('String!'))));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::RECORD), "\x00" . "\x00" . pack('S', 2) . "\x00" . pack('S', 4) . 'key1' . "\x02" . pack('l', 1337) . "\x00" . pack('S', 4) . 'key2' . "\x04" . pack('S', 7) . 'String!', new RecordContainer(array('key1' => new QtiInteger(1337), 'key2' => new QtiString('String!'))), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::RECORD), "\x00" . "\x00" . pack('S', 2) . "\x00" . pack('S', 4) . 'key1' . "\x02" . pack('l', 1337) . "\x00" . pack('S', 4) . 'key2' . "\x04" . pack('S', 7) . 'String!', new RecordContainer(array('key1' => new QtiInteger(1337), 'key2' => new QtiString('String!'))), $rw_correctResponse);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::RECORD), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 4) . 'key1' . "\x02" . pack('l', 1337) . "\x01" . pack('S', 4) . 'key2' . "\x00" . pack('S', 4) . 'key3' . "\x04" . pack('S', 7) . 'String!', new RecordContainer(array('key1' => new QtiInteger(1337), 'key2' => null, 'key3' => new QtiString('String!'))));
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::RECORD), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 4) . 'key1' . "\x02" . pack('l', 1337) . "\x01" . pack('S', 4) . 'key2' . "\x00" . pack('S', 4) . 'key3' . "\x04" . pack('S', 7) . 'String!', new RecordContainer(array('key1' => new QtiInteger(1337), 'key2' => null, 'key3' => new QtiString('String!'))), $rw_defaultValue);
        $returnValue[] = array(new ResponseVariable('VAR', Cardinality::RECORD), "\x00" . "\x00" . pack('S', 3) . "\x00" . pack('S', 4) . 'key1' . "\x02" . pack('l', 1337) . "\x01" . pack('S', 4) . 'key2' . "\x00" . pack('S', 4) . 'key3' . "\x04" . pack('S', 7) . 'String!', new RecordContainer(array('key1' => new QtiInteger(1337), 'key2' => null, 'key3' => new QtiString('String!'))), $rw_correctResponse);
        
        return $returnValue;
    }
    
    public function testReadVariableValueEmptyStream() {
        // Empty stream.
        $stream = new MemoryStream('');
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
        
        $this->setExpectedException(
            'qtism\\runtime\\storage\\binary\\QtiBinaryStreamAccessException',
            'An error occured while reading a Variable value.',
            QtiBinaryStreamAccessException::VARIABLE
        );
        
        $access->readVariableValue(
            new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::STRING),
            QtiBinaryStreamAccess::RW_VALUE
        );
    }
    
    public function testReadVariableValueTypeMismatch() {
        $bin = "\x00" . "\x01" . pack('S', 1) . '1' . pack('S', 1) . '2';
        $stream = new MemoryStream($bin);
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
        
        $this->setExpectedException(
            'qtism\\runtime\\storage\\binary\\QtiBinaryStreamAccessException',
            "Datatype mismatch for variable 'VAR'.",
            QtiBinaryStreamAccessException::VARIABLE
        );
        
        $access->readVariableValue(
            new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::PAIR),
            QtiBinaryStreamAccess::RW_VALUE
        );
    }
    
    /**
     * @dataProvider writeVariableValueProvider
     * 
     * @param Variable $variable
     */
    public function testWriteVariableValue(Variable $variable, $valueType = QtiBinaryStreamAccess::RW_VALUE) {
        $stream = new MemoryStream();
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
        
        switch ($valueType) {
            case QtiBinaryStreamAccess::RW_DEFAULTVALUE:
                $setterToCall = 'setDefaultValue';
                $getterToCall = 'getDefaultValue';
                break;
        
            case QtiBinaryStreamAccess::RW_CORRECTRESPONSE:
                $setterToCall = 'setCorrectResponse';
                $getterToCall = 'getCorrectResponse';
                break;
        
            default:
                $setterToCall = 'setValue';
                $getterToCall = 'getValue';
                break;
        }
        
        // Write the variable value.
        $access->writeVariableValue($variable, $valueType);
        $stream->rewind();
        
        $testVariable = clone $variable;
        // Reset the value of $testVariable.
        call_user_func(array($testVariable, $setterToCall), null);
        
        // Read what we just wrote.
        $access->readVariableValue($testVariable, $valueType);
        
        $originalValue = call_user_func(array($variable, $getterToCall));
        $readValue = call_user_func(array($testVariable, $getterToCall));
        
        // Compare.
        if (is_null($originalValue) === true) {
            $this->assertSame($originalValue, $readValue);
        }
        else if (is_scalar($originalValue) === true) {
            $this->assertEquals($originalValue, $readValue);
        }
        else if ($originalValue instanceof RecordContainer) {
            $this->assertEquals($originalValue->getCardinality(), $readValue->getCardinality());
            $this->assertTrue($readValue->equals($originalValue));
        }
        else if ($originalValue instanceof Container) {
            // MULTIPLE or ORDERED container.
            $this->assertEquals($originalValue->getCardinality(), $readValue->getCardinality());
            $this->assertEquals($readValue->getBaseType(), $readValue->getBaseType());
            $this->assertTrue($readValue->equals($originalValue), $originalValue . " != " . $readValue);
        }
        else if ($originalValue instanceof Comparable) {
            // Complex QTI Runtime object.
            $this->assertTrue($readValue->equals($originalValue));
        }
        else {
            // Unknown datatype.
            $this->assertTrue(false);
        }
    }
    
    public function writeVariableValueProvider() {
        
        $rw_value = QtiBinaryStreamAccess::RW_VALUE;
        $rw_defaultValue = QtiBinaryStreamAccess::RW_DEFAULTVALUE;
        $rw_correctResponse = QtiBinaryStreamAccess::RW_CORRECTRESPONSE;
        
        $data = array();
        
        // -- Integers
        $data[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(26)));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(-34455)));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::INTEGER));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INTEGER));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INTEGER));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INTEGER, new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(-2147483647)))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INTEGER, new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(2147483647)))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INTEGER, new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(0), new QtiInteger(-1), new QtiInteger(1), new QtiInteger(-200000), new QtiInteger(200000)))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INTEGER, new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(0), new QtiInteger(-1), new QtiInteger(1), new QtiInteger(-200000), new QtiInteger(200000)))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INTEGER, new MultipleContainer(BaseType::INTEGER, array(null))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INTEGER, new OrderedContainer(BaseType::INTEGER, array(null))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INTEGER, new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(0), null, new QtiInteger(1), null, new QtiInteger(200000)))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INTEGER, new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(0), null, new QtiInteger(1), null, new QtiInteger(200000)))));
        
        $v = new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::INTEGER); $v->setDefaultValue(null); $data[] = array($v, $rw_defaultValue);
        $v = new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::INTEGER); $v->setDefaultValue(new QtiInteger(26)); $data[] = array($v, $rw_defaultValue);
        $v = new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::INTEGER); $v->setCorrectResponse(new QtiInteger(26)); $data[] = array($v, $rw_correctResponse);
        $v = new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::INTEGER); $v->setDefaultValue(new QtiInteger(-34455)); $data[] = array($v, $rw_defaultValue);
        $v = new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INTEGER); $v->setDefaultValue(new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(-2147483647)))); $data[] = array($v, $rw_defaultValue);
        $v = new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::INTEGER); $v->setCorrectResponse(new OrderedContainer(BaseType::INTEGER, array(new QtiInteger(2147483647)))); $data[] = array($v, $rw_correctResponse);
        
        // -- Floats
        $data[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(26.1)));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(-34455.0)));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::FLOAT));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::FLOAT));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::FLOAT));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::FLOAT, new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(-21474.654)))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::FLOAT, new OrderedContainer(BaseType::FLOAT, array(new QtiFloat(21474.3)))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::FLOAT, new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(0.0), new QtiFloat(-1.1), new QtiFloat(1.1), new QtiFloat(-200000.005), new QtiFloat(200000.005)))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::FLOAT, new OrderedContainer(BaseType::FLOAT, array(new QtiFloat(0.0), new QtiFloat(-1.1), new QtiFloat(1.1), new QtiFloat(-200000.005), new QtiFloat(200000.005)))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::FLOAT, new MultipleContainer(BaseType::FLOAT, array(null))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::FLOAT, new OrderedContainer(BaseType::FLOAT, array(null))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::FLOAT, new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(0.0), null, new QtiFloat(1.1), null, new QtiFloat(200000.005)))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::FLOAT, new OrderedContainer(BaseType::FLOAT, array(new QtiFloat(0.0), null, new QtiFloat(1.1), null, new QtiFloat(200000.005)))));
        
        $v = new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::FLOAT); $v->setDefaultValue(new QtiFloat(26.1)); $data[] = array($v, $rw_defaultValue);
        $v = new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::FLOAT); $v->setCorrectResponse(new QtiFloat(26.1)); $data[] = array($v, $rw_correctResponse);
        $v = new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::FLOAT); $v->setDefaultValue(new OrderedContainer(BaseType::FLOAT, array(new QtiFloat(0.0), new QtiFloat(-1.1), new QtiFloat(1.1), new QtiFloat(-200000.005), new QtiFloat(200000.005)))); $data[] = array($v, $rw_defaultValue);
        $v = new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::FLOAT); $v->setDefaultValue(new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(0.0), new QtiFloat(-1.1), new QtiFloat(1.1), new QtiFloat(-200000.005), new QtiFloat(200000.005)))); $data[] = array($v, $rw_defaultValue);
        // $data[] = array();
        // -- Booleans
        $data[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(true)));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(false)));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::BOOLEAN));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::BOOLEAN));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::BOOLEAN));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::BOOLEAN, new MultipleContainer(BaseType::BOOLEAN, array(new QtiBoolean(true)))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::BOOLEAN, new OrderedContainer(BaseType::BOOLEAN, array(new QtiBoolean(false)))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::BOOLEAN, new MultipleContainer(BaseType::BOOLEAN, array(new QtiBoolean(false), new QtiBoolean(true), new QtiBoolean(false), new QtiBoolean(true), new QtiBoolean(true)))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::BOOLEAN, new OrderedContainer(BaseType::BOOLEAN, array(new QtiBoolean(false), new QtiBoolean(true), new QtiBoolean(false), new QtiBoolean(true), new QtiBoolean(false)))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::BOOLEAN, new MultipleContainer(BaseType::BOOLEAN, array(null))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::BOOLEAN, new OrderedContainer(BaseType::BOOLEAN, array(null))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::BOOLEAN, new MultipleContainer(BaseType::BOOLEAN, array(new QtiBoolean(false), null, new QtiBoolean(true), null, new QtiBoolean(false)))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::BOOLEAN, new OrderedContainer(BaseType::BOOLEAN, array(new QtiBoolean(false), null, new QtiBoolean(true), null, new QtiBoolean(false)))));
        
        $v = new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::BOOLEAN); $v->setDefaultValue(new QtiBoolean(true)); $data[] = array($v, $rw_defaultValue);
        $v = new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::BOOLEAN); $v->setCorrectResponse(new QtiBoolean(true)); $data[] = array($v, $rw_correctResponse);
        $v = new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::IDENTIFIER); $v->setDefaultValue(new MultipleContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('identifier1'), new QtiIdentifier('identifier2'), new QtiIdentifier('identifier3'), new QtiIdentifier('identifier4'), new QtiIdentifier('identifier5')))); $data[] = array($v, $rw_defaultValue);
        
        // -- Identifiers
        $data[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('identifier')));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('non-identifier')));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::IDENTIFIER));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::IDENTIFIER));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::IDENTIFIER));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::IDENTIFIER, new MultipleContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('identifier')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::IDENTIFIER, new OrderedContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('identifier')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::IDENTIFIER, new MultipleContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('identifier1'), new QtiIdentifier('identifier2'), new QtiIdentifier('identifier3'), new QtiIdentifier('identifier4'), new QtiIdentifier('identifier5')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::IDENTIFIER, new OrderedContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('identifier1'), new QtiIdentifier('identifier2'), new QtiIdentifier('identifier3'), new QtiIdentifier('X-Y-Z'), new QtiIdentifier('identifier4')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::IDENTIFIER, new MultipleContainer(BaseType::IDENTIFIER, array(null))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::IDENTIFIER, new OrderedContainer(BaseType::IDENTIFIER, array(null))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::IDENTIFIER, new MultipleContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('identifier1'), null, new QtiIdentifier('identifier2'), null, new QtiIdentifier('identifier3')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::IDENTIFIER, new OrderedContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('identifier1'), null, new QtiIdentifier('identifier2'), null, new QtiIdentifier('identifier3')))));
        
        $v = new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::IDENTIFIER); $v->setDefaultValue(new QtiIdentifier('non-identifier')); $data[] = array($v, $rw_defaultValue);
        $v = new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::IDENTIFIER); $v->setDefaultValue(new QtiIdentifier('non-identifier')); $data[] = array($v, $rw_correctResponse);
        
        // -- URIs
        $data[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::URI, new QtiUri('http://www.my.uri')));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::URI, new QtiUri('http://www.my.uri')));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::URI));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::URI));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::URI));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::URI, new MultipleContainer(BaseType::URI, array(new QtiUri('http://www.my.uri')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::URI, new OrderedContainer(BaseType::URI, array(new QtiUri('http://www.my.uri')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::URI, new MultipleContainer(BaseType::URI, array(new QtiUri('http://www.my.uri1'), new QtiUri('http://www.my.uri2'), new QtiUri('http://www.my.uri3'), new QtiUri('http://www.my.uri4'), new QtiUri('http://www.my.uri6')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::URI, new OrderedContainer(BaseType::URI, array(new QtiUri('http://www.my.uri1'), new QtiUri('http://www.my.uri2'), new QtiUri('http://www.my.uri3'), new QtiUri('http://www.my.uri4'), new QtiUri('http://www.my.uri5')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::URI, new MultipleContainer(BaseType::URI, array(null))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::URI, new OrderedContainer(BaseType::URI, array(null))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::URI, new MultipleContainer(BaseType::URI, array(new QtiUri('http://www.my.uri1'), null, new QtiUri('http://www.my.uri2'), null, new QtiUri('http://www.my.uri3')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::URI, new OrderedContainer(BaseType::URI, array(new QtiUri('http://www.my.uri1'), null, new QtiUri('http://www.my.uri2'), null, new QtiUri('http://www.my.uri3')))));
        
        $v = new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::URI); $v->setDefaultValue(new QtiUri('http://www.my.uri')); $data[] = array($v, $rw_defaultValue);
        $v = new ResponseVariable('VAR', Cardinality::MULTIPLE, BaseType::URI); $v->setDefaultValue(new MultipleContainer(BaseType::URI, array(new QtiUri('http://www.my.uri1'), null, new QtiUri('http://www.my.uri2'), null, new QtiUri('http://www.my.uri3')))); $data[] = array($v, $rw_defaultValue);
        $v = new ResponseVariable('VAR', Cardinality::MULTIPLE, BaseType::URI); $v->setCorrectResponse(new MultipleContainer(BaseType::URI, array(new QtiUri('http://www.my.uri1'), null, new QtiUri('http://www.my.uri2'), null, new QtiUri('http://www.my.uri3')))); $data[] = array($v, $rw_correctResponse);
        
        // -- Durations
        $data[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('P3DT2H1S')));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::DURATION));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DURATION));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DURATION));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DURATION, new MultipleContainer(BaseType::DURATION, array(new QtiDuration('PT2S')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DURATION, new OrderedContainer(BaseType::DURATION, array(new QtiDuration('P2YT2S')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DURATION, new MultipleContainer(BaseType::DURATION, array(new QtiDuration('PT1S'), new QtiDuration('PT2S'), new QtiDuration('PT3S'), new QtiDuration('PT4S'), new QtiDuration('PT5S')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DURATION, new OrderedContainer(BaseType::DURATION, array(new QtiDuration('PT1S'), new QtiDuration('PT2S'), new QtiDuration('PT3S'), new QtiDuration('PT4S'), new QtiDuration('PT5S')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DURATION, new MultipleContainer(BaseType::DURATION, array(null))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DURATION, new OrderedContainer(BaseType::DURATION, array(null))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DURATION, new MultipleContainer(BaseType::DURATION, array(new QtiDuration('P4D'), null, new QtiDuration('P10D'), null, new QtiDuration('P20D')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DURATION, new OrderedContainer(BaseType::DURATION, array(new QtiDuration('P4D'), null, new QtiDuration('P10D'), null, new QtiDuration('P20D')))));
        
        $v = new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::DURATION); $v->setDefaultValue(new QtiDuration('P3DT2H1S')); $data[] = array($v, $rw_defaultValue);
        $v = new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::DURATION); $v->setDefaultValue(new OrderedContainer(BaseType::DURATION, array(new QtiDuration('PT1S'), new QtiDuration('PT2S'), new QtiDuration('PT3S'), new QtiDuration('PT4S'), new QtiDuration('PT5S')))); array($v, $rw_defaultValue);
        $v = new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::DURATION); $v->setCorrectResponse(new OrderedContainer(BaseType::DURATION, array(new QtiDuration('PT1S'), new QtiDuration('PT2S'), new QtiDuration('PT3S'), new QtiDuration('PT4S'), new QtiDuration('PT5S')))); array($v, $rw_correctResponse);
        
        // -- Pairs
        $data[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::PAIR, new QtiPair('A', 'B')));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::PAIR));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::PAIR));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::PAIR));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, array(new QtiPair('A', 'B')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::PAIR, new OrderedContainer(BaseType::PAIR, array(new QtiPair('A', 'B')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, array(new QtiPair('A', 'B'), new QtiPair('C', 'D'), new QtiPair('E', 'F'), new QtiPair('G', 'H'), new QtiPair('I', 'J')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::PAIR, new OrderedContainer(BaseType::PAIR, array(new QtiPair('A', 'B'), new QtiPair('C', 'D'), new QtiPair('E', 'F'), new QtiPair('G', 'H'), new QtiPair('I', 'J')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, array(null))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::PAIR, new OrderedContainer(BaseType::PAIR, array(null))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, array(new QtiPair('A', 'B'), null, new QtiPair('C', 'D'), null, new QtiPair('E', 'F')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::PAIR, new OrderedContainer(BaseType::PAIR, array(new QtiPair('A', 'B'), null, new QtiPair('D', 'E'), null, new QtiPair('F', 'G')))));
        
        $v = new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::PAIR); $v->setDefaultValue(new QtiPair('A', 'B')); $data[] = array($v, $rw_defaultValue);
        $v = new ResponseVariable('VAR', Cardinality::MULTIPLE, BaseType::PAIR); $v->setDefaultValue(new MultipleContainer(BaseType::PAIR, array(null))); $data[] = array($v, $rw_defaultValue);
        $v = new ResponseVariable('VAR', Cardinality::MULTIPLE, BaseType::PAIR); $v->setCorrectResponse(new MultipleContainer(BaseType::PAIR, array(null))); $data[] = array($v, $rw_correctResponse);
        
        // -- DirectedPairs
        $data[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::DIRECTED_PAIR, new QtiDirectedPair('A', 'B')));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::DIRECTED_PAIR));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DIRECTED_PAIR));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR, new MultipleContainer(BaseType::DIRECTED_PAIR, array(new QtiDirectedPair('A', 'B')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DIRECTED_PAIR, new OrderedContainer(BaseType::DIRECTED_PAIR, array(new QtiDirectedPair('A', 'B')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR, new MultipleContainer(BaseType::DIRECTED_PAIR, array(new QtiDirectedPair('A', 'B'), new QtiDirectedPair('C', 'D'), new QtiDirectedPair('E', 'F'), new QtiDirectedPair('G', 'H'), new QtiDirectedPair('I', 'J')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DIRECTED_PAIR, new OrderedContainer(BaseType::DIRECTED_PAIR, array(new QtiDirectedPair('A', 'B'), new QtiDirectedPair('C', 'D'), new QtiDirectedPair('E', 'F'), new QtiDirectedPair('G', 'H'), new QtiDirectedPair('I', 'J')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR, new MultipleContainer(BaseType::DIRECTED_PAIR, array(null))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DIRECTED_PAIR, new OrderedContainer(BaseType::DIRECTED_PAIR, array(null))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR, new MultipleContainer(BaseType::DIRECTED_PAIR, array(new QtiDirectedPair('A', 'B'), null, new QtiDirectedPair('C', 'D'), null, new QtiDirectedPair('E', 'F')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::DIRECTED_PAIR, new OrderedContainer(BaseType::DIRECTED_PAIR, array(new QtiDirectedPair('A', 'B'), null, new QtiDirectedPair('D', 'E'), null, new QtiDirectedPair('F', 'G')))));
        
        $v = new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::DIRECTED_PAIR); $v->setDefaultValue(new QtiDirectedPair('A', 'B')); array($v, $rw_defaultValue);
        $v = new ResponseVariable('VAR', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR); $v->setDefaultValue(new MultipleContainer(BaseType::DIRECTED_PAIR, array(new QtiDirectedPair('A', 'B'), new QtiDirectedPair('C', 'D'), new QtiDirectedPair('E', 'F'), new QtiDirectedPair('G', 'H'), new QtiDirectedPair('I', 'J')))); $data[] = array($v, $rw_defaultValue);
        $v = new ResponseVariable('VAR', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR); $v->setCorrectResponse(new MultipleContainer(BaseType::DIRECTED_PAIR, array(new QtiDirectedPair('A', 'B'), new QtiDirectedPair('C', 'D'), new QtiDirectedPair('E', 'F'), new QtiDirectedPair('G', 'H'), new QtiDirectedPair('I', 'J')))); $data[] = array($v, $rw_correctResponse);
        
        // -- Points
        $data[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(50, 50)));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::POINT));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::POINT));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::POINT));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::POINT, new MultipleContainer(BaseType::POINT, array(new QtiPoint(50, 50)))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::POINT, new OrderedContainer(BaseType::POINT, array(new QtiPoint(50, 50)))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::POINT, new MultipleContainer(BaseType::POINT, array(new QtiPoint(50, 50), new QtiPoint(0, 0), new QtiPoint(100, 50), new QtiPoint(150, 3), new QtiPoint(50, 50)))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::POINT, new OrderedContainer(BaseType::POINT, array(new QtiPoint(50, 50), new QtiPoint(0, 35), new QtiPoint(30, 50), new QtiPoint(40, 55), new QtiPoint(0, 0)))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::POINT, new MultipleContainer(BaseType::POINT, array(null))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::POINT, new OrderedContainer(BaseType::POINT, array(null))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::POINT, new MultipleContainer(BaseType::POINT, array(new QtiPoint(30, 50), null, new QtiPoint(20, 50), null, new QtiPoint(45, 32)))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::POINT, new OrderedContainer(BaseType::POINT, array(new QtiPoint(20, 11), null, new QtiPoint(36, 43), null, new QtiPoint(50, 44)))));
        
        $v = new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::POINT); $v->setDefaultValue(new QtiPoint(50, 50)); $data[] = array($v, $rw_defaultValue);
        $v = new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::POINT); $v->setDefaultValue(new OrderedContainer(BaseType::POINT, array(new QtiPoint(50, 50)))); $data[] = array($v, $rw_defaultValue);
        $v = new ResponseVariable('VAR', Cardinality::ORDERED, BaseType::POINT); $v->setCorrectResponse(new OrderedContainer(BaseType::POINT, array(new QtiPoint(50, 50)))); $data[] = array($v, $rw_correctResponse);
        
        // IntOrIdentifiers
        $data[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::INT_OR_IDENTIFIER, new QtiIntOrIdentifier(26)));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::INT_OR_IDENTIFIER, new QtiIntOrIdentifier('Q01')));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::INT_OR_IDENTIFIER));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INT_OR_IDENTIFIER));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER, new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array(new QtiIntOrIdentifier(-2147483647)))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INT_OR_IDENTIFIER, new OrderedContainer(BaseType::INT_OR_IDENTIFIER, array(new QtiIntOrIdentifier('Section1')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER, new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array(new QtiIntOrIdentifier(0), new QtiIntOrIdentifier('Q01'), new QtiIntOrIdentifier('Q02'), new QtiIntOrIdentifier(-200000), new QtiIntOrIdentifier(200000)))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INT_OR_IDENTIFIER, new OrderedContainer(BaseType::INT_OR_IDENTIFIER, array(new QtiIntOrIdentifier(0), new QtiIntOrIdentifier(-1), new QtiIntOrIdentifier(1), new QtiIntOrIdentifier(-200000), new QtiIntOrIdentifier('Q05')))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER, new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array(null))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INT_OR_IDENTIFIER, new OrderedContainer(BaseType::INT_OR_IDENTIFIER, array(null))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::INT_OR_IDENTIFIER, new MultipleContainer(BaseType::INT_OR_IDENTIFIER, array(new QtiIntOrIdentifier(0), null, new QtiIntOrIdentifier(1), null, new QtiIntOrIdentifier(200000)))));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INT_OR_IDENTIFIER, new OrderedContainer(BaseType::INT_OR_IDENTIFIER, array(new QtiIntOrIdentifier(0), null, new QtiIntOrIdentifier('Q01'), null, new QtiIntOrIdentifier(200000)))));
        
        $v = new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::INT_OR_IDENTIFIER); $v->setDefaultValue(new QtiIntOrIdentifier(26)); $data[] = array($v, $rw_defaultValue);
        $v = new OutcomeVariable('VAR', Cardinality::SINGLE, BaseType::INT_OR_IDENTIFIER); $v->setDefaultValue(new QtiIntOrIdentifier('Q01')); $data[] = array($v, $rw_defaultValue);
        $v = new OutcomeVariable('VAR', Cardinality::ORDERED, BaseType::INT_OR_IDENTIFIER); $v->setDefaultValue(new OrderedContainer(BaseType::INT_OR_IDENTIFIER, array(new QtiIntOrIdentifier(0), new QtiIntOrIdentifier(-1), new QtiIntOrIdentifier(1), new QtiIntOrIdentifier(-200000), new QtiIntOrIdentifier('Q05')))); $data[] = array($v, $rw_defaultValue);
        
        // Files
        $data[] = array(new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::FILE, FileSystemFile::retrieveFile(self::samplesDir() . 'datatypes/file/text-plain_text_data.txt')));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::MULTIPLE, BaseType::FILE, new MultipleContainer(BaseType::FILE, array(FileSystemFile::retrieveFile(self::samplesDir() . 'datatypes/file/text-plain_text_data.txt'), FileSystemFile::retrieveFile(self::samplesDir() . 'datatypes/file/text-plain_noname.txt')))));
        
        $v = new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::FILE); $v->setDefaultValue(FileSystemFile::retrieveFile(self::samplesDir() . 'datatypes/file/text-plain_text_data.txt')); $data[] = array($v, $rw_defaultValue);
        $v = new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::FILE); $v->setDefaultValue(FileSystemFile::retrieveFile(self::samplesDir() . 'datatypes/file/text-plain_text_data.txt')); $data[] = array($v, $rw_correctResponse);
        
        // Records
        $data[] = array(new OutcomeVariable('VAR', Cardinality::RECORD));
        $data[] = array(new OutcomeVariable('VAR', Cardinality::RECORD, -1, new RecordContainer(array('key1' => null))));
        $data[] = array(new OutcomeVariable('Var', Cardinality::RECORD, -1, new RecordContainer(array('key1' => new QtiDuration('PT1S'), 'key2' => new QtiFloat(25.5), 'key3' => new QtiInteger(2), 'key4' => new QtiString('String!'), 'key5' => null, 'key6' => new QtiBoolean(true)))));
        
        $v = new OutcomeVariable('VAR', Cardinality::RECORD, -1); $v->setDefaultValue(new RecordContainer(array('key1' => null))); array($v, $rw_defaultValue);
        $v = new ResponseVariable('Var', Cardinality::RECORD, -1); $v->setDefaultValue(new RecordContainer(array('key1' => new QtiDuration('PT1S'), 'key2' => new QtiFloat(25.5), 'key3' => new QtiInteger(2), 'key4' => new QtiString('String!'), 'key5' => null, 'key6' => new QtiBoolean(true)))); $data[] = array($v, $rw_defaultValue);
        $v = new ResponseVariable('Var', Cardinality::RECORD, -1); $v->setCorrectResponse(new RecordContainer(array('key1' => new QtiDuration('PT1S'), 'key2' => new QtiFloat(25.5), 'key3' => new QtiInteger(2), 'key4' => new QtiString('String!'), 'key5' => null, 'key6' => new QtiBoolean(true)))); $data[] = array($v, $rw_defaultValue);
        
        return $data;
    }
    
    public function testWriteVariableValueClosedStream() {
        $stream = new MemoryStream();
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
        
        $var = new ResponseVariable('VAR', Cardinality::SINGLE, BaseType::INTEGER);
        $type = QtiBinaryStreamAccess::RW_VALUE;
        
        $this->setExpectedException(
            'qtism\\runtime\\storage\\binary\\QtiBinaryStreamAccessException',
            'An error occured while writing a Variable value.',
            QtiBinaryStreamAccessException::VARIABLE
        );
        $stream->close();
        $access->writeVariableValue($var, $type);
    }
    
    public function testReadAssessmentItemSession1() {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset.xml');
        
        $position = pack('S', 0); // Q01
        $state = "\x01"; // INTERACTING
        $navigationMode = "\x00"; // LINEAR
        $submissionMode = "\x00"; // INDIVIDUAL
        $attempting = "\x00"; // false
        $hasItemSessionControl = "\x00"; // false
        $numAttempts = "\x02"; // 2
        $duration = pack('S', 4) . 'PT0S'; // 0 seconds recorded yet.
        $completionStatus = pack('S', 10) . 'incomplete';
        $hasTimeReference = "\x01"; // true
        $timeReference = pack('l', 1378302030); //  Wednesday, September 4th 2013, 13:40:30 (GMT)
        $varCount = "\x02"; // 2 variables (scoring & RESPONSE).
        
        $score = pack('S', 0) . pack('S', 8) . "\x00" . "\x00" . "\x00" . "\x01" . pack('d', 1.0); // 9th (8 + 1) outcomeDeclaration.
        $response = pack('S', 1) . pack('S', 0) . "\x00" . "\x00" . "\x00" . "\x01" . pack('S', 7) . 'ChoiceA'; // 1st (0 + 1) responseDeclaration.
        
        $shufflingCount = "\x00"; // No shuffling states.
        
        $bin = implode('', array($position, $state, $navigationMode, $submissionMode, $attempting, $hasItemSessionControl, $numAttempts, $duration, $completionStatus, $hasTimeReference, $timeReference, $varCount, $score, $response, $shufflingCount));
        $stream = new MemoryStream($bin);
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
        $seeker = new AssessmentTestSeeker($doc->getDocumentComponent(), array('assessmentItemRef', 'outcomeDeclaration', 'responseDeclaration', 'itemSessionControl'));
        
        $session = $access->readAssessmentItemSession(new SessionManager(new FileSystemFileManager()), $seeker);
        
        $this->assertEquals('Q01', $session->getAssessmentItem()->getIdentifier());
        $this->assertEquals(AssessmentItemSessionState::INTERACTING, $session->getState());
        $this->assertEquals(NavigationMode::LINEAR, $session->getNavigationMode());
        $this->assertEquals(SubmissionMode::INDIVIDUAL, $session->getSubmissionMode());
        $this->assertFalse($session->isAttempting());
        $this->assertEquals(2, $session['numAttempts']->getValue());
        $this->assertEquals('PT0S', $session['duration']->__toString());
        $this->assertEquals('incomplete', $session['completionStatus']->getValue());
        $this->assertInstanceOf('qtism\\runtime\\common\\OutcomeVariable', $session->getVariable('scoring'));
        $this->assertInstanceOf('qtism\\common\\datatypes\\QtiFloat', $session['scoring']);
        $this->assertEquals(1.0, $session['scoring']->getValue());
        $this->assertInstanceOf('qtism\\runtime\\common\\ResponseVariable', $session->getVariable('RESPONSE'));
        $this->assertEquals(BaseType::IDENTIFIER, $session->getVariable('RESPONSE')->getBaseType());
        $this->assertInstanceOf('qtism\\common\\datatypes\\QtiString', $session['RESPONSE']);
        $this->assertEquals('ChoiceA', $session['RESPONSE']->getValue());
    }
    
    /**
     * @depends testReadAssessmentItemSession1
     */
    public function testReadAssessmentItemSession2() {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/templatevariables_in_items.xml');
        
        $position = pack('S', 0); // Q01
        $state = "\x04"; // CLOSED
        $navigationMode = "\x01"; // NONLINEAR
        $submissionMode = "\x01"; // SIMULTANEOUS
        $attempting = "\x00"; // false
        $hasItemSessionControl = "\x00"; // false
        $numAttempts = "\x01"; // 1
        $duration = pack('S', 5) . 'PT20S'; // 20 seconds recorded.
        $completionStatus = pack('S', 8) . 'complete';
        $hasTimeReference = "\x01"; // true
        $timeReference = pack('l', 1378302030); //  Wednesday, September 4th 2013, 13:40:30 (GMT)
        $varCount = "\x03"; // 3 variables (SCORE & RESPONSE & TPL)
        
        $score = pack('S', 0) . pack('S', 0) . "\x00" . "\x00" . "\x00" . "\x01" . pack('d', 1.0); // 1st (0 + 1) outcomeDeclaration.
        $response = pack('S', 1) . pack('S', 0) . "\x00" . "\x00" .  "\x00" . "\x01" . pack('S', 7) . 'ChoiceA'; // 1st (0 + 1) responseDeclaration.
        $template = pack('S', 2) . pack('S', 0) . "\x00" . "\x00" . "\x00" . "\x01" . pack('l', 10); // 1st (0 + 1) templateDeclaration.
        
        $shufflingCount = "\x00"; // No shuffling states.
        
        $binArray = array(
            $position,
            $state,
            $navigationMode, 
            $submissionMode,
            $attempting,
            $hasItemSessionControl,
            $numAttempts,
            $duration, 
            $completionStatus,
            $hasTimeReference,
            $timeReference,
            $varCount, 
            $score,
            $response,
            $template,
            $shufflingCount
        );
        
        $bin = implode('', $binArray);
        $stream = new MemoryStream($bin);
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
        $seeker = new AssessmentTestSeeker($doc->getDocumentComponent(), array('assessmentItemRef', 'outcomeDeclaration', 'responseDeclaration', 'templateDeclaration', 'itemSessionControl'));
        
        $session = $access->readAssessmentItemSession(new SessionManager(new FileSystemFileManager()), $seeker);
        
        $this->assertEquals('Q01', $session->getAssessmentItem()->getIdentifier());
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $session->getState());
        $this->assertEquals(NavigationMode::NONLINEAR, $session->getNavigationMode());
        $this->assertEquals(SubmissionMode::SIMULTANEOUS, $session->getSubmissionMode());
        $this->assertFalse($session->isAttempting(false));
        $this->assertEquals(1, $session['numAttempts']->getValue());
        $this->assertEquals('PT20S', $session['duration']->__toString());
        $this->assertEquals('complete', $session['completionStatus']->getValue());
        $this->assertInstanceOf('qtism\\runtime\\common\\OutcomeVariable', $session->getVariable('SCORE'));
        $this->assertInstanceOf('qtism\\common\\datatypes\\QtiFloat', $session['SCORE']);
        $this->assertEquals(1.0, $session['SCORE']->getValue());
        $this->assertInstanceOf('qtism\\runtime\\common\\ResponseVariable', $session->getVariable('RESPONSE'));
        $this->assertSame(BaseType::IDENTIFIER, $session->getVariable('RESPONSE')->getBaseType());
        $this->assertInstanceOf('qtism\\common\\datatypes\\QtiString', $session['RESPONSE']);
        $this->assertEquals('ChoiceA', $session['RESPONSE']->getValue());
        $this->assertInstanceOf('qtism\\runtime\\common\\TemplateVariable', $session->getVariable('TPL'));
        $this->assertInstanceOf('qtism\\common\\datatypes\\QtiInteger', $session['TPL']);
        $this->assertSame(10, $session['TPL']->getValue());
    }
    
    public function testWriteAssessmentItemSession1() {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset.xml');
        
        $seeker = new AssessmentTestSeeker($doc->getDocumentComponent(), array('assessmentItemRef', 'outcomeDeclaration', 'responseDeclaration', 'itemSessionControl'));
        $stream = new MemoryStream();
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
        
        $session = new AssessmentItemSession($doc->getDocumentComponent()->getComponentByIdentifier('Q02'));
        $session->beginItemSession();
        
        $access->writeAssessmentItemSession($seeker, $session);
        
        $stream->rewind();
        $session = $access->readAssessmentItemSession(new SessionManager(new FileSystemFileManager()), $seeker);
        $this->assertEquals(AssessmentItemSessionState::INITIAL, $session->getState());
        $this->assertEquals(NavigationMode::LINEAR, $session->getNavigationMode());
        $this->assertEquals(SubmissionMode::INDIVIDUAL, $session->getSubmissionMode());
        $this->assertEquals('PT0S', $session['duration']->__toString());
        $this->assertEquals(0, $session['numAttempts']->getValue());
        $this->assertEquals('not_attempted', $session['completionStatus']->getValue());
        $this->assertFalse($session->isAttempting());
        $this->assertEquals(0.0, $session['SCORE']->getValue());
        $this->assertTrue($session['RESPONSE']->equals(new MultipleContainer(BaseType::PAIR)));
        $this->assertSame(null, $session->getTimeReference());
        $this->assertFalse($session->hasTimeReference());
    }
    
    public function testWriteAssessmentItemSession2() {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/templatevariables_in_items.xml');
    
        $seeker = new AssessmentTestSeeker($doc->getDocumentComponent(), array('assessmentItemRef', 'outcomeDeclaration', 'responseDeclaration', 'templateDeclaration', 'itemSessionControl'));
        $stream = new MemoryStream();
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
    
        $session = new AssessmentItemSession($doc->getDocumentComponent()->getComponentByIdentifier('Q01'));
        $session->beginItemSession();

        $access->writeAssessmentItemSession($seeker, $session);
    
        $stream->rewind();
        $session = $access->readAssessmentItemSession(new SessionManager(new FileSystemFileManager()), $seeker);
        $this->assertEquals(AssessmentItemSessionState::INITIAL, $session->getState());
        $this->assertEquals(NavigationMode::LINEAR, $session->getNavigationMode());
        $this->assertEquals(SubmissionMode::INDIVIDUAL, $session->getSubmissionMode());
        $this->assertEquals('PT0S', $session['duration']->__toString());
        $this->assertEquals(0, $session['numAttempts']->getValue());
        $this->assertEquals('not_attempted', $session['completionStatus']->getValue());
        $this->assertFalse($session->isAttempting());
        $this->assertEquals(0.0, $session['SCORE']->getValue());
        $this->assertSame(null, $session['RESPONSE']);
        $this->assertEquals(10, $session['TPL']->getValue());
        $this->assertSame(null, $session->getTimeReference());
        $this->assertFalse($session->hasTimeReference());
    }
    
    public function testWriteAssessmentItemSession() {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset.xml');
    
        $seeker = new AssessmentTestSeeker($doc->getDocumentComponent(), array('assessmentItemRef', 'outcomeDeclaration', 'responseDeclaration', 'itemSessionControl'));
        $stream = new MemoryStream();
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
    
        $session = new AssessmentItemSession($doc->getDocumentComponent()->getComponentByIdentifier('Q02'));
        $session->beginItemSession();
    
        $access->writeAssessmentItemSession($seeker, $session);
    
        $stream->rewind();
        $session = $access->readAssessmentItemSession(new SessionManager(new FileSystemFileManager()), $seeker);
        $this->assertEquals(AssessmentItemSessionState::INITIAL, $session->getState());
        $this->assertEquals(NavigationMode::LINEAR, $session->getNavigationMode());
        $this->assertEquals(SubmissionMode::INDIVIDUAL, $session->getSubmissionMode());
        $this->assertEquals('PT0S', $session['duration']->__toString());
        $this->assertEquals(0, $session['numAttempts']->getValue());
        $this->assertEquals('not_attempted', $session['completionStatus']->getValue());
        $this->assertFalse($session->isAttempting());
        $this->assertEquals(0.0, $session['SCORE']->getValue());
        $this->assertTrue($session['RESPONSE']->equals(new MultipleContainer(BaseType::PAIR)));
        $this->assertSame(null, $session->getTimeReference());
        $this->assertFalse($session->hasTimeReference());
    }
    
    public function testReadRouteItem() {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset.xml');
        
        $seeker = new AssessmentTestSeeker($doc->getDocumentComponent(), array('assessmentItemRef', 'assessmentSection', 'testPart', 'outcomeDeclaration', 'responseDeclaration', 'branchRule', 'preCondition'));
        $bin = '';
        $bin .= "\x00"; // occurence = 0
        $bin .= pack('S', 2); // item-tree-position = Q03
        $bin .= pack('S', 0); // part-tree-position = P01
        $bin .= "\x01"; // sections-count = 1
        $bin .= pack('S', 0); // section-tree-position = S01
        $bin .= "\x00"; // branchrules-count = 0
        $bin .= "\x00"; // preconditions-count = 0
        
        $stream = new MemoryStream($bin);
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
        
        $routeItem = $access->readRouteItem($seeker);
        $this->assertEquals('Q03', $routeItem->getAssessmentItemRef()->getIdentifier());
        $this->assertEquals('S01', $routeItem->getAssessmentSection()->getIdentifier());
        $this->assertEquals('P01', $routeItem->getTestPart()->getIdentifier());
        $this->assertInternalType('integer', $routeItem->getOccurence());
        $this->assertEquals(0, $routeItem->getOccurence());
        $this->assertEquals(0, count($routeItem->getBranchRules()));
        $this->assertEquals(0, count($routeItem->getPreConditions()));
    }
    
    public function testWriteRouteItem() {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset.xml');
        
        $seeker = new AssessmentTestSeeker($doc->getDocumentComponent(), array('assessmentItemRef', 'assessmentSection', 'testPart', 'outcomeDeclaration', 'responseDeclaration', 'branchRule', 'preCondition'));
        $stream = new MemoryStream();
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
        
        // Get route item at index 2 which is the route item describing
        // item occurence 0 of Q03.
        $sessionManager = new SessionManager(new FileSystemFileManager());
        $testSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $routeItem = $testSession->getRoute()->getRouteItemAt(2);
        
        $access->writeRouteItem($seeker, $routeItem);
        $stream->rewind();
        
        $routeItem = $access->readRouteItem($seeker);
        $this->assertEquals('Q03', $routeItem->getAssessmentItemRef()->getIdentifier());
        $this->assertEquals('S01', $routeItem->getAssessmentSection()->getIdentifier());
        $this->assertEquals('P01', $routeItem->getTestPart()->getIdentifier());
        $this->assertInternalType('integer', $routeItem->getOccurence());
        $this->assertEquals(0, $routeItem->getOccurence());
        $this->assertEquals(0, count($routeItem->getBranchRules()));
        $this->assertEquals(0, count($routeItem->getPreConditions()));
    }
    
    public function testReadPendingResponses() {
    	$doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset_simultaneous.xml');
        
        $seeker = new AssessmentTestSeeker($doc->getDocumentComponent(), array('assessmentItemRef', 'assessmentSection', 'testPart', 'outcomeDeclaration', 'responseDeclaration', 'branchRule', 'preCondition'));
        $bin = '';
        $bin.= "\x01"; // variable-count = 1.
        $bin.= pack('S', 0); // response-declaration-position = 0
        $bin.= pack('S', 0) . "\x00" . "\x01" . pack('S', 7) . 'ChoiceA'; // variable-value = 'ChoiceA' (identifier)
        $bin.= pack('S', 0); // item-tree-position = 0
        $bin.= "\x00"; // occurence = 0
        
        $stream = new MemoryStream($bin);
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
        
        $pendingResponses = $access->readPendingResponses($seeker);
        $state = $pendingResponses->getState();
        $this->assertEquals(1, count($state));
        $this->assertInstanceOf('qtism\\runtime\\common\\ResponseVariable', $state->getVariable('RESPONSE'));
        $this->assertEquals('ChoiceA', $state['RESPONSE']->getValue());
        
        $itemRef = $pendingResponses->getAssessmentItemRef();
        $this->assertEquals('Q01', $itemRef->getIdentifier());
        
        $this->assertEquals(0, $pendingResponses->getOccurence());
        $this->assertInternalType('integer', $pendingResponses->getOccurence());
    }
    
    public function testWritePendingResponses() {
    	$doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset_simultaneous.xml');
        
        $seeker = new AssessmentTestSeeker($doc->getDocumentComponent(), array('assessmentItemRef', 'assessmentSection', 'testPart', 'outcomeDeclaration', 'responseDeclaration', 'branchRule', 'preCondition'));
        $stream = new MemoryStream();
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
        
        $factory = new SessionManager(new FileSystemFileManager());
        $session = $factory->createAssessmentTestSession($doc->getDocumentComponent());
        $session->beginTestSession();
        
        $session->beginAttempt();
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB')))));
        
        $store = $session->getPendingResponseStore();
        $pendingResponses = $store->getPendingResponses($doc->getDocumentComponent()->getComponentByIdentifier('Q01'));
        $access->writePendingResponses($seeker, $pendingResponses);
        
        $stream->rewind();
        $pendingResponses = $access->readPendingResponses($seeker);
        
        $state = $pendingResponses->getState();
        $this->assertEquals('ChoiceB', $state['RESPONSE']->getValue());
        $this->assertEquals('Q01', $pendingResponses->getAssessmentItemRef()->getIdentifier());
        $this->assertEquals(0, $pendingResponses->getOccurence());
        $this->assertInternalType('integer', $pendingResponses->getOccurence());
    }
    
    public function testReadShufflingGroup() {
        $bin = '';
        $bin .= "\x03"; // identifier-count = 3
        $bin .= pack('S', 3) . 'id1';
        $bin .= pack('S', 3) . 'id2';
        $bin .= pack('S', 3) . 'id3';
        $bin .= "\x01"; // fixed-identifier-count = 1
        $bin .= pack('S', 3) . 'id2';
        
        $stream = new MemoryStream($bin);
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
        
        $shufflingGroup = $access->readShufflingGroup();
        $this->assertEquals(array('id1', 'id2', 'id3'), $shufflingGroup->getIdentifiers()->getArrayCopy());
        $this->assertEquals(array('id2'), $shufflingGroup->getFixedIdentifiers()->getArrayCopy());
    }
    
    public function testWriteShufflingGroup() {
        $shufflingGroup = new ShufflingGroup(new IdentifierCollection(array('id1', 'id2', 'id3')));
        $shufflingGroup->setFixedIdentifiers(new IdentifierCollection(array('id2')));
        
        $stream = new MemoryStream();
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
        
        $access->writeShufflingGroup($shufflingGroup);
        $stream->rewind();
        
        $shufflingGroup = $access->readShufflingGroup();
        $this->assertEquals(array('id1', 'id2', 'id3'), $shufflingGroup->getIdentifiers()->getArrayCopy());
        $this->assertEquals(array('id2'), $shufflingGroup->getFixedIdentifiers()->getArrayCopy());
    }
    
    public function testReadShufflingState() {
        $bin = '';
        
        // Binary data related to Shuffling State.
        $bin .= pack('S', 8) . 'RESPONSE'; // response-identifier
        $bin .= "\x01"; // shuffling-group-count

        // Binary data related to Shuffling Group.
        $bin .= "\x03"; // identifier-count = 3
        $bin .= pack('S', 3) . 'id1';
        $bin .= pack('S', 3) . 'id2';
        $bin .= pack('S', 3) . 'id3';
        $bin .= "\x01"; // fixed-identifier-count = 1
        $bin .= pack('S', 3) . 'id2';
        
        $stream = new MemoryStream($bin);
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
        
        $shufflingState = $access->readShufflingState();
        $this->assertEquals('RESPONSE', $shufflingState->getResponseIdentifier());
        
        $shufflingGroups = $shufflingState->getShufflingGroups();
        $this->assertEquals(array('id1', 'id2', 'id3'), $shufflingGroups[0]->getIdentifiers()->getArrayCopy());
        $this->assertEquals(array('id2'), $shufflingGroups[0]->getFixedIdentifiers()->getArrayCopy());
    }
    
    public function testWriteShufflingState() {
        $shufflingGroup = new ShufflingGroup(new IdentifierCollection(array('id1', 'id2', 'id3')));
        $shufflingGroup->setFixedIdentifiers(new IdentifierCollection(array('id2')));
        $shufflingGroups = new ShufflingGroupCollection(array($shufflingGroup));
        
        $shuffling = new Shuffling('RESPONSE', $shufflingGroups);
        $stream = new MemoryStream();
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
        $access->writeShufflingState($shuffling);
        
        $stream->rewind();
        $shufflingState = $access->readShufflingState();
        
        $this->assertEquals('RESPONSE', $shufflingState->getResponseIdentifier());
        
        $shufflingGroups = $shufflingState->getShufflingGroups();
        $this->assertEquals(array('id1', 'id2', 'id3'), $shufflingGroups[0]->getIdentifiers()->getArrayCopy());
        $this->assertEquals(array('id2'), $shufflingGroups[0]->getFixedIdentifiers()->getArrayCopy());
    }
    
    public function testReadRecordFieldEmptyStream() {
        $stream = new MemoryStream('');
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
        
        $this->setExpectedException(
            'qtism\\runtime\\storage\\binary\\QtiBinaryStreamAccessException',
            'An error occured while reading a Record Field.',
            QtiBinaryStreamAccessException::RECORDFIELD
        );
        
        $access->readRecordField();
    }
    
    public function testWriteRecordFieldClosedStream() {
        $stream = new MemoryStream('');
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
        
        $this->setExpectedException(
            'qtism\\runtime\\storage\\binary\\QtiBinaryStreamAccessException',
            'An error occured while writing a Record Field.',
            QtiBinaryStreamAccessException::RECORDFIELD
        );
        
        $stream->close();
        $access->writeRecordField(array('key', new QtiString('string')));
    }
    
    public function testReadIdentifierEmptyStream() {
        $stream = new MemoryStream('');
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
        
        $this->setExpectedException(
            'qtism\\runtime\\storage\\binary\\QtiBinaryStreamAccessException',
            'An error occured while reading an identifier.',
            QtiBinaryStreamAccessException::IDENTIFIER
        );
        
        $access->readIdentifier();
    }
    
    public function testWriteIdentifierClosedStream() {
        $stream = new MemoryStream('');
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
        
        $this->setExpectedException(
            'qtism\\runtime\\storage\\binary\\QtiBinaryStreamAccessException',
            'An error occured while writing an identifier.',
            QtiBinaryStreamAccessException::IDENTIFIER
        );
        
        $stream->close();
        $access->writeIdentifier('identifier');
    }
    
    public function testReadPointEmptyStream() {
        $stream = new MemoryStream('');
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
        
        $this->setExpectedException(
            'qtism\\runtime\\storage\\binary\\QtiBinaryStreamAccessException',
            'An error occured while reading a point.',
            QtiBinaryStreamAccessException::POINT
        );
        
        $access->readPoint();
    }
    
    public function testWritePointClosedStream() {
        $stream = new MemoryStream('');
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
        
        $this->setExpectedException(
            'qtism\\runtime\\storage\\binary\\QtiBinaryStreamAccessException',
            'An error occured while writing a point.',
            QtiBinaryStreamAccessException::POINT
        );
        
        $stream->close();
        $access->writePoint(new QtiPoint(0, 0));
    }
    
    public function testReadPairEmptyStream() {
        $stream = new MemoryStream('');
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
        
        $this->setExpectedException(
            'qtism\\runtime\\storage\\binary\\QtiBinaryStreamAccessException',
            'An error occured while reading a pair.',
            QtiBinaryStreamAccessException::PAIR
        );
        
        $access->readPair();
    }
}
