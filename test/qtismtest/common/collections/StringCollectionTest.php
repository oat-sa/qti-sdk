<?php

namespace qtismtest\common\collections;

use qtism\common\collections\StringCollection;
use qtismtest\QtiSmTestCase;

class StringCollectionTest extends QtiSmTestCase
{
    /**
     * The StringCollection object to test.
     *
     * @var StringCollection
     */
    private $collection;

    public function setUp()
    {
        parent::setUp();
        $this->collection = new StringCollection();
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->collection);
    }

    public function testAddString()
    {
        $string = 'foobar';
        $this->collection[] = $string;
        $this->assertEquals(count($this->collection), 1);
        $this->assertEquals($this->collection[0], 'foobar');
    }

    /**
     * @depends testAddString
     */
    public function testRemoveString()
    {
        $string = 'foobar';
        $this->collection[] = $string;
        unset($this->collection[0]);
        $this->assertEquals(count($this->collection), 0);
    }

    /**
     * @depends testAddString
     */
    public function testModifyString()
    {
        $string = 'foobar';
        $this->collection[] = $string;
        $this->assertTrue(isset($this->collection[0]));
        $this->collection[0] = 'foo';
        $this->assertNotEquals($this->collection[0], $string);
    }

    public function testAddStringWrongType()
    {
        $int = 1;
        $this->setExpectedException(\InvalidArgumentException::class);
        $this->collection[] = $int;
    }

    public function testForeachable()
    {
        $a = ['string1', 'string2', 'string3'];
        foreach ($a as $s) {
            $this->collection[] = $s;
        }

        reset($a);

        foreach ($this->collection as $s) {
            $c = current($a);
            $this->assertEquals($c, $s);
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

        $this->assertEquals('string2', $this->collection->current());

        // Check if we iterate from the beginning in a new foreach.
        $i = 0;
        foreach ($this->collection as $s) {
            $i++;
        }
        $this->assertEquals(3, $i);
    }

    public function testAttachNotObject()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "You can only attach 'objects' into an AbstractCollection, 'string' given"
        );
        $this->collection->attach('string');
    }

    public function testResetKeys()
    {
        $this->collection[] = "string1";
        $this->collection[] = "string2";
        $this->collection[] = "string3";

        unset($this->collection[1]);

        $this->assertEquals($this->collection->getKeys(), [0, 2]);

        $this->collection->resetKeys();

        $this->assertEquals($this->collection->getKeys(), [0, 1]);
    }
}
