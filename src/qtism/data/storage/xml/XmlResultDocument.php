<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * Copyright (c) 2018-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Moyon Camille <camille@taotesting.com>
 * @author Julien Sébire <julien@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml;

use DOMDocument;
use InvalidArgumentException;
use qtism\data\storage\xml\versions\ResultVersion;

/**
 * Class XmlResultDocument
 */
class XmlResultDocument extends XmlDocument
{
    /**
     * Sets version to a supported QTI Result version.
     *
     * @param string $versionNumber
     * @throws InvalidArgumentException when version is not supported for QTI Result.
     */
    public function setVersion($versionNumber)
    {
        $this->version = ResultVersion::create($versionNumber);
    }

    /**
     * Infer the QTI version of the document from its XML definition.
     *
     * @return string false if cannot be inferred otherwise a semantic version of the QTI version with major, minor and patch versions e.g. '2.1.0'.
     * @throws XmlStorageException when the version can not be inferred.
     */
    protected function inferVersion(): string
    {
        return ResultVersion::inferFromDocument($this->getDomDocument());
    }
}
