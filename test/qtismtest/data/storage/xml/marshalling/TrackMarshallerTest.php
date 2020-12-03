<?php

namespace qtismtest\data\storage\xml\marshalling;

use InvalidArgumentException;
use qtism\data\content\xhtml\html5\Track;
use qtism\data\content\xhtml\html5\TrackKind;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\data\storage\xml\marshalling\MarshallingException;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

class TrackMarshallerTest extends Html5ElementMarshallerTest
{
    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshallerDoesNotExistInQti21(): void
    {
        $track = new Track('http://example.com/');
        $this->assertHtml5MarshallingOnlyInQti22AndAbove($track, 'track');
    }

    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshall22(): void
    {
        $src = 'http://example.com/';
        $default = true;
        $kind = 'chapters';
        $srcLang = 'en';

        $expected = sprintf(
            '<track src="%s" default="%s" kind="%s" srclang="%s"/>',
            $src,
            $default ? 'true' : 'false',
            $kind,
            $srcLang
        );

        $track = new Track($src, $default, TrackKind::getConstantByName($kind), $srcLang);

        $this->assertMarshalling($expected, $track);
    }

    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshall22WithDefaultValues(): void
    {
        $src = 'http://example.com/';

        $expected = sprintf('<track src="%s" srclang="en"/>', $src);
        $track = new Track($src);

        $this->assertMarshalling($expected, $track);
    }

    /**
     * @throws MarshallerNotFoundException
     */
    public function testUnMarshallerDoesNotExistInQti21(): void
    {
        $this->assertHtml5UnmarshallingOnlyInQti22AndAbove('<track/>', 'track');
    }

    /**
     * @throws MarshallerNotFoundException
     */
    public function testUnmarshall22(): void
    {
        $src = 'http://example.com/';
        $default = true;
        $kind = 'chapters';
        $srcLang = 'en';

        $xml = sprintf(
            '<track src="%s" default="%s" kind="%s" srclang="%s"/>',
            $src,
            $default ? 'true' : 'false',
            $kind,
            $srcLang
        );

        $expected = new Track($src, $default, TrackKind::getConstantByName($kind), $srcLang);

        $this->assertUnmarshalling($expected, $xml);
    }

    /**
     * @throws MarshallerNotFoundException
     */
    public function testUnmarshall22WithDefaultValues(): void
    {
        $src = 'http://example.com/';

        $xml = sprintf('<track src="%s"/>', $src);

        $expected = new Track($src);

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
            // ['<track src=" "/>', InvalidArgumentException::class, 'The "src" argument must be a valid URI, " " given.'],

            ['<track/>', UnmarshallingException::class, 'The required attribute "src" is missing from element "track".'],
            ['<track src=""/>', UnmarshallingException::class, 'The required attribute "src" is missing from element "track".'],
            ['<track src="http://example.com/" default="blah"/>', InvalidArgumentException::class, 'String value "true" or "false" expected, "blah" given.'],
            ['<track src="http://example.com/" kind="blah"/>', InvalidArgumentException::class, 'The "kind" argument must be a value from the TrackKind enumeration, "boolean" given.'],
        ];
    }
}
