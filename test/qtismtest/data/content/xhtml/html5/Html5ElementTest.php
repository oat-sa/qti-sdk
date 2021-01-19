<?php

namespace qtismtest\data\content\xhtml\html5;

use InvalidArgumentException;
use qtism\data\content\xhtml\html5\Html5Element;
use qtism\data\content\enums\Role;
use qtism\data\QtiComponentCollection;
use qtismtest\QtiSmTestCase;

class Html5ElementTest extends QtiSmTestCase
{
    public function testCreateWithoutTitle(): void
    {
        $subject = new fakeHtml5Element();

        self::assertFalse($subject->hasTitle());
    }

    public function testCreateWithValidTitle(): void
    {
        $title = 'a title';
            
        $subject = new fakeHtml5Element($title);
        self::assertEquals($title, $subject->getTitle());
    }

    public function testCreateWithInvalidTitle(): void
    {
        $wrongTitle = "a title with\tabulations and li\ne break.";

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "title" argument must be a non-empty, normalized string (no line break nor tabulation), "' . $wrongTitle . '" given.');

        (new fakeHtml5Element($wrongTitle));
    }

    public function testCreateWithNullTitle(): void
    {
        $subject = new fakeHtml5Element();
        $this::assertSame('', $subject->getTitle());
    }

    public function testCreateWithoutRole(): void
    {
        $subject = new fakeHtml5Element();
        self::assertFalse($subject->hasRole());
    }

    /**
     * @dataProvider rolesToTest
     * @param int $role
     */
    public function testCreateWithValidRole(int $role): void
    {
        $subject = new fakeHtml5Element();

        $subject->setRole($role);
        self::assertEquals($role, $subject->getRole());
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

    public function testCreateWithNonIntegerRole(): void
    {
        $wrongRole = 'foo';
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "role" argument must be a value from the Role enumeration, "' . $wrongRole . '" given.');

        (new FakeHtml5Element())->setRole($wrongRole);
    }

    public function testCreateWithInvalidRole(): void
    {
        $wrongRole = 1012;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "role" argument must be a value from the Role enumeration, "' . $wrongRole . '" given.');

        (new FakeHtml5Element())->setRole($wrongRole);
    }
}

class FakeHtml5Element extends Html5Element
{
    public function getQtiClassName(): string
    {
        return '';
    }

    public function getComponents(): QtiComponentCollection
    {
        return new QtiComponentCollection();
    }
}
