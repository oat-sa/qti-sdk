<?php

namespace qtismtest\data;

use qtism\common\enums\BaseType;
use qtismtest\QtiSmTestCase;

class BaseTypeTest extends QtiSmTestCase
{
    /**
     * @dataProvider validBaseTypeProvider
     */
    public function testGetConstantByNameValidBaseType($baseType)
    {
        $this->assertInternalType('integer', BaseType::getConstantByName($baseType));
    }

    /**
     * @dataProvider invalidBaseTypeProvider
     */
    public function testGetConstantByNameInvalidBaseType($baseType)
    {
        $this->assertFalse(BaseType::getConstantByName($baseType));
    }

    /**
     * @dataProvider validBaseTypeConstantProvider
     */
    public function testGetNameByConstantValidBaseType($constant, $expected)
    {
        $this->assertEquals($expected, BaseType::getNameByConstant($constant));
    }

    /**
     * @dataProvider invalidBaseTypeConstantProvider
     */
    public function testGetNameByConstantInvalidBaseType($constant)
    {
        $this->assertFalse(BaseType::getNameByConstant($constant));
    }

    public function validBaseTypeConstantProvider()
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

    public function invalidBaseTypeConstantProvider()
    {
        return [
            [-1],
        ];
    }

    public function validBaseTypeProvider()
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

    public function invalidBaseTypeProvider()
    {
        return [
            [10],
            ['unknown'],
            ['int_or_identifier'],
        ];
    }
}
