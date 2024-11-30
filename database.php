<?php
    // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> database.php
    // PHP code to access the database.
	class Database {
		//static methods
		//Use the information of your database
		private static $dbName = ''; // Example: private static $dbName = 'myDB';
		private static $dbHost = ''; // Example: private static $dbHost = 'localhost';
		private static $dbUsername = ''; // Example: private static $dbUsername = 'myUserName';
		private static $dbUserPassword = ''; // // Example: private static $dbUserPassword = 'myPassword';
		 
		private static $cont  = null;
		 
		public function __construct() {
			// unable to create an instance.
			die('Init function is not allowed');
		}
		 
		public static function connect() {
			// One connection through whole application
			if ( null == self::$cont ) {     
				try {
					// using PHP Databaase Object
					self::$cont =  new PDO( "mysql:host=".self::$dbHost.";"."dbname=".self::$dbName, self::$dbUsername, self::$dbUserPassword); 
				} catch(PDOException $e) { //builtin class
				die($e->getMessage()); 
				}
			}
			return self::$cont; //returns the PDO
		}
		 
		public static function disconnect() {
			self::$cont = null;
		}
	}
