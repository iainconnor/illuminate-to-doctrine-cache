<?php

namespace IainConnor\IlluminateToDoctrineCache;

use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\FileCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\PredisCache;
use Illuminate\Cache\CacheManager;
use Illuminate\Cache\FileStore;
use Illuminate\Cache\MemcachedStore;
use Illuminate\Cache\RedisStore;
use Illuminate\Cache\Repository;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Predis\ClientInterface;

class IlluminateToDoctrineCacheFactoryTest extends TestCase
{
    public function testValidCacheTypes()
    {
        $expectedDriverClassMap = [
            'apc' => [ApcCache::class, ApcuCache::class],
            'file' => FileCache::class,
            'memcached' => MemcachedCache::class,
            'redis' => PredisCache::class,
        ];

        foreach ($expectedDriverClassMap as $driver => $expectedClass) {
            $cacheStore = $this->getMockStoreForDriver($driver);

            $cacheRepository = $this->createMock(Repository::class);
            $cacheRepository
                ->method('getStore')
                ->willReturn($cacheStore);

            $cacheManager = $this->getMockCacheManager($driver, $cacheRepository);

            $factory = new IlluminateToDoctrineCacheFactory($cacheManager);
            $doctrineCache = $factory->getDoctrineCacheForIlluminateCache();

            if (is_array($expectedClass)) {
                $found = false;
                foreach ($expectedClass as $expectedClassExample) {
                    if ($doctrineCache instanceof $expectedClassExample) {
                        $found = true;
                        break;
                    }
                }

                $this->assertTrue($found, "'" .
                                          get_class($doctrineCache) .
                                          " is not an instance of any of '" .
                                          join("', '", $expectedClass) .
                                          "'.");
            } else {
                $this->assertInstanceOf($expectedClass, $doctrineCache);
            }
        }
    }

    /**
     * @param string $defaultDriver
     *
     * @return \Illuminate\Contracts\Cache\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockStoreForDriver(string $defaultDriver)
    {
        switch ($defaultDriver) {
            case 'file':
                return new FileStore($this->createMock(Filesystem::class), sys_get_temp_dir());
                break;
            case 'memcached':
                return new MemcachedStore($this->createMock(\Memcached::class));
                break;
            case 'redis':
                $redisStore = $this->createMock(RedisStore::class);
                $redisStore
                    ->method('connection')
                    ->willReturn($this->createMock(ClientInterface::class));

                return $redisStore;
                break;
            default:
                return $this->createMock("\Illuminate\Cache\\" . ucfirst(strtolower($defaultDriver)) . "Store");
                break;
        }
    }

    /**
     * @param string     $defaultDriver
     * @param Repository $cacheRepository
     *
     * @return CacheManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private
    function getMockCacheManager(
        string $defaultDriver,
        Repository $cacheRepository
    ) {
        $cacheManager = $this->createMock(CacheManager::class);
        $cacheManager
            ->method('getDefaultDriver')
            ->willReturn($defaultDriver);
        $cacheManager
            ->method('store')
            ->willReturn($cacheRepository);

        return $cacheManager;
    }

    public function testInvalidCacheTypes()
    {
        $this->expectException(NoMatchingCacheException::class);

        $cacheRepository = $this->createMock(Repository::class);

        $cacheManager = $this->getMockCacheManager('foo', $cacheRepository);

        $factory = new IlluminateToDoctrineCacheFactory($cacheManager);
        $factory->getDoctrineCacheForIlluminateCache();
    }
}
