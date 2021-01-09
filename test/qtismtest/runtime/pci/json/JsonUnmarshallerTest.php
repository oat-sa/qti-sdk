<?php

namespace qtismtest\runtime\pci\json;

use qtism\common\datatypes\files\FileHash;
use qtism\common\datatypes\files\FileManagerException;
use qtism\common\datatypes\files\FileSystemFile;
use qtism\common\datatypes\files\FileSystemFileManager;
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
use qtism\common\datatypes\QtiScalar;
use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiUri;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\pci\json\Unmarshaller;
use qtism\runtime\pci\json\UnmarshallingException;
use qtismtest\QtiSmTestCase;
use stdClass;

/**
 * Class JsonUnmarshallerTest
 */
class JsonUnmarshallerTest extends QtiSmTestCase
{
    /**
     * @return Unmarshaller
     */
    protected static function createUnmarshaller()
    {
        return new Unmarshaller(new FileSystemFileManager());
    }

    /**
     * @dataProvider unmarshallScalarProvider
     *
     * @param QtiScalar $expectedScalar
     * @param string $json
     * @throws UnmarshallingException
     * @throws FileManagerException
     */
    public function testUnmarshallScalar(QtiScalar $expectedScalar = null, $json)
    {
        $unmarshaller = self::createUnmarshaller();
        if (is_null($expectedScalar) === false) {
            $this::assertTrue($unmarshaller->unmarshall($json)->equals($expectedScalar));
        } else {
            $this::assertSame($expectedScalar, $unmarshaller->unmarshall($json));
        }
    }

    /**
     * @dataProvider unmarshallComplexProvider
     *
     * @param QtiDatatype $expectedComplex
     * @param string $json
     * @throws UnmarshallingException
     * @throws FileManagerException
     */
    public function testUnmarshallComplex(QtiDatatype $expectedComplex, $json)
    {
        $unmarshaller = self::createUnmarshaller();
        $value = $unmarshaller->unmarshall($json);
        $this::assertTrue($expectedComplex->equals($value));
    }

    /**
     * @dataProvider unmarshallFileProvider
     *
     * @param FileSystemFile $expectedFile
     * @param string $json
     * @throws UnmarshallingException
     * @throws FileManagerException
     */
    public function testUnmarshallFile(FileSystemFile $expectedFile, $json)
    {
        $unmarshaller = self::createUnmarshaller();
        $value = $unmarshaller->unmarshall($json);
        $this::assertTrue($expectedFile->equals($value));

        // cleanup.
        $fileManager = new FileSystemFileManager();
        $fileManager->delete($value);
    }

