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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace qtism\data\storage\xml\versions;

use qtism\data\storage\xml\marshalling\Qti22MarshallerFactory;
use qtism\data\storage\xml\marshalling\Qti30MarshallerFactory;

class ResultVersion30 extends ResultVersion
{
    public const XMLNS = 'http://www.imsglobal.org/xsd/imsqti_result_v3p0';

    public const XSD = 'https://purl.imsglobal.org/spec/qti/v3p0/schema/xsd/imsqti_resultv3p0_v1p0.xsd';

    public const LOCAL_XSD = 'qtiv3p0/imsqti_resultv3p0_v1p0.xsd';

    public const MARSHALLER_FACTORY = Qti30MarshallerFactory::class;
}
