<?php
class Database {

	// connection
	protected static $_pdo = false;
	protected static $statement;
	protected static $fieldsCache;


	static public function config($host, $username, $password, $dbname, $dsn = 'mysql')
	{
		if( ! static::$_pdo ) {
			try {
				static::$_pdo = new PDO(
					$dsn.':dbname='.$dbname.';host='.$host, $username, $password,
					[
						PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
						PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
						PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
					]
				);
			} catch (Exception $e) {
				die($e->getMessage());
			}
		}
	}

	public static function execute($query, $parameters = array()) {
		
		self::$statement = static::$_pdo->prepare($query);
		foreach ($parameters as $key => &$param) {
			if (is_null($param)) {
				$type = PDO::PARAM_NULL;
			} else if (is_bool($param)) {
				$type = PDO::PARAM_BOOL;
			} else if (is_int($param)) {
				$type = PDO::PARAM_INT;
			} else {
				$type = PDO::PARAM_STR;
			}

			self::$statement->bindParam( is_int($key) ? ++$key : $key, $param, $type );
		}
		try {
			$q = self::$statement->execute();
		} catch (Exception $e) {
			die($e->getMessage());
		}
		return self::$statement;
	}

	public static function lastInsertId()
	{
		return static::$_pdo->lastInsertId();
	}

	public function getFields($table_name)
	{
		if ( ! isset(static::$fieldsCache[$table_name]) ) {
			
			$query = 'DESCRIBE ' . $table_name;
			static::$fieldsCache[$table_name] = static::execute($query)->fetchAll( PDO::FETCH_COLUMN );
		}
		return static::$fieldsCache[$table_name];
	}
	
}