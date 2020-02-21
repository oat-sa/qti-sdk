<?php

namespace qtism;

/**
 * Helps triggering deprecation messages for classes and functions.
 */
trait DeprecatedTrait
{
    /**
     * Triggers deprecation message for class.
     */
    protected function deprecateClass()
    {
        trigger_error(
            sprintf('Class "%s" is deprecated since version "%s". Please use "%s" instead.', get_class($this), $this::DEPRECATED_SINCE, $this::DEPRECATED_REPLACE_CLASS),
            E_USER_DEPRECATED
        );
    }
    
    /**
     * Triggers deprecation message for class (static version).
     */
    protected static function deprecateClassStatic()
    {
        trigger_error(
            sprintf('Class "%s" is deprecated since version "%s". Please use "%s" instead.', __CLASS__, self::DEPRECATED_SINCE, self::DEPRECATED_REPLACE_CLASS),
            E_USER_DEPRECATED
        );
    }
}
