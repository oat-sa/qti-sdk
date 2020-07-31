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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Julien Sébire <julien@taotesting.com>
 * @license GPLv2
 */

namespace qtism\common\utils\versions;

use DOMDocument;

/**
 * Specialized version for QTI Result Report.
 */
abstract class ResultVersion extends QtiVersion
{
    const SUPPORTED_VERSIONS = [
        '2.1.0' => ResultVersion21::class,
        '2.1.1' => ResultVersion21::class,
        '2.2.0' => ResultVersion22::class,
        '2.2.1' => ResultVersion22::class,
        '2.2.2' => ResultVersion22::class,
    ];

    const UNSUPPORTED_VERSION_MESSAGE = 'QTI Result Report is not supported for version "%s".';

    /**
     * @param string $rootNs
     * @param DOMDocument $document
     * @return string
     */
    public static function findVersionInDocument(string $rootNs, DOMDocument $document): string
    {
        $version = '';

        if ($rootNs === ResultVersion21::XMLNS) {
            $version = '2.1.0';
        } elseif ($rootNs === ResultVersion22::XMLNS) {
            $version = '2.2.0';
        }

        return $version;
    }
}
