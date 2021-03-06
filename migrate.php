<?php
/**
 * This file is the main script which should be run on the command line in order to perform database migrations.
 * If you want to use this script like so:  ./migrate.php -- you will need to give it executable permissions (chmod +x migrate.php) and ensure the top line of this script points to the actual location of your PHP binary.
 *
 * @package    mysql_php_migrations
 * @subpackage Globals
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

// we want to see any errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

// fix date issues
if (function_exists('date_default_timezone_set'))
{
    date_default_timezone_set("UTC");
}

/**
 * Define the full path to this file.
 */
if (!defined('MPM_PATH')) {
  define('MPM_PATH', dirname(__FILE__));
}

/**
 * Version Number - for reference
 */
if(!defined('MPM_VERSION')) {
	define('MPM_VERSION', '2.2.0-luxbet');
}


/**
 * Include the init script.
 */
require_once(MPM_PATH . '/lib/init.php');
// get the proper controller, do the action, and exit the script
try {
	$obj = MpmControllerFactory::getInstance($argv);
	$obj->doAction(true);

} catch (MpmClassUndefinedException $ex) {
	// exit gracefully when wrong action is called like './migrate.php -h'
	$obj = MpmCommandLineWriter::getInstance();
	$obj->addText('Invalid action. Please try "./migrate.php help"');
	$obj->addText($ex->getMessage());
	$obj->write();
}
?>
