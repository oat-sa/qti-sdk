<?php

namespace qtismtest\data\content\xhtml;

use qtism\data\content\xhtml\ParamType;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class ParamTypeTest
 *
 * @package qtismtest\data\content\xhtml
 */
class ParamTypeTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn()
    {
        return ParamType::class;
    }

    /**
     * @return array
     */
    protected function getNames()
    {
        return [
            'DATA',
            'REF',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys()
    {
        return [
            'DATA',
            'REF',
        ];
    }

    /**
     * @return array
     */
    protected function getConstants()
    {
        return [
            ParamType::DATA,
            ParamType::REF,
        ];
    }
}
