<?php

namespace qtismtest\data\content\enums;

use qtism\data\content\enums\CrossOrigin;
use qtismtest\QtiSmEnumTestCase;

class CrossOriginTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return CrossOrigin::class;
    }

    protected function getNames()
    {
        return [
            'anonymous',
            'use-credentials', 
        ];
    }

    protected function getKeys()
    {
        return $this->getNames();
    }

    protected function getConstants()
    {
        return array_map(
            [CrossOrigin::class, 'getConstantByName'],
            $this->getNames()
        );
    }
}
