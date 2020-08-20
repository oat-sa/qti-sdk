<?php

namespace qtismtest\runtime\pci\json;

use qtism\common\datatypes\files\FileSystemFile;
use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiDatatype;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiIntOrIdentifier;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiUri;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\pci\json\Marshaller;
use qtism\runtime\pci\json\MarshallingException;
use qtismtest\QtiSmTestCase;
use stdClass;

class JsonMarshallerTest extends QtiSmTestCase
{
    /**
     * @dataProvider marshallScalarProvider
     *
     * @param QtiDatatype|null $scalar
     * @param string $expectedJson
     */
    public function testMarshallScalar($scalar, $expectedJson)
    {
        $marshaller = new Marshaller();
        $this->assertEquals($expectedJson, $marshaller->marshall($scalar));
    }

    /**
     * @dataProvider marshallComplexProvider
     *
     * @param QtiDatatype $complex
     * @param string $expectedJson
     */
    public function testMarshallComplex(QtiDatatype $complex, $expectedJson)
    {
        $marshaller = new Marshaller();
        $this->assertEquals($expectedJson, $marshaller->marshall($complex));
    }

    /**
     * @dataProvider marshallMultipleProvider
     *
     * @param MultipleContainer $multiple
     * @param string $expectedJson
     */
    public function testMarshallMultiple(MultipleContainer $multiple, $expectedJson)
    {
        $marshaller = new Marshaller();
        $this->assertEquals($expectedJson, $marshaller->marshall($multiple));
    }

    /**
     * @dataProvider marshallOrderedProvider
     *
     * @param OrederedContainer $ordered
     * @param string $expectedJson
     */
    public function testMarshallOrdered(OrderedContainer $ordered, $expectedJson)
    {
        $marshaller = new Marshaller();
        $this->assertEquals($expectedJson, $marshaller->marshall($ordered));
    }

    /**
     * @dataProvider marshallRecordProvider
     *
     * @param RecordContainer $record
     * @param string $expectedJson
     */
    public function testMarshallRecord(RecordContainer $record, $expectedJson)
    {
        $marshaller = new Marshaller();
        $this->assertEquals($expectedJson, $marshaller->marshall($record));
    }

    /**
     * @dataProvider marshallStateProvider
     *
     * @param State $state
     * @param string $expectedJson
     */
    public function testMarshallState(State $state, $expectedJson)
    {
        $marshaller = new Marshaller();
        $this->assertEquals($expectedJson, $marshaller->marshall($state));
    }

    /**
     * @dataProvider marshallInvalidInputProvider
     *
     * @param mixed $input
     */
    public function testMarshallInvalidInput($input)
    {
        $this->expectException(MarshallingException::class);
        $this->expectExceptionMessage("The '" . Marshaller::class. "::marshall' method only takes State, QtiDatatype and null values as arguments");
        $this->expectExceptionCode(MarshallingException::NOT_SUPPORTED);
        $marshaller = new Marshaller();
        $marshaller->marshall($input);
    }

    public function testMarshallAsArray()
    {
        $marshaller = new Marshaller();
        $data = $marshaller->marshall(new QtiInteger(12), Marshaller::MARSHALL_ARRAY);
        $this->assertEquals(12, $data['base']['integer']);
    }

    public function marshallScalarProvider()
    {
        return [
            [new QtiBoolean(true), json_encode(['base' => ['boolean' => true]])],
            [new QtiBoolean(false), json_encode(['base' => ['boolean' => false]])],
            [new QtiInteger(1337), json_encode(['base' => ['integer' => 1337]])],
            [new QtiFloat(1337.1337), json_encode(['base' => ['float' => 1337.1337]])],
            [new QtiString('String!'), json_encode(['base' => ['string' => 'String!']])],
            [new QtiString(''), json_encode(['base' => ['string' => '']])],
            [new QtiIdentifier('RESP_X32'), json_encode(['base' => ['identifier' => 'RESP_X32']])],
            [new QtiIntOrIdentifier('RESP_X33'), json_encode(['base' => ['intOrIdentifier' => 'RESP_X33']])],
            [new QtiIntOrIdentifier(1337), json_encode(['base' => ['intOrIdentifier' => 1337]])],
            [new QtiUri('http://www.taotesting.com'), json_encode(['base' => ['uri' => 'http://www.taotesting.com']])],
            [null, json_encode(['base' => null])],
        ];
    }

