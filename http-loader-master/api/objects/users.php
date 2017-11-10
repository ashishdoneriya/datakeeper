<?php
	class User {

		private $conn;
		private $tableName = "users";
		public $id;
		public $name;
		public $email;
		public $password;

		function __construct($db) {
			$this->conn = $db;
		}

		public function fetchUserInfo() {
			$query = "select userId, name from $this->tableName where email='$this->email' and password='$this->password'";
			$rows = $this->conn->query($query);
			if(count($rows) > 0) {
				$row = $rows->fetch();
				$this->id = $row["userId"];
				$this->name = $row["name"];
			} else {
				$this->id = null;
				$this->name = null;
			}
		}

		public function register() {
			$this->name=htmlspecialchars(strip_tags($this->name));
			$this->email=htmlspecialchars(strip_tags($this->email));
			$this->password=htmlspecialchars(strip_tags($this->password));

			$query = "insert into $this->tableName (name, email, password) value ('$this->name', '$this->email', '$this->password')";
			$this->conn->query($query);
		}

		public function updataPassword() {
			$this->id=htmlspecialchars(strip_tags($this->id));
			$this->password=htmlspecialchars(strip_tags($this->password));

			$query = "update $this->table set password=$password where userId=$this->id";
			$this->conn->query($query);
		}
	}
?>