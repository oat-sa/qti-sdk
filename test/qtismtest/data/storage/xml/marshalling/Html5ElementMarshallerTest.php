<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use PHPUnit\Exception;
use qtism\data\content\enums\Role;
use qtism\data\content\FlowCollection;
use qtism\data\content\InlineCollection;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\html5\Figure;
use qtism\data\content\xhtml\html5\Html5Element;
use qtism\data\content\xhtml\html5\Rb;
use qtism\data\content\xhtml\html5\Rp;
use qtism\data\content\xhtml\html5\Rt;
use qtism\data\content\xhtml\html5\Ruby;
use qtism\data\storage\xml\marshalling\Html5ContentMarshaller;
use qtism\data\storage\xml\marshalling\Marshaller;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\data\storage\xml\marshalling\MarshallingException;
use qtismtest\QtiSmTestCase;

class Html5ElementMarshallerTest extends QtiSmTestCase
{
    private const HTML5_PREFIX = 'qh5';

    private const HTML5_NAMESPACE = 'http://www.imsglobal.org/xsd/imsqtiv2p2_html5_v1p0';

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
            '<%s id="%s" class="%s" xml:lang="%s" label="%s" title="%s" role="%s"/>',
            $this->namespaceTag(Figure::QTI_CLASS_NAME_FIGURE),
            $id,
            $class,
            $lang,
            $label,
            $title,
            $role
        );

        $html5Element = new Figure($title, Role::getConstantByName($role), $id, $class, $lang, $label);

        $this->assertMarshalling($expected, $html5Element);
    }

    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshall22WithDefaultValues(): void
    {
        $expected = '<' . $this->namespaceTag(Figure::QTI_CLASS_NAME_FIGURE) . '/>';

        $html5Element = new Figure();

        $this->assertMarshalling($expected, $html5Element);
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
            '<%s id="%s" class="%s" xml:lang="%s" label="%s" title="%s" role="%s"/>',
            $this->namespaceTag(Figure::QTI_CLASS_NAME_FIGURE),
            $id,
            $class,
            $lang,
            $label,
            $title,
            $role
        );

        $expected = new Figure($title, Role::getConstantByName($role), $id, $class, $lang, $label);

        $this->assertUnmarshalling($expected, $xml);
    }

    /**
     * @throws MarshallerNotFoundException
     */
    public function testUnmarshall22WithDefaultValues(): void
    {
        $xml = '<' . $this->namespaceTag(Figure::QTI_CLASS_NAME_FIGURE) . '/>';

        $expected = new Figure();

        $this->assertUnmarshalling($expected, $xml);
    }

    public function testRubyMarshaller()
    {
        $id = 'id';
        $class = 'testclass';

        $expected = sprintf(
            '<%1$s id="%2$s" class="%3$s"><%4$s>真</%4$s><%5$s>まこと</%5$s><%6$s>真</%6$s></%7$s>',
            $this->namespaceTag(Ruby::QTI_CLASS_NAME),
            $id,
            $class,
            $this->prefixTag(Rt::QTI_CLASS_NAME),
            $this->prefixTag(Rb::QTI_CLASS_NAME),
            $this->prefixTag(Rp::QTI_CLASS_NAME),
            $this->prefixTag(Ruby::QTI_CLASS_NAME)
        );

        $rb = new Rb();
        $rb->setContent(new FlowCollection([new TextRun('まこと')]));

        $rt = new Rt();
        $rt->setContent(new FlowCollection([new TextRun('真')]));

        $rp = new Rp();
        $rp->setContent(new FlowCollection([new TextRun('真')]));

        $object = new Ruby(null, null, $id, $class);
        $object->setContent(new FlowCollection([ $rt, $rb, $rp]));

        $this->assertMarshalling($expected, $object);
    }
    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testRubyMarshall22WithDefaultValues(): void
    {
        $expected = sprintf(
            '<%s/>',
            $this->namespaceTag(Ruby::QTI_CLASS_NAME)
        );

        $ruby = new Ruby();

        $this->assertMarshalling($expected, $ruby);
    }

    /**
     * @throws MarshallerNotFoundException
     */
    public function testRubyUnMarshallerDoesNotExistInQti21(): void
    {
        $this->assertHtml5UnmarshallingOnlyInQti22AndAbove(
            sprintf(
                '<%s></%s>',
                $this->namespaceTag(Ruby::QTI_CLASS_NAME),
                $this->prefixTag(Ruby::QTI_CLASS_NAME)
            ),
            Ruby::QTI_CLASS_NAME
        );
    }

    /**
     * @throws MarshallerNotFoundException
     */
    public function testRubyUnmarshall22(): void
    {
        $id = 'id';
        $class = 'testclass';

        $xml = sprintf(
            '<%1$s id="%2$s" class="%3$s"></%4$s>',
            $this->namespaceTag(Ruby::QTI_CLASS_NAME),
            $id,
            $class,
            $this->prefixTag(Ruby::QTI_CLASS_NAME)
        );

        $expected = new Ruby(null, null, $id, $class);

        $this->assertUnmarshalling($expected, $xml);
    }

    public function testRubyUnmarshall22WithDefaultValues(): void
    {
        $xml = sprintf(
            '<%s></%s>',
            $this->namespaceTag(Ruby::QTI_CLASS_NAME),
            $this->prefixTag(Ruby::QTI_CLASS_NAME)
        );

        $expected = new Ruby();

        $this->assertUnmarshalling($expected, $xml);
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
        $this->expectExceptionMessage(sprintf(
            "No marshaller implementation could be found for component '%s'.",
            $elementName
        ));
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

    protected function namespaceTag(string $tagName): string
    {
        return $this->prefixTag($tagName) . ' xmlns:' . self::HTML5_PREFIX . '="' . self::HTML5_NAMESPACE . '"';
    }

    protected function prefixTag(string $tagName): string
    {
        return self::HTML5_PREFIX . ':' . $tagName;
    }
}
