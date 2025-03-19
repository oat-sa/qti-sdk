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

namespace qtism\data\rules;

use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * From IMS QTI:
 *
 * If the expression given in a responseIf or responseElseIf evaluates to true
 * then the sub-rules contained within it are followed and any following
 * responseElseIf or responseElse parts are ignored for this response condition.
 *
 * If the expression given in a responseIf or responseElseIf does not evaluate
 * to true then consideration passes to the next responseElseIf or, if there are
 * no more responseElseIf parts then the sub-rules of the responseElse are
 * followed (if specified).
 */
class ResponseCondition extends QtiComponent implements ResponseRule
{
    /**
     * A ResponseIf object.
     *
     * @var ResponseIf
     * @qtism-bean-property
     */
    private $responseIf;

    /**
     * A collection of ResponseElseIf objects.
     *
     * @var ResponseElseIfCollection
     * @qtism-bean-property
     */
    private $responseElseIfs;

    /**
     * An optional ResponseElse object.
     *
     * @var ResponseElse
     * @qtism-bean-property
     */
    private $responseElse = null;

    /**
     * Create a new instance of ResponseCondition.
     *
     * @param ResponseIf $responseIf An ResponseIf object.
     * @param ResponseElseIfCollection $responseElseIfs A collection of ResponseElseIf objects.
     * @param ResponseElse $responseElse An ResponseElse object.
     */
    public function __construct(ResponseIf $responseIf, ?ResponseElseIfCollection $responseElseIfs = null, ?ResponseElse $responseElse = null)
    {
        $this->setResponseIf($responseIf);
        $this->setResponseElse($responseElse);
        $this->setResponseElseIfs($responseElseIfs ?? new ResponseElseIfCollection());
    }

    /**
     * Get the ResponseIf object.
     *
     * @return ResponseIf A ResponseIf object.
     */
    public function getResponseIf(): ResponseIf
    {
        return $this->responseIf;
    }

    /**
     * Set the ResponseIf object.
     *
     * @param ResponseIf $responseIf A ResponseIf object.
     */
    public function setResponseIf(ResponseIf $responseIf): void
    {
        $this->responseIf = $responseIf;
    }

    /**
     * Get the collection of ResponseElseIf objects.
     *
     * @return ResponseElseIfCollection A ResponseElseIfCollection object.
     */
    public function getResponseElseIfs(): ResponseElseIfCollection
    {
        return $this->responseElseIfs;
    }

    /**
     * Set the collection of ResponseElseIf objects.
     *
     * @param ResponseElseIfCollection $responseElseIfs A ResponseElseIfCollection object.
     */
    public function setResponseElseIfs(ResponseElseIfCollection $responseElseIfs): void
    {
        $this->responseElseIfs = $responseElseIfs;
    }

    /**
     * Get the optional ResponseElse object. Returns null if not specified.
     *
     * @return ResponseElse|null A ResponseElse object.
     */
    public function getResponseElse(): ?ResponseElse
    {
        return $this->responseElse;
    }

    /**
     * Set the optional ResponseElse object. A null value means there is no else.
     *
     * @param ResponseElse $responseElse A ResponseElse object.
     */
    public function setResponseElse(ResponseElse $responseElse = null): void
    {
        $this->responseElse = $responseElse;
    }

    /**
     * Whether or not a ResponseElse object is defined for the response condition.
     *
     * @return bool
     */
    public function hasResponseElse(): bool
    {
        return $this->getResponseElse() !== null;
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'responseCondition';
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents(): QtiComponentCollection
    {
        $comp = array_merge(
            [$this->getResponseIf()],
            $this->getResponseElseIfs()->getArrayCopy()
        );

        if ($this->getResponseElse() !== null) {
            $comp[] = $this->getResponseElse();
        }

        return new QtiComponentCollection($comp);
    }
}
