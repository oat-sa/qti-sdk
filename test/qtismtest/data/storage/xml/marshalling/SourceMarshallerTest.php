<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use InvalidArgumentException;
use qtism\data\content\xhtml\html5\Role;
use qtism\data\content\xhtml\html5\Source;
use qtism\data\storage\xml\marshalling\UnmarshallingException;
use qtismtest\QtiSmTestCase;
use RuntimeException;

class SourceMarshallerTest extends QtiSmTestCase
{
    public function testMarshallerDoesNotExistInQti21()
    {
        $source = new Source('http://example.com/');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No marshaller implementation found while marshalling component \'source\'.');
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($source);
        $marshaller->marshall($source);
    }

    public function testMarshall22()
    {
        $src = 'http://example.com/';
        $type = 'text/plain';
        $id = 'Identifier';
        $class = 'a css class';
        $lang = 'es';
        $label = 'A label';
        $title = 'a title';
        $role = 'note';

        $expected = sprintf(
            '<source src="%s" type="%s" id="%s" class="%s" xml:lang="%s" label="%s" title="%s" role="%s"/>',
            $src,
            $type,
            $id,
            $class,
            $lang,
            $label,
            $title,
            $role
        );

        $source = new Source($src, $type, $id, $class, $lang, $label, $title, Role::getConstantByName($role));

        $marshaller = $this->getMarshallerFactory('2.2.0')->createMarshaller($source);
        $element = $marshaller->marshall($source);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals($expected, $dom->saveXML($element));
    }

    public function testMarshall22WithDefaultValues()
    {
        $src = 'http://example.com/';

        $expected = sprintf('<source src="%s"/>', $src);

        $source = new Source($src);

        $marshaller = $this->getMarshallerFactory('2.2.0')->createMarshaller($source);
        $element = $marshaller->marshall($source);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals($expected, $dom->saveXML($element));
    }

    public function testUnMarshallerDoesNotExistInQti21()
    {
        $element = $this->createDOMElement('<source src="http://example.com/"/>');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No Marshaller implementation found while unmarshalling element \'source\'.');
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $marshaller->unmarshall($element);
    }

    public function testUnmarshall22()
    {
        $src = 'http://example.com/';
        $type = 'text/plain';
        $id = 'Identifier';
        $class = 'a css class';
        $lang = 'es';
        $label = 'A label';
        $title = 'a title';
        $role = 'note';

        $xml = sprintf(
            '<source src="%s" type="%s" id="%s" class="%s" xml:lang="%s" label="%s" title="%s" role="%s"/>',
            $src,
            $type,
            $id,
            $class,
            $lang,
            $label,
            $title,
            $role
        );

        $element = $this->createDOMElement($xml);
        $marshaller = $this->getMarshallerFactory('2.2.0')->createMarshaller($element);
        $source = $marshaller->unmarshall($element);

        $this->assertInstanceOf(Source::class, $source);
        $this->assertEquals($src, $source->getSrc());
        $this->assertEquals($type, $source->getType());
        $this->assertEquals($id, $source->getId());
        $this->assertEquals($class, $source->getClass());
        $this->assertEquals($lang, $source->getLang());
        $this->assertEquals($label, $source->getLabel());
        $this->assertEquals($title, $source->getTitle());
        $this->assertEquals($role, Role::getNameByConstant($source->getRole()));
    }

    public function testUnmarshall22WithDefaultValues()
    {
        $src = 'http://example.com/';
        $type = '';
        $id = '';
        $class = '';
        $lang = '';
        $label = '';

        $xml = sprintf('<source src="%s"/>', $src);

        $element = $this->createDOMElement($xml);
        $marshaller = $this->getMarshallerFactory('2.2.0')->createMarshaller($element);
        $source = $marshaller->unmarshall($element);

        $this->assertInstanceOf(Source::class, $source);
        $this->assertEquals($src, $source->getSrc());
        $this->assertEquals($type, $source->getType());
        $this->assertEquals($id, $source->getId());
        $this->assertEquals($class, $source->getClass());
        $this->assertEquals($lang, $source->getLang());
        $this->assertEquals($label, $source->getLabel());
    }

    public function testUnmarshallMissingSrc()
    {
        $element = $this->createDOMElement('<source/>');
        $marshaller = $this->getMarshallerFactory('2.2.0')->createMarshaller($element);

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage('The required attribute "src" is missing from element "source".');

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
            // ['<source src="^"/>', InvalidArgumentException::class, 'The "src" argument must be a valid URI, " " given.'],

            ['<source src=""/>', UnmarshallingException::class, 'The required attribute "src" is missing from element "source".'],
            ['<source src="http://example.com/" type="blah"/>', InvalidArgumentException::class, 'The "type" argument must be a valid Mime type, "blah" given.'],
        ];
    }
}
