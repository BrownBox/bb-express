<?php
namespace BrownBox\Express\Helper;

/**
 * A helper class for managing cookies
 * @author markparnell
 */
class Cookie {
    const LIFETIME = 7776000; // 90 days
    private static $prefix = 'bbx_';

    /**
     * Add/update a cookie
     * @param string $name
     * @param mixed $value
     * @param integer $expiry Optional
     * @return boolean
     */
    public static function setCookie($name, $value, $expiry = null) {
        if (is_null($expiry)) {
            $expiry = time()+self::LIFETIME;
        }
        $result = setcookie(self::$prefix.$name, $value, $expiry, '/', $_SERVER['SERVER_NAME']);
        if ($result) { // Add it to $_COOKIE so it's available before next page load
            $_COOKIE[self::$prefix.$name] = $value;
        }
        return $result;
    }

    /**
     * Delete an existing cookie
     * @param string $name
     * @return boolean
     */
    public static function deleteCookie($name) {
        $result = self::setCookie($name, null, strtotime('-1 year'));
        if (self::hasCookie($name)) {
            unset($_COOKIE[self::$prefix.$name]);
        }
        return $result;
    }

    /**
     * Check whether the cookie exists
     * @param string $name
     * @return boolean
     */
    public static function hasCookie($name) {
        return array_key_exists(self::$prefix.$name, $_COOKIE);
    }

    /**
     * Get a cookie's value. Will return null if cookie doesn't exist.
     * @param string $name
     * @return mixed
     */
    public static function getCookie($name) {
        if (self::hasCookie($name)) {
            return $_COOKIE[self::$prefix.$name];
        }
        return null;
    }
}
