<?php

/**
* Class Sessions
* 
* @author  Igor Shvartsev (igor.shvartsev@gmail.com)
* @package Divak
* @version 1.0
*/
class Session
{
    /**
     * Instance.
     *
     * @var Session
     */
    protected static $instance = null;

    /**
     * Lifetime of the session cookie, defined in seconds.
     *
     * @var numeric
     */
    protected static $lifetime = 0;

    /**
     * Path on the domain where the cookie will work. Use a single slash ('/') for all paths on the domain.
     *
     * @var string
     */
    protected static $path = '/';

    /**
     * Domain for example 'www.php.net'.
     * To make cookies visible on all subdomains then the domain must be prefixed with a dot like '.php.net'.
     *
     * @var string
     */
    protected static $domain = '';

    /**
     * isSecure.
     * If TRUE cookie will only be sent over isSecure connections.
     *
     * @var bool
     */
    protected static $isSecure = false;

    /**
     * Http only
     * If set to TRUE then PHP will attempt to send the httponly flag when setting the session cookie.
     *
     * @var bool
     */
    protected static $isHttpOnly = false;

    /**
     * Disable construct.
     */
    private function __construct()
    {
    }

    /**
     * Magic get.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return self::get($name);
    }

    /**
     * Magic set.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        self::set($name, $value);
    }

    /**
     * Disable clone.
     */
    private function __clone()
    {
    }

    /**
     * Gets instance.
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Set cookie params.
     *
     * @param numeric $lifetime
     * @param string $path
     * @param string $domain
     * @param bool $isSecure
     * @param bool $isHttpOnly
     */
    public static function setCookieParams(
        $lifetime = 0, $path = '/', $domain = '', $isSecure = false, $isHttpOnly = false
    ) {
        self::$lifetime = $lifetime;
        self::$path = $path;
        self::$domain = $domain;
        self::$isSecure = $isSecure;
        self::$isHttpOnly = $isHttpOnly;
    }

    /**
     * Set storage path.
     *
     * @param string $storagePath - abs path to the dir
     */
    public function setStoragePath($storagePath)
    {
        session_save_path($storagePath);
    }

    /**
     * Start session.
     *
     * @param string $name
     */
    public static function start($name = null)
    {
        if ($name) {
            session_name(preg_replace('/[^a-zA-Z1-9]/i', '', $name));
            session_set_cookie_params(
                self::$lifetime, self::$path, self::$domain, self::$isSecure, self::$isHttpOnly
            );
        }
        session_start();
    }

    /**
     * Set session variable.
     *
     * @param string $name
     * @param mixed  $value
     */
    public static function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * Get session variable.
     *
     * @param string $name
     *
     * @return mixed
     */
    public static function get($name)
    {
        return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
    }

    /**
     * Get all session variables.
     *
     * @return []
     */
    public static function getAll()
    {
        return $_SESSION;
    }

    /**
     * Remove session variable.
     *
     * @param mixed $name
     */
    public static function remove($name)
    {
        unset($_SESSION[$name]);
    }
}
