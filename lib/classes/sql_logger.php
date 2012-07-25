<?php
/**
 * This file houses the MpmSqlLogger class.
 *
 * @package    mysql_php_migrations
 * @subpackage Classes
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmSqlLogger is a Singleton class used to log SQL executed into a file
 *
 * @package    mysql_php_migrations
 * @subpackage Classes
 */
class MpmSqlLogger {
	private static $file_name = "log.sql";

	private static $enabled = false;

	public static function set_enable($enabled) {
		self::$enabled = $enabled;
	}

	public static function log_to_file($string) {
		if (self::$enabled) {
			error_log($string . "\n", 3, self::$file_name);
		}
	}

	public static function remove_file() {
		if (file_exists(self::$file_name)) {
			unlink(self::$file_name);
		}
	}
}