# Illuminate To Doctrine Cache

Provides a mapping between Illuminate and Doctrine Cache components.

## Installation

From composer, `composer require iainconnor/illuminate-to-doctrine-cache`.

## Usage

1. Configure your Illuminate cache as normal in Laravel or Lumen.
2. Add `\IainConnor\IlluminateToDoctrineCache\IlluminateToDoctrineCacheServiceProvider` in [Laravel](https://laravel.com/docs/5.4/providers#registering-providers) or [Lumen](https://lumen.laravel.com/docs/5.4/providers#registering-providers).
3. Fetch `\IainConnor\IlluminateToDoctrineCache\IlluminateToDoctrineCacheFactory` from the service container.
4. Call the `getDoctrineCacheForIlluminateCache()` method to get an instance of `\Doctrine\Common\Cache\Cache` for your configured Illuminate cache.
5. Be sure to catch and handle `\IainConnor\IlluminateToDoctrineCache\NoMatchingCacheException`, which will be thrown if no matching Doctrine cache can be found for your Illuminate cache.  
   Currently supported Illuminate cache drivers are `redis`, `memcached`, `file`, `array` and `apc`.