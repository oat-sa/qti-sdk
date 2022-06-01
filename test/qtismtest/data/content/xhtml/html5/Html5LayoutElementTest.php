<?php

namespace qtismtest\data\content\xhtml\html5;

use qtism\data\content\enums\Role;
use qtism\data\content\FlowCollection;
use qtism\data\content\xhtml\A;
use qtism\data\content\xhtml\html5\Html5LayoutElement;
use qtism\data\content\xhtml\text\Br;
use qtism\data\content\xhtml\text\P;
use qtismtest\QtiSmTestCase;

class Html5LayoutElementTest extends QtiSmTestCase
{
    public function testCreateWithValues(): void
    {
        $title = 'a title';
        $role = 'article';
        $id = 'the_id';
        $class = 'css class';
        $lang = 'en';
        $label = 'This is the label.';

        $subject = new FakeHtml5LayoutElement($title, $role, $id, $class, $lang, $label);

        self::assertSame($title, $subject->getTitle());
        self::assertEquals(Role::getConstantByName($role), $subject->getRole());
        self::assertSame($id, $subject->getId());
        self::assertSame($class, $subject->getClass());
        self::assertSame($lang, $subject->getLang());
        self::assertSame($label, $subject->getLabel());
        self::assertEquals(new FlowCollection(), $subject->getComponents());
    }

    public function testCreateWithDefaultValues(): void
    {
        $subject = new FakeHtml5LayoutElement();

        self::assertSame('', $subject->getTitle());
        self::assertSame('', $subject->getId());
        self::assertSame('', $subject->getClass());
        self::assertSame('', $subject->getLang());
        self::assertSame('', $subject->getLabel());
        self::assertEquals(new FlowCollection(), $subject->getComponents());
    }

    public function testSetContent(): void
    {
        $subject = new FakeHtml5LayoutElement();
        $content = new FlowCollection(
            [
                new P(),
                new Br(),
                new A('blah'),
            ]
        );

        $subject->setContent($content);

        self::assertEquals($content, $subject->getContent());
        self::assertEquals($content, $subject->getComponents());
    }
}

class FakeHtml5LayoutElement extends Html5LayoutElement
{
    public function getQtiClassName(): string
    {
        return '';
    }
}