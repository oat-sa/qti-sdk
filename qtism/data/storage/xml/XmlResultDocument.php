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
 * @license GPLv2
 */

namespace qtism\data\storage\xml;

use DOMDocument;
use DOMElement;
use LogicException;
use qtism\data\storage\xml\versions\QtiVersionException;
use qtism\data\storage\xml\versions\ResultVersion;
use qtism\data\storage\xml\versions\ResultVersion21;
use qtism\data\storage\xml\versions\ResultVersion22;

/**
 * Class XmlResultDocument
 */
class XmlResultDocument extends XmlDocument
{
    /**
     * Set the QTI Result version in use for this document.
     *
     * @param string $versionNumber A QTI Result version number e.g. '2.1.0'.
     * @throws QtiVersionException when version is unknown regarding existing QTI Result versions.
     */
    public function setVersion($versionNumber)
    {
        $this->version = ResultVersion::create($versionNumber);
    }

    /**
     * Infer the QTI Result version of the document from its XML definition.
     *
     * @return string a semantic version inferred from the document.
     * @throws XmlStorageException when the version can not be inferred.
     */
    protected function inferVersion(): string
    {
        return ResultVersion::infer($this->getDomDocument());
    }
}
