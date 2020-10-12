<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use InvalidArgumentException;
use qtism\data\content\xhtml\html5\Track;
use qtism\data\content\xhtml\html5\TrackKind;
use qtism\data\storage\xml\marshalling\UnmarshallingException;
use qtismtest\QtiSmTestCase;
use RuntimeException;

class TrackMarshallerTest extends QtiSmTestCase
{
    public function testMarshallerDoesNotExistInQti21()
    {
        $track = new Track('http://example.com/');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No marshaller implementation found while marshalling component \'track\'.');
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($track);
        $marshaller->marshall($track);
    }

    public function testMarshall22()
    {
        $src = 'http://example.com/';
        $default = true;
        $kind = 'chapters';
        $srcLang = 'en';
        $id = 'Identifier';
        $class = 'a css class';
        $lang = 'es';
        $label = 'A label';

        $expected = sprintf(
            '<track src="%s" default="%s" kind="%s" srclang="%s" id="%s" class="%s" xml:lang="%s" label="%s"/>',
            $src,
            $default ? 'true' : 'false',
            $kind,
            $srcLang,
            $id,
            $class,
            $lang,
            $label
        );

        $track = new Track($src, $default, TrackKind::getConstantByName($kind), $srcLang, $id, $class, $lang, $label);

        $marshaller = $this->getMarshallerFactory('2.2.0')->createMarshaller($track);
        $element = $marshaller->marshall($track);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals($expected, $dom->saveXML($element));
    }

    public function testMarshall22WithDefaultValues()
    {
        $src = 'http://example.com/';

        $expected = sprintf('<track src="%s"/>', $src);

        $track = new Track($src);

        $marshaller = $this->getMarshallerFactory('2.2.0')->createMarshaller($track);
        $element = $marshaller->marshall($track);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals($expected, $dom->saveXML($element));
    }

    public function testUnMarshallerDoesNotExistInQti21()
    {
        $element = $this->createDOMElement('<track src="http://example.com/"/>');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No Marshaller implementation found while unmarshalling element \'track\'.');
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $marshaller->unmarshall($element);
    }

    public function testUnmarshall22()
    {
        $src = 'http://example.com/';
        $default = true;
        $kind = 'chapters';
        $srcLang = 'en';
        $id = 'Identifier';
        $class = 'a css class';
        $lang = 'es';
        $label = 'A label';

        $xml = sprintf(
            '<track src="%s" default="%s" kind="%s" srclang="%s" id="%s" class="%s" xml:lang="%s" label="%s"/>',
            $src,
            $default ? 'true' : 'false',
            $kind,
            $srcLang,
            $id,
            $class,
            $lang,
            $label
        );

        $element = $this->createDOMElement($xml);
        $marshaller = $this->getMarshallerFactory('2.2.0')->createMarshaller($element);
        $track = $marshaller->unmarshall($element);

        $this->assertInstanceOf(Track::class, $track);
        $this->assertEquals($src, $track->getSrc());
        $this->assertEquals($default, $track->getDefault());
        $this->assertSame($kind, TrackKind::getNameByConstant($track->getKind()));
        $this->assertEquals($srcLang, $track->getSrcLang());
        $this->assertEquals($id, $track->getId());
        $this->assertEquals($class, $track->getClass());
        $this->assertEquals($lang, $track->getLang());
        $this->assertEquals($label, $track->getLabel());
    }

    public function testUnmarshall22WithDefaultValues()
    {
        $src = 'http://example.com/';
        $default = false;
        $kind = 'subtitles';
        $srcLang = '';
        $id = '';
        $class = '';
        $lang = '';
        $label = '';

        $xml = sprintf('<track src="%s"/>', $src);

        $element = $this->createDOMElement($xml);
        $marshaller = $this->getMarshallerFactory('2.2.0')->createMarshaller($element);
        $track = $marshaller->unmarshall($element);

        $this->assertInstanceOf(Track::class, $track);
        $this->assertEquals($src, $track->getSrc());
        $this->assertEquals($default, $track->getDefault());
        $this->assertSame($kind, TrackKind::getNameByConstant($track->getKind()));
        $this->assertEquals($srcLang, $track->getSrcLang());
        $this->assertEquals($id, $track->getId());
        $this->assertEquals($class, $track->getClass());
        $this->assertEquals($lang, $track->getLang());
        $this->assertEquals($label, $track->getLabel());
    }

    public function testUnmarshallMissingSrc()
    {
        $element = $this->createDOMElement('<track/>');
        $marshaller = $this->getMarshallerFactory('2.2.0')->createMarshaller($element);

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage('The required attribute "src" is missing from element "track".');

        $marshaller->unmarshall($element);
    }

    /**
     * @dataProvider WrongXmlToUnmarshall
     * @param string $xml
     * @param string $exception
     * @param string $message
     */
    public function testUnmarshallWithWrongTypesOrValues(string $xml, string $exception, string $message)
    {
        $element = $this->createDOMElement($xml);
        $marshaller = $this->getMarshallerFactory('2.2.0')->createMarshaller($element);

        $this->expectException($exception);
        $this->expectExceptionMessage($message);

        $marshaller->unmarshall($element);
    }

    public function WrongXmlToUnmarshall(): array
    {
        return [
            // TODO: fix Format::isUri because a relative path is a valid URI but not an empty string.
            // ['<track src=" "/>', InvalidArgumentException::class, 'The "src" argument must be a valid URI, " " given.'],

            ['<track src=""/>', UnmarshallingException::class, 'The required attribute "src" is missing from element "track".'],
            ['<track src="http://example.com/" default="blah"/>', InvalidArgumentException::class, 'String value "true" or "false" expected, "blah" given.'],
            ['<track src="http://example.com/" kind="blah"/>', InvalidArgumentException::class, 'The "kind" argument must be a value from the TrackKind enumeration, "boolean" given.'],
        ];
    }
}
