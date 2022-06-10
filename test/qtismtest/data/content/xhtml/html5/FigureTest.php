<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace qtismtest\data\content\xhtml\html5;

use qtism\data\content\enums\Role;
use qtism\data\content\xhtml\html5\Figure;
use qtismtest\QtiSmTestCase;

class FigureTest extends QtiSmTestCase
{
    public function testCreateWithValues(): void
    {
        $title = 'title';
        $role = 'article';
        $id = 'testid';
        $class = 'test_class';
        $lang = 'lang';
        $label = 'label';

        $subject = new Figure($title, $role, $id, $class, $lang, $label);

        self::assertEquals($title, $subject->getTitle());
        self::assertEquals(Role::getConstantByName($role), $subject->getRole());
        self::assertEquals($id, $subject->getId());
        self::assertEquals($class, $subject->getClass());
        self::assertEquals($lang, $subject->getLang());
        self::assertEquals($label, $subject->getLabel());
    }

    public function testCreateWithDefaultValues(): void
    {
        $subject = new Figure();

        self::assertEquals('', $subject->getId());
        self::assertEquals('', $subject->getClass());
    }

    public function testGetQtiClassName(): void
    {
        $subject = new Figure();

        self::assertEquals(Figure::QTI_CLASS_NAME_FIGURE, $subject->getQtiClassName());
    }
}
