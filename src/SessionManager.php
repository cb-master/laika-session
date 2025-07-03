<?php
/**
 * Library Name: Cloud Bill Master PHP Session Handler
 * Author: Showket Ahmed
 * Email: riyadhtayf@gmail.com 
 */

namespace CBM\Session;

use CBM\Session\Interface\SessionDriverInterface;
use CBM\Session\Handler\MemcachedSessionHandler;
use CBM\Session\Handler\RedisSessionHandler;
use CBM\Session\Handler\FileSessionHandler;
use CBM\Session\Handler\PdoSessionHandler;
use InvalidArgumentException;
use RuntimeException;
use Memcached;
use Redis;
use PDO;

class SessionManager
{
    // Session Manager Instance
    /**
     * @var self $instance
     */
    private static ?self $instance = null;

    // Session Handler
    /**
     * @var SessionDriverInterface $handler
     */
    private static SessionDriverInterface $handler;

    // Session Active Status
    /**
     * @var bool $started. Default is false
     */
    private static bool $started = false;

    // Session Options To Start
    /**
     * @var array $options. Session Start Options
     */
    private static array $options;

    // Session Cookie Parameters
    /**
     * @var array $cookies. Session Cookie Parameters
     */
    private static array $cookies;


    private function __construct()
    {
        self::$handler->setup();
    }

    // Session Handler Initiate
    /**
     * @param array|PDO|Redis|Memcached $config Required Argument.
     * File Session: Ignore This Parameter.
     * PDO Session: PDO Object or ['driver'=>'pdo'] and dsn,username,password Keys are Required
     * Redis Session: Redis Object or ['driver'=>'redis']. host,port,timeout,prefix,password Keys are Optional
     * Memcached Session: Memcached Object or ['driver'=>'memcached']. host,port,timeout,prefix Keys are Optional
     * @return self
     */
    public static function init(array|PDO|Redis|Memcached $config = []): self
    {
        self::boot($config);
        // Session Options
        self::$options = self::defaultOptions();
        // Session Cookies
        self::$cookies = self::defaultCookies();
        // Get Instance
        self::$instance ??= new self();
        return self::$instance;
    }

    // Set Session Options
    /**
     * @param array $options. Example ['name'=>'PHPSESSID'] and any other session options
     */
    public static function setOptions(array $options): void
    {
        if(!self::$instance){
            throw new RuntimeException('Please Run SessionManager::init() First.');
        }
        self::$options = array_merge(self::$options, $options);
    }

    // Set Session Cookies
    /**
     * @param array $cookies. Example ['path'=>'/'] and any other session cookies
     */
    public static function setCookies(array $cookies): void
    {
        if(!self::$instance){
            throw new RuntimeException('Please Run SessionManager::init() First.');
        }
        self::$cookies = array_merge(self::$cookies, $cookies);
    }

    // Start Session
    public static function start(): void
    {
        if(!self::$handler){
            throw new RuntimeException("Session handler not configured. Call SessionManager::config() first.");
        }
        if(!self::$started && (session_status() !== PHP_SESSION_ACTIVE)){
            // PHP INI Set
            // array_filter(self::$options, function($val,$key){
            //     ini_set("session.{$key}", $val);
            // }, ARRAY_FILTER_USE_BOTH);

            session_set_save_handler(self::$handler, true);

            // Session Cookies
            session_set_cookie_params(self::$cookies);

            // Session Start
            session_start(self::$options);
            self::$started = true;
        }
    }

    // Session End
    public static function end(): bool
    {
        self::start();
        session_write_close();
        session_unset();
        self::$started = false;
        return session_destroy();
    }

    ############################################
    ############# Internal Methods #############
    ############################################

    // Boot Session Handler
    /**
     * @param array|PDO|Redis|Memcached $config Required Argument.
     * File Session: Ignore This Parameter.
     * PDO Session: PDO Object or ['driver'=>'pdo'] and dsn,username,password Keys are Required
     * Redis Session: Redis Object or ['driver'=>'redis']. host,port,timeout,prefix,password Keys are Optional
     * Memcached Session: Memcached Object or ['driver'=>'memcached']. host,port,timeout,prefix Keys are Optional
     * @return self
     */
    private static function boot(array|PDO|Redis|Memcached $config): void
    {
        if(is_array($config)){
            $driver = strtolower($config['driver'] ?? 'file');
        }elseif(is_object($config)){
            if($config instanceof PDO){
                $driver = 'pdo';
            }elseif($config instanceof Redis){
                $driver = 'redis';
            }elseif($config instanceof Memcached){
                $driver = 'memcached';
            }else{
                $driver = get_class($config);
                throw new InvalidArgumentException("Session Manager Initiate Failed. Unsupported Object '{$driver}' Found!");
            }
        }
        switch($driver){
            case 'file':
                self::$handler = new FileSessionHandler($config);
                break;

            case 'pdo':
                self::$handler = new PdoSessionHandler($config);
                break;

            case 'redis':
                self::$handler = new RedisSessionHandler($config);
                break;

            case 'memcached':
                self::$handler = new MemcachedSessionHandler($config);
                break;

            default:
                throw new InvalidArgumentException("Unsupported Session Driver: '{$driver}'");
        }
    }

    // Session Options
    private static function defaultOptions()
    {
        return [
            'name'              =>  'CBMASTER',
			'use_only_cookies'	=>	true,
			'use_strict_mode'	=>	true,
			'gc_probability'	=>	1,
			'gc_divisor'		=>	100,
			'gc_maxlifetime'	=>	1440
		];
    }

    // Session Options
    private static function defaultCookies()
    {
        return [
			"path"      =>  '/',
			"secure"    =>  true,
			"httponly"  =>  true,
			"samesite"  =>  "Strict"
		];
    }
}