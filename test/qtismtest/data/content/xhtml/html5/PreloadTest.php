<?php

namespace qtismtest\data\content\xhtml;

use qtism\data\content\xhtml\html5\Preload;
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
        return [
            'none',
            'auto',
            'metadata',
        ];
    }

    protected function getConstants()
    {
        return [
            Preload::NONE,
            Preload::AUTO,
            Preload::METADATA,
        ];
    }
}
