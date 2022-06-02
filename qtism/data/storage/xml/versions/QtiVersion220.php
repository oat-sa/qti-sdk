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

    const MARSHALLER_FACTORY = Qti22MarshallerFactory::class;

    public const HTML5_NAMESPACE = 'http://www.imsglobal.org/xsd/imsqtiv2p2_html5_v1p0';

    public const HTML5_NAMESPACE_PREFIX = 'qh5';

    public const HTML5_XSD = 'http://www.imsglobal.org/xsd/qti/qtiv2p2/imsqtiv2p2p2_html5_v1p0.xsd';

    public function getExternalNamespace(string $prefix): string
    {
        if ($prefix === self::HTML5_NAMESPACE_PREFIX) {
            return self::HTML5_NAMESPACE;
        }

        return '';
    }
}
