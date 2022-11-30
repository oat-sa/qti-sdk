<?php

declare(strict_types=1);

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

namespace qtism\data\results;

use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiUri;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * Class SessionIdentifier
 *
 * The system that creates the result (for example, the test delivery system) should assign a session identifier
 * that it can use to identify the session.
 */
class SessionIdentifier extends QtiComponent
{
    /**
     * A unique identifier of the system which added this identifier to the result.
     *
     * Multiplicity [1]
     *
     * @var QtiUri
     */
    protected $sourceID;

    /**
     * The system that creates the report should add a session identifier.
     * Subsequent systems that process the results might use their own identifier for the session
     * and should add this too if the result is exported again for further transport.
     *
     * Multiplicity [1]
     *
     * @var QtiIdentifier
     */
    protected $identifier;

    /**
     * SessionIdentifier constructor.
     *
     * XML representation of the session created by the delivery system
     *
     * @param QtiUri $sourceID
     * @param QtiIdentifier $identifier
     */
    public function __construct(QtiUri $sourceID, QtiIdentifier $identifier)
    {
        $this->setSourceID($sourceID);
        $this->setIdentifier($identifier);
    }

    /**
     * Returns the QTI class name as per QTI 2.1 specification.
     *
     * @return string A QTI class name.
     */
    public function getQtiClassName(): string
    {
        return 'sessionIdentifier';
    }

    /**
     * Get the direct child components of this one.
     *
     * @return QtiComponentCollection A collection of QtiComponent objects.
     */
    public function getComponents(): QtiComponentCollection
    {
        return new QtiComponentCollection();
    }

    /**
     * Get the source id of the session identifier
     *
     * @return QtiUri
     */
    public function getSourceID(): QtiUri
    {
        return $this->sourceID;
    }

    /**
     * Set the source id of the session identifier
     *
     * @param QtiUri $sourceID
     * @return $this
     */
    public function setSourceID(QtiUri $sourceID)
    {
        $this->sourceID = $sourceID;
        return $this;
    }

    /**
     * Get the identifier of the session identifier
     *
     * @return QtiIdentifier
     */
    public function getIdentifier(): QtiIdentifier
    {
        return $this->identifier;
    }

    /**
     * Set the identifier of the session identifier
     *
     * @param QtiIdentifier $identifier
     * @return $this
     */
    public function setIdentifier(QtiIdentifier $identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }
}
