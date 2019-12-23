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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Moyon Camille, <camille@taotesting.com>
 * @author Julien Sébire, <julien@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\results;

use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiUri;
use qtism\common\datatypes\Utils;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * This is the context for the 'assessmentResult'. It provides the corresponding set of identifiers.
 */
class Context extends QtiComponent
{
    /**
     * A unique identifier for the test candidate. The attribute is defined by the IMS Learning Information Services specification [LIS, 13].
     *
     * Multiplicity [0,1]
     *
     * @var QtiIdentifier
     */
    protected $sourcedId;

    /**
     * The system that creates the result (for example, the test delivery system) should assign a session identifier
     * that it can use to identify the session. Subsequent systems that process the result might assign their own identifier
     * to the session which should be added to the context if the result is modified and exported for transport again.
     *
     * Multiplicity [0,*]
     *
     * @var SessionIdentifierCollection
     */
    protected $sessionIdentifiers;

    /**
     * Context constructor.
     *
     * Xml representation of Result Context
     *
     * @param QtiIdentifier|null $sourcedId
     * @param SessionIdentifierCollection|null $sessionIdentifiers
     */
    public function __construct(
        QtiIdentifier $sourcedId = null,
        SessionIdentifierCollection $sessionIdentifiers = null
    ) {
        $this->setSourcedId($sourcedId);
        if ($sessionIdentifiers === null) {
            $sessionIdentifiers = new SessionIdentifierCollection();
        }
        $this->setSessionIdentifiers($sessionIdentifiers);
    }

    /**
     * Returns the QTI class name as per QTI 2.1 specification.
     *
     * @return string A QTI class name.
     */
    public function getQtiClassName()
    {
        return 'context';
    }

    /**
     * Get the direct child components of this one.
     *
     * @return QtiComponentCollection A collection of QtiComponent objects.
     */
    public function getComponents(): QtiComponentCollection
    {
        if ($this->hasSessionIdentifiers()) {
            $components = $this->getSessionIdentifiers()->getArrayCopy();
        } else {
            $components = [];
        }
        return new QtiComponentCollection($components);
    }

    /**
     * Get the sourcedId of the context
     *
     * @return QtiIdentifier|null
     */
    public function getSourcedId()
    {
        return $this->sourcedId;
    }

    /**
     * Set the sourced id of the context
     *
     * @param QtiIdentifier $sourcedId
     * @return $this
     */
    public function setSourcedId(QtiIdentifier $sourcedId = null): self
    {
        $this->sourcedId = $sourcedId;
        return $this;
    }

    /**
     * Check if the context has a sourced id
     *
     * @return bool
     */
    public function hasSourcedId(): bool
    {
        return $this->sourcedId !== null;
    }

    /**
     * Get session identifiers of context
     *
     * @return SessionIdentifierCollection
     */
    public function getSessionIdentifiers(): SessionIdentifierCollection
    {
        return $this->sessionIdentifiers;
    }

    /**
     * Set the Session identifiers
     *
     * @param SessionIdentifierCollection $sessionIdentifiers
     * @return $this
     */
    public function setSessionIdentifiers(SessionIdentifierCollection $sessionIdentifiers): self
    {
        $this->sessionIdentifiers = $sessionIdentifiers;
        return $this;
    }

    /**
     * Adds a Session identifier given its parameters.
     *
     * @param string $sourceId
     * @param string $identifier
     * @return $this
     * @throws DuplicateSourceIdException when an already existing sourceId is given.
     */
    public function addSessionIdentifier(string $sourceId, string $identifier): self
    {
        $identifier = Utils::normalizeString($identifier);

        if ($this->hasSessionIdentifierWithSourceId($sourceId)) {
            throw new DuplicateSourceIdException('SourceId "' . $sourceId . '" already exist in this AssessmentResult context.');
        }
        
        $this->sessionIdentifiers->attach(
            new SessionIdentifier(
                new QtiUri($sourceId),
                new QtiIdentifier($identifier)
            )
        );

        return $this;
    }

    /**
     * Check if the context has session identifiers
     *
     * @return bool
     */
    public function hasSessionIdentifiers(): bool
    {
        return (bool)$this->sessionIdentifiers->count();
    }

    /**
     * Check if the context has a session identifier with the given sourceId.
     *
     * @param $sourceId
     * @return bool
     */
    private function hasSessionIdentifierWithSourceId($sourceId): bool
    {
        foreach ($this->sessionIdentifiers as $sessionIdentifier) {
            if ($sessionIdentifier->getSourceID()->getValue() === $sourceId) {
                return true;
            }
        }

        return false;
    }
}
