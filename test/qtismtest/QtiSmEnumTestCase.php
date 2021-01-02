<?php

namespace qtismtest;

/**
 * Class QtiSmEnumTestCase
 */
abstract class QtiSmEnumTestCase extends QtiSmTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testConsistency()
    {
        $refCount = count($this->getNames());

        $this->assertEquals($refCount, count($this->getConstants()));
        $this->assertEquals($refCount, count($this->getKeys()));
    }

    public function testAsArray()
    {
        $keys = $this->getKeys();
        $constants = $this->getConstants();
        $className = $this->getEnumerationFqcn();
        $array = $className::asArray();

        for ($i = 0; $i < count($keys); $i++) {
            $key = $keys[$i];
            $this->assertTrue(isset($array[$key]));
            $this->assertEquals($constants[$i], $array[$key]);
        }
    }

    public function testGetConstantByName()
    {
        $names = $this->getNames();
        $constants = $this->getConstants();
        $className = $this->getEnumerationFqcn();

        for ($i = 0; $i < count($names); $i++) {
            $name = $names[$i];
            $this->assertEquals(
                $constants[$i],
                $className::getConstantByName($name)
            );
        }

        $this->assertFalse($className::getConstantByName($this->getUnknownConstantName()));
    }

    public function testGetNameByConstant()
    {
        $names = $this->getNames();
        $constants = $this->getConstants();
        $className = $this->getEnumerationFqcn();

        for ($i = 0; $i < count($constants); $i++) {
            $constant = $constants[$i];
            $this->assertEquals(
                $names[$i],
                $className::getNameByConstant($constant)
            );
        }

        $this->assertFalse($className::getNameByConstant($this->getUnknownConstantValue()));
    }

    /**
     * @return string
     */
    protected function getUnknownConstantName()
    {
        return 'xyz';
    }

    /**
     * @return int
     */
    protected function getUnknownConstantValue()
    {
        return PHP_INT_MAX;
    }

    /**
     * @return mixed
     */
    abstract protected function getNames();

    /**
     * @return mixed
     */
    abstract protected function getKeys();

    /**
     * @return mixed
     */
    abstract protected function getConstants();

    /**
     * @return mixed
     */
    abstract protected function getEnumerationFqcn();
}
