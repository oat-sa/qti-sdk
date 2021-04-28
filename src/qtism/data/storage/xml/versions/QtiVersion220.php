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

namespace qtism\data\storage\xml\versions;

use qtism\data\storage\xml\marshalling\Qti22MarshallerFactory;

/**
 * QTI version 2.2.0
 */
class QtiVersion220 extends QtiVersion
{
    const XMLNS = 'http://www.imsglobal.org/xsd/imsqti_v2p2';

    const XSD = 'http://www.imsglobal.org/xsd/qti/qtiv2p2/imsqti_v2p2.xsd';

    const LOCAL_XSD = 'qtiv2p2/imsqti_v2p2.xsd';

    const QH5_NS = 'http://www.imsglobal.org/xsd/imsqtiv2p2_html5_v1p0';

    const QH5_XSD = 'http://www.imsglobal.org/xsd/qti/qtiv2p2/imsqtiv2p2p2_html5_v1p0.xsd';

    const MARSHALLER_FACTORY = Qti22MarshallerFactory::class;

    public function getExternalSchemaLocation(string $prefix): string
    {
        if ($prefix === 'qh5') {
            return static::QH5_XSD;
        }

        return '';
    }

    public function getExternalNamespace(string $prefix): string
    {
        if ($prefix === 'qh5') {
            return self::QH5_NS;
        }

        return '';
    }
}
