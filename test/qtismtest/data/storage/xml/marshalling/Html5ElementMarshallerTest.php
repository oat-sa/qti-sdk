<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\data\content\enums\Role;
use qtism\data\content\xhtml\html5\Html5Element;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\storage\xml\marshalling\Html5ElementMarshaller;
use qtism\data\storage\xml\marshalling\Marshaller;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\data\storage\xml\marshalling\MarshallingException;
use qtism\data\storage\xml\marshalling\UnmarshallingException;
use qtismtest\QtiSmTestCase;
use RuntimeException;

class Html5ElementMarshallerTest extends QtiSmTestCase
{
    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshall22(): void
    {
        $title = 'a title';
        $role = 'note';
        $id = 'identifier';
        $class = 'the class';
        $lang = 'english';
        $label = 'This is a label.';

        $expected = sprintf(
            '<html5 id="%s" class="%s" xml:lang="%s" label="%s" title="%s" role="%s"/>',
            $id,
            $class,
            $lang,
            $label,
            $title,
            $role
        );

        $html5Element = new FakeHtml5Element($title, Role::getConstantByName($role), $id, $class, $lang, $label);

        $marshaller = new FakeHtml5ElementMarshaller('2.2.0');

        $this->assertMarshalling($expected, $html5Element, $marshaller);
    }

    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshall22WithDefaultValues(): void
    {
        $expected = '<html5/>';

        $html5Element = new FakeHtml5Element();

        $marshaller = new FakeHtml5ElementMarshaller('2.2.0');

        $this->assertMarshalling($expected, $html5Element, $marshaller);
    }

    /**
     * @throws MarshallerNotFoundException
     */
    public function testUnmarshall22(): void
    {
        $title = 'the title';
        $role = 'note';
        $id = 'Identifier';
        $class = 'a css class';
        $lang = 'es';
        $label = 'A label';

        $xml = sprintf(
            '<html5 id="%s" class="%s" xml:lang="%s" label="%s" title="%s" role="%s"/>',
            $id,
            $class,
            $lang,
            $label,
            $title,
            $role
        );

        $marshaller = new FakeHtml5ElementMarshaller('2.2.0');

        $expected = new FakeHtml5Element(
            $title,
            Role::getConstantByName($role),
            $id,
            $class,
            $lang,
            $label
        );
        $this->assertUnmarshalling($expected, $xml, $marshaller);
    }

    /**
     * @throws MarshallerNotFoundException
     */
    public function testUnmarshall22WithDefaultValues(): void
    {
        $xml = '<html5/>';

        $marshaller = new FakeHtml5ElementMarshaller('2.2.0');

        $expected = new FakeHtml5Element();
        $this->assertUnmarshalling($expected, $xml, $marshaller);
    }

    /**
     * @param Html5Element $object
     * @param string $elementName
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function assertHtml5MarshallingOnlyInQti22AndAbove(Html5Element $object, string $elementName): void
    {
        $this->expectException(MarshallerNotFoundException::class);
        $this->expectExceptionMessage('No mapping entry found for QTI class name \'' . $elementName . '\'.');
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($object);
        $marshaller->marshall($object);
    }

    /**
     * @param string $xml
     * @param string $elementName
     * @throws MarshallerNotFoundException
     */
    public function assertHtml5UnmarshallingOnlyInQti22AndAbove(string $xml, string $elementName): void
    {
        $element = $this->createDOMElement($xml);
        $this->expectException(MarshallerNotFoundException::class);
        $this->expectExceptionMessage('No mapping entry found for QTI class name \'' . $elementName . '\'.');
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $marshaller->unmarshall($element);
    }

    /**
     * @param string $expected
     * @param Html5Element $object
     * @param Marshaller|null $marshaller Optional marshaller to use for marshalling he object.
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function assertMarshalling(string $expected, Html5Element $object, Marshaller $marshaller = null): void
    {
        if ($marshaller === null) {
            $marshaller = $this->getMarshallerFactory('2.2.0')->createMarshaller($object);
        }
        $element = $marshaller->marshall($object);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals($expected, $dom->saveXML($element));
    }

    /**
     * @param Html5Element $expected
     * @param string $xml
     * @param Marshaller|null $marshaller Optional marshaller to use for marshalling he object.
     * @throws MarshallerNotFoundException
     */
    protected function assertUnmarshalling(Html5Element $expected, string $xml, Marshaller $marshaller = null): void
    {
        $element = $this->createDOMElement($xml);

        if ($marshaller === null) {
            $marshaller = $this->getMarshallerFactory('2.2.0')->createMarshaller($element);
        }

        $component = $marshaller->unmarshall($element);
        $this::assertEquals($expected, $component);
    }

    /**
     * @param string $xml
     * @param string $exception
     * @param string $message
     * @throws MarshallerNotFoundException
     */
    public function assertUnmarshallingException(string $xml, string $exception, string $message): void
    {
        $element = $this->createDOMElement($xml);
        $marshaller = $this->getMarshallerFactory('2.2.0')->createMarshaller($element);

        $this->expectException($exception);
        $this->expectExceptionMessage($message);

        $marshaller->unmarshall($element);
    }
}

class FakeHtml5Element extends Html5Element
{
    public function getQtiClassName(): string
    {
        return 'html5';
    }

    public function getComponents(): QtiComponentCollection
    {
        return new QtiComponentCollection();
    }
}

class FakeHtml5ElementMarshaller extends Html5ElementMarshaller
{
    /**
     * @throws MarshallingException
     * @throws UnmarshallingException
     */
    public function __call($method, $args)
    {
        if ($method === 'marshall') {
            return $this->marshall($args[0]);
        }
        if ($method === 'unmarshall') {
            return $this->unmarshall($args[0]);
        }
    }

    /**
     * @param DOMElement $element
     * @return QtiComponent
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element)
    {
        $component = new FakeHtml5Element();
        $this->fillBodyElement($component, $element);
        return $component;
    }

    public function getExpectedQtiClassName()
    {
    }
}
