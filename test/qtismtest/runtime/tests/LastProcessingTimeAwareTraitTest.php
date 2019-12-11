<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Julien Sébire <julien@taotesting.com>
 * @license GPLv2
 */

namespace qtismtest\runtime\tests;

use DateTime;
use PHPUnit\Framework\TestCase;
use qtism\runtime\tests\LastProcessingTimeAwareInterface;
use qtism\runtime\tests\LastProcessingTimeAwareTrait;

class LastProcessingTimeAwareTraitTest extends TestCase implements LastProcessingTimeAwareInterface
{
    use LastProcessingTimeAwareTrait;
    
    public function test()
    {
        $this->updateLastProcessingTime();
        $lastProcessingTime = $this->getLastProcessingTime();
        $this->assertInstanceOf(DateTime::class, $lastProcessingTime);
    }
}
