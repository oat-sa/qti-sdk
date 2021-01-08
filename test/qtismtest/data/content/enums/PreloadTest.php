<?php

namespace qtismtest\data\content\enums;

use qtism\data\content\enums\Preload;
use qtismtest\QtiSmEnumTestCase;

class PreloadTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return Preload::class;
    }

    protected function getNames()
    {
        return [
            'none',
            'auto',
            'metadata',
        ];
    }

    protected function getKeys()
    {
        return $this->getNames();
    }

    protected function getConstants()
    {
        return array_map(
            [Preload::class, 'getConstantByName'],
            $this->getNames()
        );
    }
}
