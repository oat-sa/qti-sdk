<?php

namespace qtismtest\data\content\xhtml;

use qtism\data\content\xhtml\html5\TrackKind;
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
        return [
            'subtitles',
            'captions',
            'descriptions',
            'chapters',
            'metadata',
        ];
    }

    protected function getConstants()
    {
        return [
            TrackKind::SUBTITLES,
            TrackKind::CAPTIONS,
            TrackKind::DESCRIPTIONS,
            TrackKind::CHAPTERS,
            TrackKind::METADATA,
        ];
    }
}
