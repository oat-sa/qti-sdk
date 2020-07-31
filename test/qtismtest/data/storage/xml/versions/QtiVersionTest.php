<?php

namespace qtismtest\data\storage\xml\versions;

use InvalidArgumentException;
use qtism\data\storage\xml\versions\QtiVersion;
use qtismtest\QtiSmTestCase;

class QtiVersionTest extends QtiSmTestCase
{
    public function testVersionCompareValid()
    {
        $this->assertFalse(QtiVersion::compare('2', '2.1', '='));
    }

    public function testVersionCompareInvalidVersion1()
    {
        $msg = 'QTI version "2.1.4" is not supported. Supported versions are "2.0.0", "2.1.0", "2.1.1", "2.2.0", "2.2.1", "2.2.2", "3.0.0".';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($msg);
        QtiVersion::compare('2.1.4', '2.1.1', '>');
    }

    public function testVersionCompareInvalidVersion2()
    {
        $msg = 'QTI version "2.1.4" is not supported. Supported versions are "2.0.0", "2.1.0", "2.1.1", "2.2.0", "2.2.1", "2.2.2", "3.0.0".';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($msg);
        QtiVersion::compare('2.1.0', '2.1.4', '<');
    }
}
