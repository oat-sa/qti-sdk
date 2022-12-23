<?php

namespace qtismtest\common\collections;

use InvalidArgumentException;
use qtism\common\collections\StringCollection;
use qtismtest\QtiSmTestCase;

/**
 * Class StringCollectionTest
 */
class StringCollectionTest extends QtiSmTestCase
{
    /**
     * The StringCollection object to test.
     *
     * @var StringCollection
     */
    private $collection;

    public function setUp(): void
    {
        parent::setUp();
        $this->collection = new StringCollection();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->collection);
    }

    public function testAddString(): void
    {
        $string = 'foobar';
        $this->collection[] = $string;
        $this::assertCount(1, $this->collection);
        $this::assertEquals('foobar', $this->collection[0]);
    }

    /**
     * @depends testAddString
     */
    public function testRemoveString(): void
    {
        $string = 'foobar';
        $this->collection[] = $string;
        unset($this->collection[0]);
        $this::assertCount(0, $this->collection);
    }

    /**
     * @depends testAddString
     */
    public function testModifyString(): void
    {
        $string = 'foobar';
        $this->collection[] = $string;
        $this::assertTrue(isset($this->collection[0]));
        $this->collection[0] = 'foo';
        $this::assertNotEquals($this->collection[0], $string);
    }

    public function testAddStringWrongType(): void
    {
        $int = 1;
        $this->expectException(InvalidArgumentException::class);
        $this->collection[] = $int;
    }

    public function testForeachable(): void
    {
        $a = ['string1', 'string2', 'string3'];
        foreach ($a as $s) {
            $this->collection[] = $s;
        }

        reset($a);

        foreach ($this->collection as $s) {
            $c = current($a);
            $this::assertEquals($c, $s);
            next($a);
        }

        // Break in a foreach and check...
        $i = 0;
        foreach ($this->collection as $s) {
            if ($i === 1) {
                break;
            }

            $i++;
        }

        $this::assertEquals('string2', $this->collection->current());

        // Check if we iterate from the beginning in a new foreach.
        $i = 0;
        foreach ($this->collection as $s) {
            $i++;
        }
        $this::assertEquals(3, $i);
    }

    public function testAttachNotObject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("You can only attach 'objects' into an AbstractCollection, 'string' given");
        $this->collection->attach('string');
    }

    public function testResetKeys(): void
    {
        $this->collection[] = 'string1';
        $this->collection[] = 'string2';
        $this->collection[] = 'string3';

        unset($this->collection[1]);

        $this::assertEquals([0, 2], $this->collection->getKeys());

        $this->collection->resetKeys();

        $this::assertEquals([0, 1], $this->collection->getKeys());
    }
}
