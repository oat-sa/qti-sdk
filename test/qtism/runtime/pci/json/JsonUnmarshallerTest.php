<?php

use qtism\common\datatypes\files\FileSystemFileManager;
use qtism\common\datatypes\files\FileSystemFile;
use qtism\runtime\common\RecordContainer;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiDatatype;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiIntOrIdentifier;
use qtism\common\datatypes\QtiUri;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiBoolean;
use qtism\runtime\pci\json\Unmarshaller;
use qtism\common\datatypes\QtiScalar;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

class JsonUnmarshallerTest extends QtiSmTestCase {
	
    static protected function createUnmarshaller() {
        return new Unmarshaller(new FileSystemFileManager());
    }
    
    /**
     * @dataProvider unmarshallScalarProvider
     * 
     * @param Scalar $expectedScalar
     * @param string $json
     */
    public function testUnmarshallScalar(QtiScalar $expectedScalar = null, $json) {
        $unmarshaller = self::createUnmarshaller();
        if (is_null($expectedScalar) === false) {
            $this->assertTrue($unmarshaller->unmarshall($json)->equals($expectedScalar));
        }
        else {
            $this->assertSame($expectedScalar, $unmarshaller->unmarshall($json));
        }
    }
    
    /**
     * @dataProvider unmarshallComplexProvider
     * 
     * @param QtiDatatype $expectedComplex
     * @param string $json
     */
    public function testUnmarshallComplex(QtiDatatype $expectedComplex, $json) {
        $unmarshaller = self::createUnmarshaller();
        $value = $unmarshaller->unmarshall($json);
        $this->assertTrue($expectedComplex->equals($value));
    }
    
    /**
     * @dataProvider unmarshallFileProvider
     * 
     * @param File $expectedFile
     * @param string $json
     */
    public function testUnmarshallFile(FileSystemFile $expectedFile, $json) {
        $unmarshaller = self::createUnmarshaller();
        $value = $unmarshaller->unmarshall($json);
        $this->assertTrue($expectedFile->equals($value));
        
        // cleanup.
        $fileManager = new FileSystemFileManager();
        $fileManager->delete($value);
    }
    
    /**
     * @dataProvider unmarshallListProvider
     * 
     * @param MultipleContainer $expectedContainer
     * @param string $json
     */
    public function testUnmarshallList(MultipleContainer $expectedContainer, $json) {
        $unmarshaller = self::createUnmarshaller();
        $this->assertTrue($expectedContainer->equals($unmarshaller->unmarshall($json)));
    }
    
    /**
     * @dataProvider unmarshallRecordProvider
     * 
     * @param RecordContainer $expectedRecord
     * @param string $json
     */
    public function testUnmarshallRecord(RecordContainer $expectedRecord, $json) {
        $unmarshaller = self::createUnmarshaller();
        $this->assertTrue($expectedRecord->equals($unmarshaller->unmarshall($json)));
    }
    
    /**
     * @dataProvider unmarshallInvalidProvider
     * 
     * @param mixed $input
     */
    public function testUnmarshallInvalid($input) {
        $unmarshaller = self::createUnmarshaller();
        $this->setExpectedException('qtism\\runtime\\pci\\json\\UnmarshallingException');
        $unmarshaller->unmarshall($input);
    }
    
