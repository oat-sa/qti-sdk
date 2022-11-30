<?php

declare(strict_types=1);

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

    public function testConsistency(): void
    {
        $refCount = count($this->getNames());

        $this::assertCount($refCount, $this->getConstants());
        $this::assertCount($refCount, $this->getKeys());
    }

    public function testAsArray(): void
    {
        $enumerationName = $this->getEnumerationFqcn();
        $array = $enumerationName::asArray();
        $keys = $this->getKeys();
        $constants = $this->getConstants();

        for ($i = 0; $i < count($keys); $i++) {
            $key = $keys[$i];
            $this::assertTrue(isset($array[$key]));
            $this::assertEquals($constants[$i], $array[$key]);
        }
    }

    public function testGetConstantByName(): void
    {
        $names = $this->getNames();
        $constants = $this->getConstants();
        $enumerationName = $this->getEnumerationFqcn();

        for ($i = 0; $i < count($names); $i++) {
            $name = $names[$i];
            $this::assertEquals(
                $constants[$i],
                $enumerationName::getConstantByName($name)
            );
        }

        $this::assertFalse($enumerationName::getConstantByName($this->getUnknownConstantName()));
    }

    public function testGetNameByConstant(): void
    {
        $names = $this->getNames();
        $constants = $this->getConstants();
        $enumerationName = $this->getEnumerationFqcn();

        for ($i = 0; $i < count($constants); $i++) {
            $constant = $constants[$i];
            $this::assertEquals(
                $names[$i],
                $enumerationName::getNameByConstant($constant)
            );
        }

        $this::assertFalse($enumerationName::getNameByConstant($this->getUnknownConstantValue()));
    }

    /**
     * @return string
     */
    protected function getUnknownConstantName(): string
    {
        return 'xyz';
    }

    /**
     * @return int
     */
    protected function getUnknownConstantValue(): int
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
