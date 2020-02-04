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

use DOMElement;
use LogicException;

/**
 * Class XmlResultDocument
 */
class XmlResultDocument extends XmlDocument
{
    /**
     * Validate DomDocument against associated xsd
     *
     * Add DomDocument to XmlUtils to fetch xsd by document namespace
     *
     * @param string $filename
     * @throws XmlStorageException
     */
    public function schemaValidate($filename = '')
    {
        parent::schemaValidate(__DIR__ . '/schemes/qtiv2p1/imsqti_result_v2p1.xsd');
    }

    /**
     * Decorate the root DomElement
     *
     * Add Result namespace regarding version
     *
     * @param DOMElement $rootElement
     * @throws LogicException if the version is not supported by QTI result
     */
    protected function decorateRootElement(DOMElement $rootElement)
    {
        $version = trim($this->getVersion());
        switch ($version) {
            case '2.1':
                $qtiSuffix = 'result_v2p1';
                $xsdLocation = 'http://www.imsglobal.org/xsd/qti/qtiv2p1/imsqti_result_v2p1.xsd';
                break;

            case '2.2':
                $qtiSuffix = 'result_v2p2';
                $xsdLocation = 'http://www.imsglobal.org/xsd/qti/qtiv2p2/imsqti_result_v2p2.xsd';
                break;

            default:
                throw new LogicException('Result xml is not supported for QTI version "' . $version . '"');
        }

        $rootElement->setAttribute('xmlns', "http://www.imsglobal.org/xsd/imsqti_${qtiSuffix}");
        $rootElement->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $rootElement->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation', "http://www.imsglobal.org/xsd/imsqti_${qtiSuffix} ${xsdLocation}");
    }
}
