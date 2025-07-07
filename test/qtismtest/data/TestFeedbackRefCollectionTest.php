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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace qtismtest\data;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use qtism\data\TestFeedbackRef;
use qtism\data\TestFeedbackRefCollection;

class TestFeedbackRefCollectionTest extends TestCase
{
    public function testAddValidTestFeedbackRef()
    {
        $collection = new TestFeedbackRefCollection();
        $testFeedbackRef = $this->createMock(TestFeedbackRef::class);

        $collection->attach($testFeedbackRef);
        $this->assertTrue($collection->contains($testFeedbackRef));
    }

    public function testAddInvalidTypeThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A TestFeedbackRefCollection object only accepts to store TestFeedbackRef objects.');

        $collection = new TestFeedbackRefCollection();
        $invalidObject = new \stdClass();

        $collection->attach($invalidObject);
    }

    public function testRemoveTestFeedbackRef()
    {
        $collection = new TestFeedbackRefCollection();
        $testFeedbackRef = $this->createMock(TestFeedbackRef::class);

        $collection->attach($testFeedbackRef);
        $this->assertTrue($collection->contains($testFeedbackRef));

        $collection->detach($testFeedbackRef);
        $this->assertFalse($collection->contains($testFeedbackRef));
    }
}
