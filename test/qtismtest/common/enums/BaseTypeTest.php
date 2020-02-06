<?php

namespace qtismtest\common\enums;

use qtism\common\enums\BaseType;
use qtismtest\QtiSmEnumTestCase;

class BaseTypeTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return BaseType::class;
    }

    protected function getNames()
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

    protected function getKeys()
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

    protected function getConstants()
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
