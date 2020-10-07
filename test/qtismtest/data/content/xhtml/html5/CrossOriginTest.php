<?php

namespace qtismtest\data\content\xhtml;

use qtism\data\content\xhtml\html5\CrossOrigin;
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
        return [
            'anonymous',
            'use-credentials',
        ];
    }

    protected function getConstants()
    {
        return [
            CrossOrigin::ANONYMOUS,
            CrossOrigin::USE_CREDENTIALS,
        ];
    }
}
