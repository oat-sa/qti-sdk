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

use qtism\data\content\xhtml\html5\Ruby;
use qtismtest\QtiSmTestCase;

class RubyTest extends QtiSmTestCase
{
    public const SUBJECT_QTI_CLASS = 'ruby';

    public function testCreateWithValues(): void
    {
        $id = 'testid';
        $class = 'test_class';

        $subject = new Ruby(null, null, $id, $class);

        self::assertEquals($id, $subject->getId());
        self::assertEquals($class, $subject->getClass());
    }

    public function testCreateWithDefaultValues(): void
    {
        $subject = new Ruby();

        self::assertEquals('', $subject->getId());
        self::assertEquals('', $subject->getClass());
    }

    public function testGetQtiClassName(): void
    {
        $subject = new Ruby();

        self::assertEquals(self::SUBJECT_QTI_CLASS, $subject->getQtiClassName());
    }
}
