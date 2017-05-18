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
 * Copyright (c) 2013-2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\rules;

use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;

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
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ResponseCondition extends QtiComponent implements ResponseRule
{
    /**
	 * A ResponseIf object.
	 *
	 * @var \qtism\data\rules\ResponseIf
	 * @qtism-bean-property
	 */
    private $responseIf;

    /**
	 * A collection of ResponseElseIf objects.
	 *
	 * @var \qtism\data\rules\ResponseElseIfCollection
	 * @qtism-bean-property
	 */
    private $responseElseIfs;

    /**
	 * An optional ResponseElse object.
	 *
	 * @var \qtism\data\rules\ResponseElse
	 * @qtism-bean-property
	 */
    private $responseElse = null;

    /**
	 * Create a new instance of ResponseCondition.
	 *
	 * @param \qtism\data\rules\ResponseIf $responseIf An ResponseIf object.
	 * @param \qtism\data\rules\ResponseElseIfCollection $responseElseIfs A collection of ResponseElseIf objects.
	 * @param \qtism\data\rules\ResponseElse $responseElse An ResponseElse object.
	 */
    public function __construct(ResponseIf $responseIf, ResponseElseIfCollection $responseElseIfs = null, ResponseElse $responseElse = null)
    {
        $this->setResponseIf($responseIf);
        $this->setResponseElse($responseElse);
        $this->setResponseElseIfs((is_null($responseElseIfs)) ? new ResponseElseIfCollection() : $responseElseIfs);
    }

    /**
	 * Get the ResponseIf object.
	 *
	 * @return \qtism\data\rules\ResponseIf A ResponseIf object.
	 */
    public function getResponseIf()
    {
        return $this->responseIf;
    }

    /**
	 * Set the ResponseIf object.
	 *
	 * @param \qtism\data\rules\ResponseIf $responseIf A ResponseIf object.
	 */
    public function setResponseIf(ResponseIf $responseIf)
    {
        $this->responseIf = $responseIf;
    }

    /**
	 * Get the collection of ResponseElseIf objects.
	 *
	 * @return \qtism\data\rules\ResponseElseIfCollection A ResponseElseIfCollection object.
	 */
    public function getResponseElseIfs()
    {
        return $this->responseElseIfs;
    }

    /**
	 * Set the collection of ResponseElseIf objects.
	 *
	 * @param \qtism\data\rules\ResponseElseIfCollection $responseElseIfs A ResponseElseIfCollection object.
	 */
    public function setResponseElseIfs(ResponseElseIfCollection $responseElseIfs)
    {
        $this->responseElseIfs = $responseElseIfs;
    }

    /**
	 * Get the optional ResponseElse object. Returns null if not specified.
	 *
	 * @return \qtism\data\rules\ResponseElse A ResponseElse object.
	 */
    public function getResponseElse()
    {
        return $this->responseElse;
    }

    /**
	 * Set the optional ResponseElse object. A null value means there is no else.
	 *
	 * @param \qtism\data\rules\ResponseElse $responseElse A ResponseElse object.
	 */
    public function setResponseElse(ResponseElse $responseElse = null)
    {
        $this->responseElse = $responseElse;
    }

    /**
	 * Whether or not a ResponseElse object is defined for the response condition.
	 *
	 * @return boolean
	 */
    public function hasResponseElse()
    {
        return $this->getResponseElse() !== null;
    }

    /**
	 * @see \qtism\data\QtiComponent::getQtiClassName()
	 */
    public function getQtiClassName()
    {
        return 'responseCondition';
    }

    /**
	 * @see \qtism\data\QtiComponent::getComponents()
	 */
    public function getComponents()
    {
        $comp = array_merge(
                    array($this->getResponseIf()),
                    $this->getResponseElseIfs()->getArrayCopy()
                );

        if (!is_null($this->getResponseElse())) {
            $comp[] = $this->getResponseElse();
        }

        return new QtiComponentCollection($comp);
    }

    /**
     * Transforms this rule into a Qti-PL string.
     *
     *@return string A Qti-PL representation of the rule
     */
    public function toQtiPL()
    {
        $qtipl = $this->responseIf->toQtiPL();
        $qtipl .= (count($this->responseElseIfs) > 0) ? $this->responseElseIfs->toQtiPL(): "";
        $qtipl .= ($this->responseElse == null) ? "" : " " . $this->responseElse->toQtiPL();
        return $qtipl;
    }
}
