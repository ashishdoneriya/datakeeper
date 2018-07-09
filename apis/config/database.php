<?php
class Database {
	private $host = "localhost";
	private $port = "3306";
	private $username = "root";
	private $password = "root";
	private $database = "datakeeper";

	public function getConnection() {
		$conn = null;
		try {
			$options = [
				PDO::ATTR_EMULATE_PREPARES   => false, // turn off emulation mode for "real" prepared statements
				PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, //turn on errors in the form of exceptions
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //make the default fetch be an associative array
			];
			$conn = new PDO("mysql:host=$this->host;port=$this->port;dbname=$this->database", $this->username, $this->password, $options);
		} catch (PDOException $exception) {
			echo "Connection error: " . $exception->getMessage();
			return null;
		}
		return $conn;
	}
}
?>
