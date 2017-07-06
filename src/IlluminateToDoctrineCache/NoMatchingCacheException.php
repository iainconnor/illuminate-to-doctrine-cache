<?php

namespace IainConnor\IlluminateToDoctrineCache;

class NoMatchingCacheException extends \Exception
{
    /**
     * NoMatchingCacheException constructor.
     */
    public function __construct($requestedCacheName)
    {
        parent::__construct("No matching Doctrine Cache class for `" . $requestedCacheName . "`.");
    }
}
