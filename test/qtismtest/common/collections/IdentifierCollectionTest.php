<?php

namespace qtismtest\common\collections;

use InvalidArgumentException;
use qtism\common\collections\IdentifierCollection;
use qtismtest\QtiSmTestCase;

/**
 * Class IdentifierCollectionTest
 */
class IdentifierCollectionTest extends QtiSmTestCase
{
    /**
     * The IdentifierCollection object to test.
     *
     * @var IdentifierCollection
     */
    private $collection;

    public function setUp(): void
    {
        parent::setUp();
        $this->collection = new IdentifierCollection();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->collection);
    }

    public function testAddIdentifier()
    {
        $string = 'foobar';
        $this->collection[] = $string;
        $this->assertEquals(count($this->collection), 1);
        $this->assertEquals($this->collection[0], 'foobar');
    }

    /**
     * @depends testAddIdentifier
     */
    public function testRemoveIdentifier()
    {
        $string = 'foobar';
        $this->collection[] = $string;
        unset($this->collection[0]);
        $this->assertEquals(count($this->collection), 0);
    }

    /**
     * @depends testAddIdentifier
     */
    public function testModifyIdentifier()
    {
        $string = 'foobar';
        $this->collection[] = $string;
        $this->assertTrue(isset($this->collection[0]));
        $this->collection[0] = 'foo';
        $this->assertNotEquals($this->collection[0], $string);
    }

    public function testAddIdentifierWrongFormat()
    {
        $identifier = '.identifier';
        $this->expectException(InvalidArgumentException::class);
        $this->collection[] = $identifier;
    }

    public function testAddIdentifierWrongType()
    {
        $identifier = 999;
        $this->expectException(InvalidArgumentException::class);
        $this->collection[] = $identifier;
    }

    public function testToString()
    {
        $this->collection[] = 'one';
        $this->assertEquals('one', $this->collection->__toString());

        $this->collection[] = 'two';
        $this->collection[] = 'three';
        $this->assertEquals('one,two,three', $this->collection->__toString());
    }
}
