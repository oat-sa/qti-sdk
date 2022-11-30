<?php

declare(strict_types=1);

namespace qtismtest\data\results;

use qtism\data\results\SessionStatus;
use qtismtest\QtiSmEnumTestCase;

class SessionStatusTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return SessionStatus::class;
    }

    protected function getNames()
    {
        return [
            'final',
            'initial',
            'pendingResponseProcessing',
            'pendingSubmission',
        ];
    }

    protected function getKeys()
    {
        return [
            'STATUS_FINAL',
            'STATUS_INITIAL',
            'STATUS_PENDING_RESPONSE_PROCESSING',
            'STATUS_PENDING_SUBMISSION',
        ];
    }

    protected function getConstants()
    {
        return [
            SessionStatus::STATUS_FINAL,
            SessionStatus::STATUS_INITIAL,
            SessionStatus::STATUS_PENDING_RESPONSE_PROCESSING,
            SessionStatus::STATUS_PENDING_SUBMISSION,
        ];
    }
}
