<?php

namespace qtismtest\data\storage\xml\marshalling;

use qtism\data\content\xhtml\html5\Video;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\data\storage\xml\marshalling\MarshallingException;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

class VideoMarshallerTest extends Html5ElementMarshallerTest
{
    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshallerDoesNotExistInQti21(): void
    {
        $this->assertHtml5MarshallingOnlyInQti22AndAbove(new Video(), 'video');
    }

    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshall22(): void
    {
        $width = 320;
        $height = 240;
        $poster = 'http://example.com/poster';

        $expected = sprintf(
            '<'.$this->namespaceTag('video').' width="%s" height="%s" poster="%s"/>',
            $width,
            $height,
            $poster
        );

        $object = new Video(null, $width, $height, $poster);

        $this->assertMarshalling($expected, $object);
    }

    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshall22WithDefaultValues(): void
    {
        $expected = '<'.$this->namespaceTag('video').'/>';

        $video = new Video();

        $this->assertMarshalling($expected, $video);
    }

    /**
     * @throws MarshallerNotFoundException
     */
    public function testUnMarshallerDoesNotExistInQti21(): void
    {
        $this->assertHtml5UnmarshallingOnlyInQti22AndAbove('<'.$this->namespaceTag('video').'/>', 'video');
    }

    /**
     * @throws MarshallerNotFoundException
     */
    public function testUnmarshall22(): void
    {
        $width = 320;
        $height = 240;
        $poster = 'http://example.com/poster';

        $xml = sprintf(
            '<'.$this->namespaceTag('video').' width="%s" height="%s" poster="%s"/>',
            $width,
            $height,
            $poster
        );

        $expected = new Video(null, $width, $height, $poster);

        $this->assertUnmarshalling($expected, $xml);
    }

    public function testUnmarshall22WithNonIntegerWidthAndHeightStoresZeros(): void
    {
        $xml = sprintf('<'.$this->namespaceTag('video').' width="not integer" height="not integer"/>');

        $element = $this->createDOMElement($xml);
        $marshaller = $this->getMarshallerFactory('2.2.0')->createMarshaller($element);
        $object = $marshaller->unmarshall($element);

        self::assertSame(0, $object->getHeight());
        self::assertSame(0, $object->getWidth());
    }

    public function testUnmarshall22WithDefaultValues(): void
    {
        $xml = '<'.$this->namespaceTag('video').'/>';

        $expected = new Video();

        $this->assertUnmarshalling($expected, $xml);
    }

    /**
     * @dataProvider WrongXmlToUnmarshall
     * @param string $xml
     * @param string $exception
     * @param string $message
     * @throws MarshallerNotFoundException
     */
    public function testUnmarshallWithWrongTypesOrValues(string $xml, string $exception, string $message): void
    {
        $this->assertUnmarshallingException($xml, $exception, $message);
    }

    public function WrongXmlToUnmarshall(): array
    {
        return [
            // TODO: fix Format::isUri because a relative path is a valid URI but not an empty string.
            // ['<video src=" "/>', InvalidArgumentException::class, 'The "src" argument must be a valid URI, " " given.'],

            [
                '<'.$this->namespaceTag('video').' width="-1" poster="http://example.com/"/>',
                UnmarshallingException::class,
                'Error while unmarshalling element "video": The "width" argument must be 0 or a positive integer, "-1" given.',
            ],
            [
                '<'.$this->namespaceTag('video').' height="-1" poster="http://example.com/"/>',
                UnmarshallingException::class,
                'Error while unmarshalling element "video": The "height" argument must be 0 or a positive integer, "-1" given.',
            ],
        ];
    }
}