    public function testUnmarshallState() {
        $json = '
            {
                "RESPONSE1": { "base" : { "identifier" : "ChoiceA" } },
                "RESPONSE2": { "list" : { "identifier" : ["_id1", "id2", "ID3"] } },
                "RESPONSE3": { "record" : [ { "name" : "rock", "base": { "identifier" : "Paper" } } ] },
                "RESPONSE4": { "base" : null }
            }
        ';
        
        $unmarshaller = self::createUnmarshaller();;
        $state = $unmarshaller->unmarshall($json);
        $this->assertEquals(4, count($state));
        $this->assertEquals(array('RESPONSE1', 'RESPONSE2', 'RESPONSE3', 'RESPONSE4'), array_keys($state));
        
        $response1 = new QtiIdentifier('ChoiceA');
        $response2 = new MultipleContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('_id1'), new QtiIdentifier('id2'), new QtiIdentifier('ID3')));
        $response3 = new RecordContainer(array('rock' => new QtiIdentifier('Paper')));
        $response4 = null;

        $this->assertTrue($response1->equals($state['RESPONSE1']));
        $this->assertTrue($response2->equals($state['RESPONSE2']));
        $this->assertTrue($response3->equals($state['RESPONSE3']));
        $this->assertSame($response4, $state['RESPONSE4']);
    }
    
    public function unmarshallScalarProvider() {
        return array(
            array(new QtiBoolean(true), '{ "base" : {"boolean" : true } }'),
            array(new QtiBoolean(false), '{ "base" : {"boolean" : false } }'),
            array(new QtiInteger(123), '{ "base" : {"integer" : 123 } }'),
            array(new QtiFloat(23.23), '{ "base" : {"float" : 23.23 } }'),
            array(new QtiFloat(6.0), '{ "base" : {"float" : 6 } }'),
            array(new QtiString('string'), '{ "base" : {"string" : "string" } }'),
            array(new QtiUri('http://www.taotesting.com'), '{ "base" : {"uri" : "http://www.taotesting.com" } }'),
            array(new QtiIntOrIdentifier(10), '{ "base" : {"intOrIdentifier" : 10 } }'),
            array(new QtiIntOrIdentifier('_id1'), '{ "base" : {"identifier" : "_id1" } }'),
            array(new QtiIdentifier('_id1'), '{ "base" : {"identifier" : "_id1" } }'),
            array(null, '{ "base": null }')
        );
    }
    
    public function unmarshallComplexProvider() {
        $returnValue = array();
        
        $returnValue[] = array(new QtiPoint(10, 20), '{ "base" : { "point" : [10, 20] } }');
        $returnValue[] = array(new QtiPair('A', 'B'), '{ "base" : { "pair" : ["A", "B"] } }');
        $returnValue[] = array(new QtiDirectedPair('a', 'b'), '{ "base" : { "directedPair" : ["a", "b"] } }');
        $returnValue[] = array(new QtiDuration('PT3S'), '{ "base" : { "duration" : "PT3S" } }');

        return $returnValue;
    }
    
    public function unmarshallFileProvider() {
        $returnValue = array();
        $samples = self::samplesDir();
        $fileManager = new FileSystemFileManager();
        
        $file = $fileManager->retrieve($samples . 'datatypes/file/files_2.txt');
        $returnValue[] = array($file, '{ "base" : { "file" : { "mime" : "text\/html", "data" : ' . json_encode(base64_encode('<img src="/qtism/img.png"/>')) . ' } } }');
        
        $file = $fileManager->retrieve($samples . 'datatypes/file/text-plain_text_data.txt');
        $returnValue[] = array($file, '{ "base" : { "file" : { "mime" : "text\/plain", "data" : ' . json_encode(base64_encode('Some text...')) . ', "name" : "text.txt" } } }');
        
        $originalfile = $samples . 'datatypes/file/raw/image.png';
        $filepath = $samples . 'datatypes/file/image-png_noname_data.png';
        $file = $fileManager->retrieve($filepath);
        $returnValue[] = array($file, '{ "base" : { "file" : { "mime" : "image\/png", "data" : ' . json_encode(base64_encode(file_get_contents($originalfile))) . ' } } }');
        
        return $returnValue;
    }
    
    public function unmarshallListProvider() {
        $returnValue = array();
        
        $container = new MultipleContainer(BaseType::BOOLEAN, array(new QtiBoolean(true), new QtiBoolean(false), new QtiBoolean(true), new QtiBoolean(true)));
        $json = '{ "list" : { "boolean" : [true, false, true, true] } }';
        $returnValue[] = array($container, $json);
        
        $container = new MultipleContainer(BaseType::INTEGER, array(new QtiInteger(2), new QtiInteger(3), new QtiInteger(5), new QtiInteger(7), new QtiInteger(11), new QtiInteger(13)));
        $json = '{ "list" : { "integer" : [2, 3, 5, 7, 11, 13] } }';
        $returnValue[] = array($container, $json);
        
        $container = new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(3.1415926), new QtiFloat(12.34), new QtiFloat(98.76)));
        $json = '{ "list" : { "float" : [3.1415926, 12.34, 98.76] } }';
        $returnValue[] = array($container, $json);
        
        $container = new MultipleContainer(BaseType::STRING, array(new QtiString('Another'), new QtiString('And Another')));
        $json = '{ "list" : { "string" : ["Another", "And Another"] } }';
        $returnValue[] = array($container, $json);

        $container = new MultipleContainer(BaseType::POINT, array(new QtiPoint(123, 456), new QtiPoint(640, 480)));
        $json = '{ "list" : { "point" : [[123, 456], [640, 480]] } }';
        $returnValue[] = array($container, $json);
        
        $container = new MultipleContainer(BaseType::PAIR, array(new QtiPair('A', 'B'), new QtiPair('D', 'C')));
        $json = '{ "list" : { "pair" : [["A", "B"], ["D", "C"]] } }';
        $returnValue[] = array($container, $json);
        
        $container = new MultipleContainer(BaseType::DIRECTED_PAIR, array(new QtiDirectedPair('A', 'B'), new QtiDirectedPair('D', 'C')));
        $json = '{ "list" : { "directedPair" : [["A", "B"], ["D", "C"]] } }';
        $returnValue[] = array($container, $json);
        
        $container = new MultipleContainer(BaseType::DURATION, array(new QtiDuration('PT5S'), new QtiDuration('PT10S')));
        $json = '{ "list" : { "duration" : ["PT5S", "PT10S"] } }';
        $returnValue[] = array($container, $json);
        
        $container = new MultipleContainer(BaseType::BOOLEAN, array(new QtiBoolean(true), null, new QtiBoolean(false)));
        $json = '{ "list" : { "boolean": [true, null, false] } }';
        $returnValue[] = array($container, $json);
        
        return $returnValue;
    }
    
    public function unmarshallRecordProvider() {
        $returnValue = array();
        
        $record = new RecordContainer();
        $json = '{ "record" : [] }';
        $returnValue[] = array($record, $json);
        
        $record = new RecordContainer(array('A' => new QtiString('A')));
        $json = '{ "record" : [ { "name" : "A", "base" : { "string" : "A" } } ] }';
        $returnValue[] = array($record, $json);
        
        $record = new RecordContainer(array('A' => new QtiString('A'), 'B' => null));
        $json = '{ "record" : [ { "name" : "A", "base" : { "string" : "A" } }, { "name" : "B", "base" : null } ] }';
        $returnValue[] = array($record, $json);
        
        $record = new RecordContainer(array('A' => null));
        $json = '{ "record" : [ { "name": "A" } ] }';
        $returnValue[] = array($record, $json);
        
        return $returnValue;
    }
    
    public function unmarshallInvalidProvider() {
        return array(
            array(new \stdClass()),
            array(''),
            array('{ "list": [} }'),
            array('{ "base" : { "booleanooo" : true } }'),
            array('{}'),
            array('{ "base" : { "boolean" : "yop" } }'),
            array('[ "base" : { "boolean" : true} ]'),
            array('{ "list" : { "boolean" : null }'),
            array('{ "list" : { } }'),
            array('{ "liste" : { "boolean" : true } } '),
            array('{ "record" : [ { "namez" } ] '),
        );
    }
}
