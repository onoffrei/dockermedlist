<?php
class Cs_db{
	static $conn = null;
	public function conn_get(){
		if (self::$conn == null){
			self::$conn = new mysqli(cs_db_host, cs_db_user, cs_db_password, cs_db_name); 
			//var_dump(self::$conn);
			if (!self::$conn){ 
				echo "dbe0 " . self::$conn->connect_error;
				throw new Exception('dbe0 Could not connect to database server');
				self::$conn = null; 
				exit('error db(Could not connect to database server)'); 
			}
			if (self::$conn->connect_errno > 0){
				echo "dbe1 " . self::$conn->connect_error;
				throw new Exception("dbe1 " . self::$conn->connect_error);
				self::$conn = null; 
				exit('error db(Could not connect to database server)'); 
			}
			self::$conn->query('SET NAMES \'' . cs_db_charset . '\' COLLATE \'utf8_general_ci\''); 
			if (!self::$conn->set_charset(cs_db_charset)){
				printf("dbe2 Error loading character set utf8: %s\n", self::$conn->error);
				throw new Exception("dbe1 " . self::$conn->connect_error);
				self::$conn = null; 
				exit('error db(Could not connect to database server)'); 
			}
		}
		return self::$conn;
	}
} 
$cs_db = new Cs_db();
$GLOBALS['cs_db_conn'] = $cs_db->conn_get();
?>