    public function marshallComplexProvider()
    {
        $samples = self::samplesDir();

        $returnValue = [];
        $returnValue[] = [new QtiPoint(10, 20), json_encode(['base' => ['point' => [10, 20]]])];
        $returnValue[] = [new QtiPair('A', 'B'), json_encode(['base' => ['pair' => ['A', 'B']]])];
        $returnValue[] = [new QtiDirectedPair('a', 'b'), json_encode(['base' => ['directedPair' => ['a', 'b']]])];
        $returnValue[] = [new QtiDuration('P3DT4H'), json_encode(['base' => ['duration' => 'P3DT4H']])];

        $file = new FileSystemFile($samples . 'datatypes/file/text-plain_text_data.txt');
        $returnValue[] = [$file, json_encode(['base' => ['file' => ['mime' => $file->getMimeType(), 'data' => base64_encode($file->getData()), 'name' => 'text.txt']]])];

        $file = new FileSystemFile($samples . 'datatypes/file/image-png_noname_data.png');
        $returnValue[] = [$file, json_encode(['base' => ['file' => ['mime' => $file->getMimeType(), 'data' => base64_encode($file->getData())]]])];

        return $returnValue;
    }

    public function marshallMultipleProvider()
    {
        $returnValue = [];

        // bool multiple().
        $container = new MultipleContainer(BaseType::BOOLEAN, []);
        $json = json_encode(['list' => ['boolean' => []]]);
        $returnValue[] = [$container, $json];

        // bool multiple(true, false, true).
        $container = new MultipleContainer(BaseType::BOOLEAN, [new QtiBoolean(true), new QtiBoolean(false), new QtiBoolean(true)]);
        $json = json_encode(['list' => ['boolean' => [true, false, true]]]);
        $returnValue[] = [$container, $json];

        // bool multiple(true, null, false).
        $container = new MultipleContainer(BaseType::BOOLEAN, [new QtiBoolean(true), null, new QtiBoolean(false)]);
        $json = json_encode(['list' => ['boolean' => [true, null, false]]]);
        $returnValue[] = [$container, $json];

        // bool multiple (null)
        $container = new MultipleContainer(BaseType::BOOLEAN, [null]);
        $json = json_encode(['list' => ['boolean' => [null]]]);
        $returnValue[] = [$container, $json];

        // integer multiple(2, 3, 5, 7, 11, 13).
        $container = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(2), new QtiInteger(3), new QtiInteger(5), new QtiInteger(7), new QtiInteger(11), new QtiInteger(13)]);
        $json = json_encode(['list' => ['integer' => [2, 3, 5, 7, 11, 13]]]);
        $returnValue[] = [$container, $json];

        // float multiple(3.1415926, 12.34, 98.76).
        $container = new MultipleContainer(BaseType::FLOAT, [new QtiFloat(3.1415926), new QtiFloat(12.34), new QtiFloat(98.76)]);
        $json = json_encode(['list' => ['float' => [3.1415926, 12.34, 98.76]]]);
        $returnValue[] = [$container, $json];

        // string multiple("Another", "And Another").
        $container = new MultipleContainer(BaseType::STRING, [new QtiString('Another'), new QtiString('And another')]);
        $json = json_encode(['list' => ['string' => ['Another', 'And another']]]);
        $returnValue[] = [$container, $json];

        // point multiple(point(123, 456), point(640, 480)).
        $container = new MultipleContainer(BaseType::POINT, [new QtiPoint(123, 456), new QtiPoint(640, 480)]);
        $json = json_encode(['list' => ['point' => [[123, 456], [640, 480]]]]);
        $returnValue[] = [$container, $json];

        // pair multiple(pair(A, B), pair(C, D)).
        $container = new MultipleContainer(BaseType::PAIR, [new QtiPair('A', 'B'), new QtiPair('D', 'C')]);
        $json = json_encode(['list' => ['pair' => [['A', 'B'], ['D', 'C']]]]);
        $returnValue[] = [$container, $json];

        // pair multiple(pair(A, B), pair(C, D)).
        $container = new MultipleContainer(BaseType::DIRECTED_PAIR, [new QtiDirectedPair('A', 'B'), new QtiDirectedPair('D', 'C')]);
        $json = json_encode(['list' => ['directedPair' => [['A', 'B'], ['D', 'C']]]]);
        $returnValue[] = [$container, $json];

        // duration multiple("P3Y6M4DT12H30M5S", "P4Y").
        $container = new MultipleContainer(BaseType::DURATION, [new QtiDuration('PT4M10S'), new QtiDuration('P4Y')]);
        $json = json_encode(['list' => ['duration' => ['PT4M10S', 'P4Y']]]);
        $returnValue[] = [$container, $json];

        // uri multiple("file:///aFile.txt", "file:///abc.txt").
        $container = new MultipleContainer(BaseType::URI, [new QtiUri('file:///aFile.txt'), new QtiUri('file:///abc.txt')]);
        $json = json_encode(['list' => ['uri' => ['file:///aFile.txt', 'file:///abc.txt']]]);
        $returnValue[] = [$container, $json];

        // intOrIdentifier multiple(2, "_id").
        $container = new MultipleContainer(BaseType::INT_OR_IDENTIFIER, [new QtiIntOrIdentifier(2), new QtiIntOrIdentifier('_id')]);
        $json = json_encode(['list' => ['intOrIdentifier' => [2, '_id']]]);
        $returnValue[] = [$container, $json];

        // identifier multiple('_id1', 'id2', 'ID3').
        $container = new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('_id1'), new QtiIdentifier('id2'), new QtiIdentifier('ID3')]);
        $json = json_encode(['list' => ['identifier' => ['_id1', 'id2', 'ID3']]]);
        $returnValue[] = [$container, $json];

        return $returnValue;
    }

    public function marshallOrderedProvider()
    {
        $returnValue = [];

        // bool multiple().
        $container = new OrderedContainer(BaseType::BOOLEAN, []);
        $json = json_encode(['list' => ['boolean' => []]]);
        $returnValue[] = [$container, $json];

        // bool multiple(true, false, true).
        $container = new OrderedContainer(BaseType::BOOLEAN, [new QtiBoolean(true), new QtiBoolean(false), new QtiBoolean(true)]);
        $json = json_encode(['list' => ['boolean' => [true, false, true]]]);
        $returnValue[] = [$container, $json];

        // bool multiple(true, null, false)
        $container = new OrderedContainer(BaseType::BOOLEAN, [new QtiBoolean(true), null, new QtiBoolean(false)]);
        $json = json_encode(['list' => ['boolean' => [true, null, false]]]);
        $returnValue[] = [$container, $json];

        // bool multiple (null)
        $container = new OrderedContainer(BaseType::BOOLEAN, [null]);
        $json = json_encode(['list' => ['boolean' => [null]]]);
        $returnValue[] = [$container, $json];

        return $returnValue;
    }

    public function marshallRecordProvider()
    {
        $returnValue = [];

        // empty record.
        $record = new RecordContainer();
        $json = json_encode(['record' => []]);
        $returnValue[] = [$record, $json];

        // single boolean value record.
        $record = new RecordContainer(['rock' => new QtiBoolean(true)]);
        $json = json_encode(['record' => [['name' => 'rock', 'base' => ['boolean' => true]]]]);
        $returnValue[] = [$record, $json];

        // single null valued record.
        $record = new RecordContainer(['rock' => null]);
        $json = json_encode(['record' => [['name' => 'rock', 'base' => null]]]);
        $returnValue[] = [$record, $json];

        // miscellaneous record.
        $record = new RecordContainer(['numeric' => new QtiFloat(1337.1337), 'null' => null, 'coordinates' => new QtiPoint(10, 20)]);
        $json = json_encode(['record' => [['name' => 'numeric', 'base' => ['float' => 1337.1337]], ['name' => 'null', 'base' => null], ['name' => 'coordinates', 'base' => ['point' => [10, 20]]]]]);
        $returnValue[] = [$record, $json];

        return $returnValue;
    }

    public function marshallStateProvider()
    {
        $returnValue = [];

        // empty state.
        $state = new State();
        $json = json_encode([]);
        $returnValue[] = [$state, $json];

        // simple state.
        $state = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]);
        $json = json_encode(['RESPONSE' => ['base' => ['identifier' => 'ChoiceA']]]);
        $returnValue[] = [$state, $json];

        // complex state 1.
        $state = new State();
        $state->setVariable(new ResponseVariable('RESPONSE1', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA')));
        $state->setVariable(new ResponseVariable('RESPONSE2', Cardinality::SINGLE, BaseType::DURATION));
        $state->setVariable(new ResponseVariable('RESPONSE3', Cardinality::RECORD, -1, new RecordContainer(['A' => new QtiIdentifier('A'), 'B' => new QtiIdentifier('B')])));
        $json = json_encode(['RESPONSE1' => ['base' => ['identifier' => 'ChoiceA']], 'RESPONSE2' => ['base' => null], 'RESPONSE3' => ['record' => [['name' => 'A', 'base' => ['identifier' => 'A']], ['name' => 'B', 'base' => ['identifier' => 'B']]]]]);
        $returnValue[] = [$state, $json];

        // complex state 2.
        $state = new State();
        $state->setVariable(new OutcomeVariable('OUTCOME1', Cardinality::MULTIPLE, BaseType::FLOAT, new MultipleContainer(BaseType::FLOAT, [new QtiFloat(0.0), new QtiFloat(10.10)])));
        $state->setVariable(new ResponseVariable('RESPONSE1', Cardinality::ORDERED, BaseType::POINT, new OrderedContainer(BaseType::POINT, [new QtiPoint(10, 20)])));
        $json = json_encode(['OUTCOME1' => ['list' => ['float' => [0.0, 10.10]]], 'RESPONSE1' => ['list' => ['point' => [[10, 20]]]]]);
        $returnValue[] = [$state, $json];

        return $returnValue;
    }

    public function marshallInvalidInputProvider()
    {
        return [
            [10],
            ['string!'],
            [new stdClass()],
        ];
    }
}
