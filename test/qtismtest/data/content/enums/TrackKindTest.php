<?php

namespace qtismtest\data\content\enums;

use qtism\data\content\enums\TrackKind;
use qtismtest\QtiSmEnumTestCase;

class TrackKindTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return TrackKind::class;
    }

    protected function getNames()
    {
        return [
            'subtitles',
            'captions', 
            'descriptions',
            'chapters',
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
            [TrackKind::class, 'getConstantByName'],
            $this->getNames()
        );
    }
}
