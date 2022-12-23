<?php

namespace qtismtest\data;

use qtism\common\enums\BaseType;
use qtismtest\QtiSmTestCase;

/**
 * Class BaseTypeTest
 */
class BaseTypeTest extends QtiSmTestCase
{
    /**
     * @dataProvider validBaseTypeProvider
     * @param string $baseType
     */
    public function testGetConstantByNameValidBaseType($baseType): void
    {
        $this::assertIsInt(BaseType::getConstantByName($baseType));
    }

    /**
     * @dataProvider invalidBaseTypeProvider
     * @param string $baseType
     */
    public function testGetConstantByNameInvalidBaseType($baseType): void
    {
        $this::assertFalse(BaseType::getConstantByName($baseType));
    }

    /**
     * @dataProvider validBaseTypeConstantProvider
     * @param int $constant
     * @param string $expected
     */
    public function testGetNameByConstantValidBaseType($constant, $expected): void
    {
        $this::assertEquals($expected, BaseType::getNameByConstant($constant));
    }

    /**
     * @dataProvider invalidBaseTypeConstantProvider
     * @param int $constant
     */
    public function testGetNameByConstantInvalidBaseType($constant): void
    {
        $this::assertFalse(BaseType::getNameByConstant($constant));
    }

    /**
     * @return array
     */
    public function validBaseTypeConstantProvider(): array
    {
        return [
            [BaseType::IDENTIFIER, 'identifier'],
            [BaseType::BOOLEAN, 'boolean'],
            [BaseType::INTEGER, 'integer'],
            [BaseType::STRING, 'string'],
            [BaseType::FLOAT, 'float'],
            [BaseType::POINT, 'point'],
            [BaseType::PAIR, 'pair'],
            [BaseType::DIRECTED_PAIR, 'directedPair'],
            [BaseType::DURATION, 'duration'],
            [BaseType::FILE, 'file'],
            [BaseType::URI, 'uri'],
            [BaseType::INT_OR_IDENTIFIER, 'intOrIdentifier'],
        ];
    }

    /**
     * @return array
     */
    public function invalidBaseTypeConstantProvider(): array
    {
        return [
            [-1],
        ];
    }

    /**
     * @return array
     */
    public function validBaseTypeProvider(): array
    {
        return [
            ['identifier'],
            ['boolean'],
            ['integer'],
            ['string'],
            ['float'],
            ['point'],
            ['pair'],
            ['directedPair'],
            ['duratioN'], // case insensitive function
            ['file'],
            ['uri'],
            ['intOrIdentifier'],
        ];
    }

    /**
     * @return array
     */
    public function invalidBaseTypeProvider(): array
    {
        return [
            [10],
            ['unknown'],
            ['int_or_identifier'],
        ];
    }
}
