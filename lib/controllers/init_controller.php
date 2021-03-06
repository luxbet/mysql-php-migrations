<?php
/**
 * This file houses the MpmInitController class.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmInitController initializes the system so that migrations can start happening.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 */
class MpmInitController extends MpmController
{

	/**
	 * Determines what action should be performed and takes that action.
	 *
	 * @uses MPM_PATH
	 * @uses MPM_METHOD_PDO
	 * @uses MPM_METHOD_MYSQLI
	 * @uses MpmDbHelper::checkForDbTable()
	 * @uses MpmDbHelper::getDbObj()
	 * @uses MpmDbHelper::getMethod()
	 * @uses MpmInitController::displayHelp()
	 * @uses MpmCommandLineWriter::getInstance()
	 * @uses MpmCommandLineWriter::writeHeader()
	 * @uses MpmCommandLineWriter::writeFooter()
	 * @uses MpmBuildController::build()
	 *
	 * @return void
	 */
	public function doAction()
	{
		$user = '';
		$dbname = '';
		$port = '';
		$db_path = '';
		$method = 0;

		$clw = MpmCommandLineWriter::getInstance();
		$clw->writeHeader();

		echo "Defaults are in brackets ([]).  To accept the default, simply press ENTER.\n\n";

		$skip_init_db_migration_config = false;

		if (file_exists(MPM_CONFIG_FILE_PATH . '/db_migration_config.php') ||
			file_exists(MPM_CONFIG_FILE_PATH . '/db.php')) {
			echo "\nFound an existing migration config file db_migration_config.php and will use that";
			$skip_init_db_migration_config = true;
		} else {
			$this->init_db_migration_config();
		}

		// $db_config is introduced by require db_migration_config.php
		if (file_exists(MPM_CONFIG_FILE_PATH . '/db_migration_config.php')) {
			require(MPM_CONFIG_FILE_PATH . '/db_migration_config.php');
		} else {
			// Use adapter to read from WAGERplayer DB config file
			require(MPM_MIGRATION_FILE_PATH.'/db_migration_config_adapter.php');
		}

		$GLOBALS['db_config'] = $db_config;

		$this->init_migration_db_table($db_config->migrations_table);

		// by default we don't rebuild database or running migrations
		if (isset($doBuild) && $doBuild === true)
		{
		    $obj = new MpmBuildController('build', $this->arguments);
		    $obj->build();
		    echo "\n\n";
		} else {
			echo "Your next step can be './migrate.php latest' to run all the migrations. \n\n";
		}

		$clw->writeFooter();
		exit;
	}

	/**
	 * @param $db_config
	 * @return array
	 */
	public function init_db_migration_config($db_config = null)
	{
		echo "\nEnter a name to use for the table that will hold your migration data [";
		if (isset($db_config) && isset($db_config->migrations_table)) {
			echo $db_config->migrations_table;
		}
		else {
			echo 'mpm_migrations';
		}
		echo ']: ';
		$migrations_table = fgets(STDIN);
		$migrations_table = trim($migrations_table);
		if (empty($migrations_table)) {
			if (isset($db_config) && isset($db_config->migrations_table)) {
				$migrations_table = $db_config->migrations_table;
			}
			else {
				$migrations_table = 'mpm_migrations';
			}
		}

		do {
			echo "\nWhich method would you like to use to connect to\nthe database?  " . MPM_METHOD_PDO . "=PDO or " . MPM_METHOD_MYSQLI . "=MySQLi";
			if (isset($db_config)) {
				echo " [" . $db_config->method . "]";
			}
			echo ": ";
			$method = fgets(STDIN);
			$method = trim($method);
			if (!is_numeric($method)) {
				$method = 0;
			}
			if (empty($method) && isset($db_config)) {
				$method = $db_config->method;
			}
		} while ($method < MPM_METHOD_PDO || $method > MPM_METHOD_MYSQLI || $method == 0);

		echo "\nEnter your MySQL database hostname or IP address [";
		if (isset($db_config)) {
			echo $db_config->host;
		}
		else {
			echo '127.0.0.1';
		}
		echo ']: ';
		$host = fgets(STDIN);
		$host = trim($host);
		if (empty($host)) {
			if (isset($db_config)) {
				$host = $db_config->host;
			}
			else {
				$host = '127.0.0.1';
			}
		}

		while (empty($port)) {
			echo "\nEnter your MySQL database port [";
			if (isset($db_config)) {
				echo $db_config->port;
			}
			else {
				echo '3306';
			}
			echo ']: ';

			$port = fgets(STDIN);
			$port = trim($port);
			if (empty($port)) {
				$port = 3306;
			}
			if (!is_numeric($port)) {
				$port = '';
			}
		}

		while (empty($user)) {
			echo "\nEnter your MySQL database username";
			if (isset($db_config)) {
				echo ' [', $db_config->user, ']';
			}
			echo ': ';
			$user = fgets(STDIN);
			$user = trim($user);
			if (empty($user) && isset($db_config)) {
				$user = $db_config->user;
			}
		}

		echo "\nEnter your MySQL database password (enter - for no password) [";
		if (isset($db_config)) {
			echo $db_config->pass;
		}
		echo ']: ';
		$pass = fgets(STDIN);
		$pass = trim($pass);
		if (empty($pass) && isset($db_config)) {
			$pass = $db_config->pass;
		}
		else if ($pass == '-') {
			$pass = '';
		}


		while (empty($dbname)) {
			echo "\nEnter your MySQL database name";
			if (isset($db_config)) {
				echo ' [', $db_config->name, ']';
			}
			echo ': ';
			$dbname = fgets(STDIN);
			$dbname = trim($dbname);
			if (empty($dbname) && isset($db_config)) {
				$dbname = $db_config->name;
			}
		}

		echo "\nEnter the directory where you'd like to store your\nmigration files [";
		if (isset($db_config)) {
			echo $db_config->db_path;
		}
		else {
			echo MPM_MIGRATION_FILE_PATH;
		}
		echo ']: ';
		$db_path = fgets(STDIN);
		$db_path = trim($db_path);
		if (empty($db_path) && isset($db_config)) {
			$db_path = $db_config->db_path;
		}
		else if (empty($db_path) && !isset($db_config)) {
			$db_path = MPM_MIGRATION_FILE_PATH;
		}
		if (substr($db_path, strlen($db_path) - 1, 1) != '/') {
			$db_path .= '/';
		}

		$method = (int)$method;

		/*
		if (file_exists($db_path . 'schema.php')) {
			echo "\nPerform build of database after initialization (builds schema\nand runs all existing migrations) [y/N]: ";
			$do_build = fgets(STDIN);
			$do_build = trim($do_build);
			$doBuild = false;
			if (strcasecmp(substr($do_build, 0, 1), 'y') == 0) {
				$doBuild = true;
			}
		}
		*/

		$file = '<?php' . "\n\n";
		$file .= '$db_config = (object) array();' . "\n";
		$file .= '$db_config->host = ' . "'" . $host . "';" . "\n";
		$file .= '$db_config->port = ' . "'" . $port . "';" . "\n";
		$file .= '$db_config->user = ' . "'" . $user . "';" . "\n";
		$file .= '$db_config->pass = ' . "'" . $pass . "';" . "\n";
		$file .= '$db_config->name = ' . "'" . $dbname . "';" . "\n";
		$file .= '$db_config->db_path = ' . "'" . $db_path . "';" . "\n";
		$file .= '$db_config->method = ' . $method . ";" . "\n";
		$file .= '$db_config->migrations_table = ' . "'" . $migrations_table . "';" . "\n";
		$file .= "\n?>";

		if (file_exists(MPM_CONFIG_FILE_PATH . '/db_migration_config.php')) {
			unlink(MPM_CONFIG_FILE_PATH . '/db_migration_config.php');
		}

		$fp = fopen(MPM_CONFIG_FILE_PATH . '/db_migration_config.php', "w");
		if ($fp == false) {
			echo "\nUnable to write to file.  Initialization failed!\n\n";
			exit;
		}
		$success = fwrite($fp, $file);
		if ($success == false) {
			echo "\nUnable to write to file.  Initialization failed!\n\n";
			exit;
		}
		fclose($fp);

		echo "\nConfiguration saved...";
	}

