<?php

namespace qtismtest\data\content\xhtml\html5;

use InvalidArgumentException;
use qtism\data\content\xhtml\html5\Html5Element;
use qtism\data\content\xhtml\html5\Role;
use qtismtest\QtiSmTestCase;

class Html5ElementTest extends QtiSmTestCase
{
    public function testCreateWithoutTitle()
    {
        $subject = new fakeHtml5Element();

        $this->assertFalse($subject->hasTitle());
    }

    public function testCreateWithValidTitle()
    {
        $title = 'a title';
            
        $subject = new fakeHtml5Element();

        $subject->setTitle($title);
        $this->assertEquals($title, $subject->getTitle());
    }

    public function testCreateWithNonStringTitle()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "title" argument must be a string, "integer" given.');

        (new FakeHtml5Element())->setTitle(12);
    }

    public function testCreateWithoutRole()
    {
        $subject = new fakeHtml5Element();

        $this->assertFalse($subject->hasRole());
    }

    /**
     * @dataProvider rolesToTest
     * @param int $role
     */
    public function testCreateWithValidRole(int $role)
    {
        $subject = new fakeHtml5Element();

        $subject->setRole($role);
        $this->assertEquals($role, $subject->getRole());
    }

    public function rolesToTest(): array
    {
        return [
            [Role::ARTICLE],
            [Role::BUTTON],
            [Role::CHECKBOX],
            [Role::COLUMN_HEADER],
            [Role::COMPLEMENTARY],
            [Role::CONTENT_INFO],
            [Role::DEFINITION],
            [Role::DIRECTORY],
            [Role::DOCUMENT],
            [Role::GRID_CELL],
            [Role::GROUP],
            [Role::HEADING],
            [Role::IMG],
            [Role::LINK],
            [Role::LIST],
            [Role::LIST_BOX],
            [Role::LIST_ITEM],
            [Role::LOG],
            [Role::MATH],
            [Role::NOTE],
            [Role::OPTION],
            [Role::PRESENTATION],
            [Role::RADIO],
            [Role::RADIO_GROUP],
            [Role::REGION],
            [Role::ROW],
            [Role::ROW_GROUP],
            [Role::ROW_HEADER],
            [Role::SEPARATOR],
            [Role::SLIDER],
            [Role::SPIN_BUTTON],
            [Role::STATUS],
            [Role::TAB],
            [Role::TAB_LIST],
            [Role::TAB_PANEL],
            [Role::TEXT_BOX],
            [Role::TIMER],
            [Role::TOOLBAR],
        ];
    }

    public function testCreateWithNonIntegerRole()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "role" argument must be a value from the Role enumeration, "string" given.');

        (new FakeHtml5Element())->setRole('foo');
    }

    public function testCreateWithInvalidRole()
    {
        $wrongRole = 1012;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "role" argument must be a value from the Role enumeration, "' . $wrongRole . '" given.');

        (new FakeHtml5Element())->setRole(1012);
    }
}

class FakeHtml5Element extends Html5Element
{
    public function getQtiClassName()
    {
    }

    public function getComponents()
    {
    }
}
