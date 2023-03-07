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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace qtism\runtime\storage\serializable;

use qtism\runtime\tests\AssessmentTestSession;

class PhpSerializer implements SerializerInterface
{

    public function encode(AssessmentTestSession $assessmentTestSession): string
    {
        return serialize($assessmentTestSession);
    }

    public function decode(string $serializedAssessmentTestSession): AssessmentTestSession
    {
        $result = unserialize($serializedAssessmentTestSession);

        if($result instanceof AssessmentTestSession) {
            return $result;
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Unexpected serialized value provided [%s], expected [%s]',
                is_object($result) ? get_class($result) : gettype($result),
                AssessmentTestSession::class
            )
        );
    }
}
