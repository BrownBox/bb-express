<?php
/**
 * @class UserAgent
 *
 * Contains functionality related to client browsers
 *
 * @since 3.0.11
 *
 */

namespace BrownBox\Express\Helper;

class UserAgent {
    /**
     * Basic check for a handful of the most common bots
     * @access public
     * @return bool
     * @static
     */
    public static function is_bot() {
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $spiders = array(
                    // Baidu
                    'baidu',
                    // Bing
                    'bingbot',
                    'bingpreview',
                    'msnbot',
                    // DuckDuckGo
                    'duckduckgo',
                    // Google
                    'adsbot-google',
                    'googlebot',
                    'mediapartners-google',
                    // Teoma
                    'teoma',
                    // Yahoo!
                    'slurp', // yes, really!
                    // Yandex
                    'yandex',
            );
            foreach ($spiders as $spider) {
                if (stripos($_SERVER['HTTP_USER_AGENT'], $spider) !== false) {
                    return true;
                }
            }
        }
        return false;
    }
}