	/**
	 * @param $migrations_table
	 */
	public function init_migration_db_table($migrations_table)
	{
		echo "\nlooking for existing migrations table... ";

		try {
			if (false === MpmDbHelper::checkForDbTable()) {
				echo "not found.\n";
				echo "Creating migrations table... ";
				$sql1 = "CREATE TABLE IF NOT EXISTS `{$migrations_table}` ( `id` INT(11) NOT NULL AUTO_INCREMENT, `timestamp` DATETIME NOT NULL, `active` TINYINT(1) NOT NULL DEFAULT 0, `is_current` TINYINT(1) NOT NULL DEFAULT 0, PRIMARY KEY ( `id` ) ) ENGINE=InnoDB";
				$sql2 = "CREATE UNIQUE INDEX `TIMESTAMP_INDEX` ON `{$migrations_table}` ( `timestamp` )";

				if (MpmDbHelper::getMethod() == MPM_METHOD_PDO) {
					$pdo = MpmDbHelper::getDbObj();
					$pdo->beginTransaction();
					try {
						$pdo->internal_exec($sql1);
						$pdo->internal_exec($sql2);
					}
					catch (Exception $e) {
						$pdo->rollback();
						echo "failure!\n\n" . 'Unable to create required ' . $migrations_table . ' table:' . $e->getMessage();
						echo "\n\n";
						exit;
					}
					$pdo->commit();
				}
				else {
					$mysqli = MpmDbHelper::getDbObj();
					$mysqli->internal_exec($sql1);
					if ($mysqli->errno) {
						echo "failure!\n\n" . 'Unable to create required ' . $migrations_table . ' table:' . $mysqli->error;
						echo "\n\n";
						exit;
					}
					$mysqli->internal_exec($sql2);
					if ($mysqli->errno) {
						echo "failure!\n\n" . 'Unable to create required ' . $migrations_table . ' table:' . $mysqli->error;
						echo "\n\n";
						exit;
					}
				}
				echo "done.\n\n";
			}
			else {
				echo "found.\n\n";
			}

		}
		catch (Exception $e) {
			echo "failure!\n\nUnable to complete initialization: " . $e->getMessage() . "\n\n";
			echo "Check your database settings and re-run init.\n\n";
			exit;
		}
	}

	/**
	 * Displays the help page for this controller.
	 *
	 * @uses MpmCommandLineWriter::getInstance()
	 * @uses MpmCommandLineWriter::addText()
	 * @uses MpmCommandLineWriter::write()
	 *
	 * @return void
	 */
	public function displayHelp()
	{
		$obj = MpmCommandLineWriter::getInstance();
		$obj->addText('./migrate.php init');
		$obj->addText(' ');
		$obj->addText('This command is used to initialize the migration system for use with your particular deployment.  After you have modified the /config/db.php configuration file appropriately, you should run this command to setup the initial tracking schema and add your username to the migraiton archive.');
		$obj->addText(' ');
		$obj->addText('Example:');
		$obj->addText('./migrate.php init', 4);
		$obj->write();
	}

}

?>
