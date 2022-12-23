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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\content;

use qtism\data\ExternalQtiComponent;

/**
 * From IMS QTI:
 *
 * MathML defines a Markup Language for describing mathematical notation using XML. The
 * primary purpose of MathML is to provide a language for embedding mathematical
 * expressions into other documents, in particular into HTML documents.
 */
class Math extends ExternalQtiComponent implements BlockStatic, FlowStatic, InlineStatic
{
    use FlowTrait;

    /**
     * Math constructor.
     *
     * @param $xmlString
     */
    public function __construct($xmlString)
    {
        parent::__construct($xmlString);
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'math';
    }

    protected function buildTargetNamespace(): void
    {
        $this->setTargetNamespace('http://www.w3.org/1998/Math/MathML');
    }
}
