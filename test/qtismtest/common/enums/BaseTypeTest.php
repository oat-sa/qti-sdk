<?php

namespace qtismtest\common\enums;

use qtism\common\enums\BaseType;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class BaseTypeTest
 */
class BaseTypeTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn(): string
    {
        return BaseType::class;
    }

    /**
     * @return array
     */
    protected function getNames(): array
    {
        return [
            'identifier',
            'boolean',
            'integer',
            'float',
            'string',
            'point',
            'pair',
            'directedPair',
            'duration',
            'file',
            'uri',
            'intOrIdentifier',
            'coords',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys(): array
    {
        return [
            'IDENTIFIER',
            'BOOLEAN',
            'INTEGER',
            'FLOAT',
            'STRING',
            'POINT',
            'PAIR',
            'DIRECTED_PAIR',
            'DURATION',
            'FILE',
            'URI',
            'INT_OR_IDENTIFIER',
            'COORDS',
        ];
    }

    /**
     * @return array
     */
    protected function getConstants(): array
    {
        return [
            BaseType::IDENTIFIER,
            BaseType::BOOLEAN,
            BaseType::INTEGER,
            BaseType::FLOAT,
            BaseType::STRING,
            BaseType::POINT,
            BaseType::PAIR,
            BaseType::DIRECTED_PAIR,
            BaseType::DURATION,
            BaseType::FILE,
            BaseType::URI,
            BaseType::INT_OR_IDENTIFIER,
            BaseType::COORDS,
        ];
    }
}
