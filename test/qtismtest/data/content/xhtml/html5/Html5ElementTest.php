<?php

namespace qtismtest\data\content\xhtml\html5;

use InvalidArgumentException;
use qtism\data\content\xhtml\html5\Html5Element;
use qtism\data\content\enums\Role;
use qtismtest\QtiSmTestCase;
use TypeError;

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
            [Role::getConstantByName('article')],
            [Role::getConstantByName('button')],
            [Role::getConstantByName('checkbox')],
            [Role::getConstantByName('columnheader')],
            [Role::getConstantByName('complementary')],
            [Role::getConstantByName('contentinfo')],
            [Role::getConstantByName('definition')],
            [Role::getConstantByName('directory')],
            [Role::getConstantByName('document')],
            [Role::getConstantByName('gridcell')],
            [Role::getConstantByName('group')],
            [Role::getConstantByName('heading')],
            [Role::getConstantByName('img')],
            [Role::getConstantByName('link')],
            [Role::getConstantByName('list')],
            [Role::getConstantByName('listbox')],
            [Role::getConstantByName('listitem')],
            [Role::getConstantByName('log')],
            [Role::getConstantByName('math')],
            [Role::getConstantByName('note')],
            [Role::getConstantByName('option')],
            [Role::getConstantByName('presentation')],
            [Role::getConstantByName('radio')],
            [Role::getConstantByName('radiogroup')],
            [Role::getConstantByName('region')],
            [Role::getConstantByName('row')],
            [Role::getConstantByName('rowgroup')],
            [Role::getConstantByName('rowheader')],
            [Role::getConstantByName('separator')],
            [Role::getConstantByName('slider')],
            [Role::getConstantByName('spinbutton')],
            [Role::getConstantByName('status')],
            [Role::getConstantByName('tab')],
            [Role::getConstantByName('tablist')],
            [Role::getConstantByName('tabpanel')],
            [Role::getConstantByName('textbox')],
            [Role::getConstantByName('timer')],
            [Role::getConstantByName('toolbar')],
        ];
    }

    public function testCreateWithNonIntegerRole()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "role" argument must be a value from the Role enumeration, "foo" given.');

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
