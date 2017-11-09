<?php
class Database {
	private $host = "localhost";
	private $port = "80";
	private $username = "root";
	private $password = "root";
	private $database = "vuejsui";

	public function getConnection() {
		$conn = null;
		try {
			$conn = new PDO("mysql:host=$this->host;port=$this->port;dbname=$this->database", $this->username, $this->password);
		} catch (PDOException $exception) {
			echo "Connection error: " . $exception->getMessage();
			return null;
		}
		return $conn;
	}
}
?>