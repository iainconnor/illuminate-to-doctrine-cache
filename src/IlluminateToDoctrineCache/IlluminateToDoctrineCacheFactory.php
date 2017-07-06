<?php

namespace IainConnor\IlluminateToDoctrineCache;

use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache as DoctrineCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\PredisCache;
use Illuminate\Cache\CacheManager as IlluminateCache;
use Illuminate\Cache\FileStore;
use Illuminate\Cache\MemcachedStore;
use Illuminate\Cache\RedisStore;

class IlluminateToDoctrineCacheFactory
{
    /** @var IlluminateCache */
    protected $illuminateCache;
    /** @var DoctrineCache */
    protected $doctrineCache;

    /**
     * IlluminateToDoctrineCacheFactory constructor.
     *
     * @param IlluminateCache $illuminateCache
     */
    public function __construct(IlluminateCache $illuminateCache)
    {
        $this->illuminateCache = $illuminateCache;
    }

    /**
     * @throws NoMatchingCacheException
     * @return DoctrineCache
     */
    public function getDoctrineCacheForIlluminateCache()
    {
        if (!isset($this->doctrineCache) || is_null($this->doctrineCache)) {
            $illuminateCacheDriver = ucfirst(strtolower($this->illuminateCache->getDefaultDriver()));

            $cacheMethod = 'get' . $illuminateCacheDriver . 'Cache';

            if (method_exists($this, $cacheMethod)) {
                $this->doctrineCache = $this->{$cacheMethod}($this->illuminateCache->store()->getStore());
            } else {
                throw new NoMatchingCacheException($illuminateCacheDriver);
            }
        }

        return $this->doctrineCache;
    }

    /**
     * @return ApcCache|ApcuCache
     */
    private function getApcCache()
    {
        return function_exists('apcu_fetch') ? new ApcuCache() : new ApcCache();
    }

    /**
     * @return ArrayCache
     */
    private function getArrayCache()
    {
        return new ArrayCache();
    }

    /**
     * @param FileStore $fileStore
     *
     * @return FilesystemCache
     */
    private function getFileCache(FileStore $fileStore)
    {
        return new FilesystemCache($fileStore->getDirectory());
    }

    /**
     * @param MemcachedStore $memcachedStore
     *
     * @return MemcachedCache
     */
    private function getMemcachedCache(MemcachedStore $memcachedStore)
    {
        $memcachedCache = new MemcachedCache();
        $memcachedCache->setMemcached($memcachedStore->getMemcached());

        return $memcachedCache;
    }

    private function getRedisCache(RedisStore $redisStore)
    {
        return new PredisCache($redisStore->connection());
    }
}
