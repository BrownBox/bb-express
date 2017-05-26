<?php
namespace BrownBox\Express\Helper;

class DateTime {
	/**
	 * Convert seconds to years/days/hours etc.
	 *
	 * @param int $time
	 * @return array|bool
	 * @static
	 * @access public
	 */
	public static function seconds_to_time( $time ) {
		if(is_numeric($time)){
			$value = array(
				"years" => 0, "days" => 0, "hours" => 0,
				"minutes" => 0, "seconds" => 0,
			);

			if( $time >= 31556926 ) {
				$value["years"] = floor( $time / 31556926 );
				$time = ( $time % 31556926 );
			}

			if ( $time >= 86400 ) {
				$value["days"] = floor( $time / 86400 );
				$time = ( $time % 86400 );
			}

			if( $time >= 3600 ) {
				$value["hours"] = floor( $time / 3600 );
				$time = ( $time % 3600 );
			}

			if ( $time >= 60 ) {
				$value["minutes"] = floor( $time / 60 );
				$time = ( $time % 60 );
			}

			$value["seconds"] = floor( $time) ;

			return (array) $value;
		} else {
			return (bool) FALSE;
		}
	}

	/**
	 * Returns the timezone string for a site, even if it's set to a UTC offset
	 *
	 * Taken from https://www.skyverge.com/blog/down-the-rabbit-hole-wordpress-and-timezones/
	 *
	 * Adapted from http://www.php.net/manual/en/function.timezone-name-from-abbr.php#89155
	 *
	 * @return string valid PHP timezone string
	 */
	public static function get_timezone_string() {
	    // if site timezone string exists, return it
	    if ($timezone = get_option('timezone_string')) {
	        return $timezone;
	    }

	    // get UTC offset, if it isn't set then return UTC
	    if (0 === ($utc_offset = get_option('gmt_offset', 0))) {
	        return 'UTC';
	    }

	    // adjust UTC offset from hours to seconds
	    $utc_offset *= 3600;

	    // attempt to guess the timezone string from the UTC offset
	    if ($timezone = timezone_name_from_abbr('', $utc_offset, 0)) {
	        return $timezone;
	    }

	    // last try, guess timezone string manually
	    $is_dst = date('I');

	    foreach (timezone_abbreviations_list() as $abbr) {
	        foreach ($abbr as $city) {
	            if ($city['dst'] == $is_dst && $city['offset'] == $utc_offset) {
	                return $city['timezone_id'];
	            }
	        }
	    }

	    // fallback to UTC
	    return 'UTC';
	}

	/**
	 * Get DateTimeZone object for current site
	 * @param string $timezone_str
	 * @return DateTimeZone
	 */
	public static function get_timezone($timezone_str = '') {
	    if (empty($timezone_str)) {
	        $timezone_str = self::get_timezone_string();
	    }
	    return new \DateTimeZone($timezone_str);
	}

	/**
	 * Get DateTime object for current time
	 * @param DateTimeZone $timezone
	 * @return DateTime
	 */
	public static function get_current_datetime(\DateTimeZone $timezone = null) {
	    if (is_null($timezone)) {
	        $timezone = self::get_timezone();
	    }
	    return new \DateTime('now', $timezone);
	}

	/**
	 * Get DateTime object for specified date/time
	 * @param string $datetime
	 * @param DateTimeZone $timezone
	 * @return \DateTime
	 */
	public static function get_datetime($datetime = '', \DateTimeZone $timezone = null) {
	    if (empty($datetime)) {
	        return self::get_current_datetime($timezone);
	    }

	    if (is_int($datetime)) {
	        $datetime = '@'.$datetime;
	    }

	    if (is_null($timezone)) {
	        $timezone = self::get_timezone();
	    }
	    return new \DateTime($datetime, $timezone);
	}
}
