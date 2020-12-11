<?php

namespace qtismtest\data\storage\xml\marshalling;

use qtism\data\content\xhtml\html5\Source;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\data\storage\xml\marshalling\MarshallingException;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

class SourceMarshallerTest extends Html5ElementMarshallerTest
{
    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshallerDoesNotExistInQti21(): void
    {
        $source = new Source('http://example.com/');
        $this->assertHtml5MarshallingOnlyInQti22AndAbove($source, 'source');
    }

    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshall22(): void
    {
        $src = 'http://example.com/';
        $type = 'text/plain';

        $expected = sprintf('<source src="%s" type="%s"/>', $src, $type);
        $source = new Source($src, $type);

        $this->assertMarshalling($expected, $source);
    }

    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshall22WithDefaultValues(): void
    {
        $src = 'http://example.com/';

        $expected = sprintf('<source src="%s"/>', $src);
        $source = new Source($src);

        $this->assertMarshalling($expected, $source);
    }

    /**
     * @throws MarshallerNotFoundException
     */
    public function testUnMarshallerDoesNotExistInQti21(): void
    {
        $this->assertHtml5UnmarshallingOnlyInQti22AndAbove('<source/>', 'source');
    }

    /**
     * @throws MarshallerNotFoundException
     */
    public function testUnmarshall22(): void
    {
        $src = 'http://example.com/';
        $type = 'text/plain';

        $xml = sprintf('<source src="%s" type="%s"/>', $src, $type);

        $expected = new Source($src, $type);
        $this->assertUnmarshalling($expected, $xml);
    }

    /**
     * @throws MarshallerNotFoundException
     */
    public function testUnmarshall22WithDefaultValues(): void
    {
        $src = 'http://example.com/';

        $xml = sprintf('<source src="%s"/>', $src);

        $expected = new Source($src);

        $this->assertUnmarshalling($expected, $xml);
    }

    /**
     * @dataProvider WrongXmlToUnmarshall
     * @param string $xml
     * @param string $exception
     * @param string $message
     * @throws MarshallerNotFoundException
     */
    public function testUnmarshallingExceptions(string $xml, string $exception, string $message): void
    {
        $this->assertUnmarshallingException($xml, $exception, $message);
    }

    public function WrongXmlToUnmarshall(): array
    {
        return [
            // TODO: fix Format::isUri because a relative path is a valid URI but not an empty string.
            // ['<source src="^"/>', InvalidArgumentException::class, 'The "src" argument must be a valid URI, " " given.'],

            ['<source/>', UnmarshallingException::class, 'Error while unmarshalling element "source": The "src" argument must be a valid URI, "NULL" given.'],
            ['<source src=""/>', UnmarshallingException::class, 'Error while unmarshalling element "source": The "src" argument must be a valid URI, "NULL" given.'],
            ['<source src="http://example.com/" type="blah"/>', UnmarshallingException::class, 'The "type" argument must be a valid Mime type, "blah" given.'],
        ];
    }
}
