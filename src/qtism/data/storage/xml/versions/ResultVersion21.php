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

/**
 * QTI Result version 2.1
 */
class ResultVersion21 extends ResultVersion
{
    const XMLNS = 'http://www.imsglobal.org/xsd/imsqti_result_v2p1';

    const XSD = 'http://www.imsglobal.org/xsd/qti/qtiv2p1/imsqti_result_v2p1.xsd';

    const LOCAL_XSD = 'qtiv2p1/imsqti_result_v2p1.xsd';
}
