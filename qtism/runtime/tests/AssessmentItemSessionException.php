<?php

namespace qtism\runtime\tests;

use \Exception;

class AssessmentItemSessionException extends Exception {
	
	const UNKNOWN = 0;
	
	const DURATION_EXCEEDED = 1;
	
	const MAX_ATTEMPTS_EXCEEDED = 2;
}