    public function testUnmarshallFileHash()
    {
        $id = 'http://some.cloud.storage/path/to/file.txt';
        $mimeType = 'text/plain';
        $filename = 'file.txt';
        $sha256 = '165940940A02A187E4463FF467090930038C5AF8FC26107BF301E714F599A1DA';

        $expectedFile = new FileHash($id, $mimeType, $filename, $sha256);

        $json = sprintf(
            '{ "base" : { "%s" : {
            "mime" : "%s", 
            "data" : "%s", 
            "name" : "%s",
            "id" : "%s" } } }',
            FileHash::FILE_HASH_KEY,
            $mimeType,
            $sha256,
            $filename,
            $id
        );      

        $unmarshaller = self::createUnmarshaller();
        $value = $unmarshaller->unmarshall($json);
        $this::assertTrue($expectedFile->equals($value));
    }

    /**
     * @dataProvider unmarshallListProvider
     *
     * @param MultipleContainer $expectedContainer
     * @param string $json
     * @throws UnmarshallingException
     * @throws FileManagerException
     */
    public function testUnmarshallList(MultipleContainer $expectedContainer, $json)
    {
        $unmarshaller = self::createUnmarshaller();
        $this::assertTrue($expectedContainer->equals($unmarshaller->unmarshall($json)));
    }

    /**
     * @dataProvider unmarshallRecordProvider
     *
     * @param RecordContainer $expectedRecord
     * @param string $json
     * @throws UnmarshallingException
     * @throws FileManagerException
     */
    public function testUnmarshallRecord(RecordContainer $expectedRecord, $json)
    {
        $unmarshaller = self::createUnmarshaller();
        $this::assertTrue($expectedRecord->equals($unmarshaller->unmarshall($json)));
    }

    /**
     * @dataProvider unmarshallInvalidProvider
     *
     * @param mixed $input
     * @throws UnmarshallingException
     * @throws FileManagerException
     */
    public function testUnmarshallInvalid($input)
    {
        $unmarshaller = self::createUnmarshaller();
        $this->expectException(UnmarshallingException::class);
        $unmarshaller->unmarshall($input);
    }

    public function testUnmarshallNoAssociative()
    {
        $unmarshaller = self::createUnmarshaller();
        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The '" . Unmarshaller::class . "::unmarshall' method only accepts a JSON string or a non-empty array as argument, 'boolean' given.");
        $unmarshaller->unmarshall(true);
    }

    public function testUnmarshallListUnknownBaseType()
    {
        $unmarshaller = self::createUnmarshaller();

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("Unknown QTI baseType 'unknownbasetype'.");

        $unmarshaller->unmarshall('{ "list" : { "unknownbasetype" : ["_id1", "id2", "ID3"] } }');
    }

    public function testUnmarshallListNonBaseTypeCompliantValue()
    {
        $unmarshaller = self::createUnmarshaller();

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage('A value does not satisfy its baseType.');

        $unmarshaller->unmarshall('{ "list" : { "identifier" : [true, "id2", "ID3"] } }');
    }

    public function testUnmarshallState()
    {
        $json = '
            {
                "RESPONSE1": { "base" : { "identifier" : "ChoiceA" } },
                "RESPONSE2": { "list" : { "identifier" : ["_id1", "id2", "ID3"] } },
                "RESPONSE3": { "record" : [ { "name" : "rock", "base": { "identifier" : "Paper" } } ] },
                "RESPONSE4": { "base" : null }
            }
        ';

        $unmarshaller = self::createUnmarshaller();
        $state = $unmarshaller->unmarshall($json);
        $this::assertEquals(4, count($state));
        $this::assertEquals(['RESPONSE1', 'RESPONSE2', 'RESPONSE3', 'RESPONSE4'], array_keys($state));

        $response1 = new QtiIdentifier('ChoiceA');
        $response2 = new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('_id1'), new QtiIdentifier('id2'), new QtiIdentifier('ID3')]);
        $response3 = new RecordContainer(['rock' => new QtiIdentifier('Paper')]);
        $response4 = null;

        $this::assertTrue($response1->equals($state['RESPONSE1']));
        $this::assertTrue($response2->equals($state['RESPONSE2']));
        $this::assertTrue($response3->equals($state['RESPONSE3']));
        $this::assertSame($response4, $state['RESPONSE4']);
    }

    /**
     * @return array
     */
    public function unmarshallScalarProvider()
    {
        return [
            [new QtiBoolean(true), '{ "base" : {"boolean" : true } }'],
            [new QtiBoolean(false), '{ "base" : {"boolean" : false } }'],
            [new QtiInteger(123), '{ "base" : {"integer" : 123 } }'],
            [new QtiFloat(23.23), '{ "base" : {"float" : 23.23 } }'],
            [new QtiFloat(6.0), '{ "base" : {"float" : 6 } }'],
            [new QtiString('string'), '{ "base" : {"string" : "string" } }'],
            [new QtiUri('http://www.taotesting.com'), '{ "base" : {"uri" : "http://www.taotesting.com" } }'],
            [new QtiIntOrIdentifier(10), '{ "base" : {"intOrIdentifier" : 10 } }'],
            [new QtiIntOrIdentifier('_id1'), '{ "base" : {"identifier" : "_id1" } }'],
            [new QtiIdentifier('_id1'), '{ "base" : {"identifier" : "_id1" } }'],
            [null, '{ "base": null }'],
        ];
    }

    /**
     * @return array
     */
    public function unmarshallComplexProvider()
    {
        $returnValue = [];

        $returnValue[] = [new QtiPoint(10, 20), '{ "base" : { "point" : [10, 20] } }'];
        $returnValue[] = [new QtiPair('A', 'B'), '{ "base" : { "pair" : ["A", "B"] } }'];
        $returnValue[] = [new QtiDirectedPair('a', 'b'), '{ "base" : { "directedPair" : ["a", "b"] } }'];
        $returnValue[] = [new QtiDuration('PT3S'), '{ "base" : { "duration" : "PT3S" } }'];

        return $returnValue;
    }

    /**
     * @return array
     * @throws FileManagerException
     */
    public function unmarshallFileProvider()
    {
        $returnValue = [];
        $samples = self::samplesDir();
        $fileManager = new FileSystemFileManager();

        $file = $fileManager->retrieve($samples . 'datatypes/file/files_2.txt');
        $returnValue[] = [$file, '{ "base" : { "file" : { "mime" : "text\/html", "data" : ' . json_encode(base64_encode('<img src="/qtism/img.png"/>')) . ' } } }'];

        $file = $fileManager->retrieve($samples . 'datatypes/file/text-plain_text_data.txt');
        $returnValue[] = [$file, '{ "base" : { "file" : { "mime" : "text\/plain", "data" : ' . json_encode(base64_encode('Some text...')) . ', "name" : "text.txt" } } }'];

        $originalfile = $samples . 'datatypes/file/raw/image.png';
        $filepath = $samples . 'datatypes/file/image-png_noname_data.png';
        $file = $fileManager->retrieve($filepath);
        $returnValue[] = [$file, '{ "base" : { "file" : { "mime" : "image\/png", "data" : ' . json_encode(base64_encode(file_get_contents($originalfile))) . ' } } }'];

        return $returnValue;
    }

    /**
     * @return array
     */
    public function unmarshallListProvider()
    {
        $returnValue = [];

        $container = new MultipleContainer(BaseType::BOOLEAN, [new QtiBoolean(true), new QtiBoolean(false), new QtiBoolean(true), new QtiBoolean(true)]);
        $json = '{ "list" : { "boolean" : [true, false, true, true] } }';
        $returnValue[] = [$container, $json];

        $container = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(2), new QtiInteger(3), new QtiInteger(5), new QtiInteger(7), new QtiInteger(11), new QtiInteger(13)]);
        $json = '{ "list" : { "integer" : [2, 3, 5, 7, 11, 13] } }';
        $returnValue[] = [$container, $json];

        $container = new MultipleContainer(BaseType::FLOAT, [new QtiFloat(3.1415926), new QtiFloat(12.34), new QtiFloat(98.76)]);
        $json = '{ "list" : { "float" : [3.1415926, 12.34, 98.76] } }';
        $returnValue[] = [$container, $json];

        $container = new MultipleContainer(BaseType::STRING, [new QtiString('Another'), new QtiString('And Another')]);
        $json = '{ "list" : { "string" : ["Another", "And Another"] } }';
        $returnValue[] = [$container, $json];

        $container = new MultipleContainer(BaseType::POINT, [new QtiPoint(123, 456), new QtiPoint(640, 480)]);
        $json = '{ "list" : { "point" : [[123, 456], [640, 480]] } }';
        $returnValue[] = [$container, $json];

        $container = new MultipleContainer(BaseType::PAIR, [new QtiPair('A', 'B'), new QtiPair('D', 'C')]);
        $json = '{ "list" : { "pair" : [["A", "B"], ["D", "C"]] } }';
        $returnValue[] = [$container, $json];

        $container = new MultipleContainer(BaseType::DIRECTED_PAIR, [new QtiDirectedPair('A', 'B'), new QtiDirectedPair('D', 'C')]);
        $json = '{ "list" : { "directedPair" : [["A", "B"], ["D", "C"]] } }';
        $returnValue[] = [$container, $json];

        $container = new MultipleContainer(BaseType::DURATION, [new QtiDuration('PT5S'), new QtiDuration('PT10S')]);
        $json = '{ "list" : { "duration" : ["PT5S", "PT10S"] } }';
        $returnValue[] = [$container, $json];

        $container = new MultipleContainer(BaseType::BOOLEAN, [new QtiBoolean(true), null, new QtiBoolean(false)]);
        $json = '{ "list" : { "boolean": [true, null, false] } }';
        $returnValue[] = [$container, $json];

        return $returnValue;
    }

    /**
     * @return array
     */
    public function unmarshallRecordProvider()
    {
        $returnValue = [];

        $record = new RecordContainer();
        $json = '{ "record" : [] }';
        $returnValue[] = [$record, $json];

        $record = new RecordContainer(['A' => new QtiString('A')]);
        $json = '{ "record" : [ { "name" : "A", "base" : { "string" : "A" } } ] }';
        $returnValue[] = [$record, $json];

        $record = new RecordContainer(['A' => new QtiString('A'), 'B' => null]);
        $json = '{ "record" : [ { "name" : "A", "base" : { "string" : "A" } }, { "name" : "B", "base" : null } ] }';
        $returnValue[] = [$record, $json];

        $record = new RecordContainer(['A' => null]);
        $json = '{ "record" : [ { "name": "A" } ] }';
        $returnValue[] = [$record, $json];

        return $returnValue;
    }

    /**
     * @return array
     */
    public function unmarshallInvalidProvider()
    {
        return [
            [new stdClass()],
            [''],
            ['{ "list": [} }'],
            ['{ "base" : { "booleanooo" : true } }'],
            ['{}'],
            ['{ "base" : { "boolean" : "yop" } }'],
            ['[ "base" : { "boolean" : true} ]'],
            ['{ "list" : { "boolean" : null }'],
            ['{ "list" : { } }'],
            ['{ "liste" : { "boolean" : true } } '],
            ['{ "record" : [ { "namez" } ] '],
        ];
    }
}
