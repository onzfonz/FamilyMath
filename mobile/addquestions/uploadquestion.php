<?php

$location = "localhost";
	$username = "familymath_miam";
	$password = "w3bUszJfTeYKPTt8";
	$database = "/afs/ir.stanford.edu/users/o/j/ojimenez/cgi-bin/grt/sqlite/goroadtrip";

//	$conn = mysql_connect("$location", "$username", "$password");
//	if (!$conn) die ("Could not connect MySQL");
//	mysql_select_db($database, $conn) or die ("Could not open database");

/*	$sqliteerror = "could not connect to sqlite db";
	$db = sqlite_open($database, 0666, $sqliteerror);
	if(!$db) die ($sqliteerror);*/
	try {
		$db = new PDO('sqlite:/afs/ir.stanford.edu/users/o/j/ojimenez/cgi-bin/grt/sqlite/goroadtrip');
	} catch (Exception $e) {
		die("could not connect to sqlite");
	}

	$question = $_POST['q'];
	$op1 = $_POST['op1'];
	$op2 = $_POST['op2'];
	$op3 = $_POST['op3'];
	$op4 = $_POST['op4'];
	$answer = $_POST['answer'];
	$level = $_POST['qLevel'];
	addQuestionToDB($db, $question, $op1, $op2, $op3, $op4, $answer, $level);
	header("Location: http://www.stanford.edu/~ojimenez/cgi-bin/grt/mobile/addquestions/");
	//GET methods
//	$sessionNum = $_GET['sid'];
//	$name = $_GET['name'];
	//POST methods
//	echo "Success!";	
	
function addQuestionToDB($dbhandle, $question, $op1, $op2, $op3, $op4, $answer, $level) {
	// This is from addPlayer....
//	echo $totalname;
	try {
		$result = $dbhandle->prepare("insert into questions(`question`, `optone`, `opttwo`, `optthree`, `optfour`, `answer`, `level`) values(?, ?, ?, ?, ?, ?, ?)");
		$result->bindParam(1, $question);
		$result->bindParam(2, $op1);
		$result->bindParam(3, $op2);
		$result->bindParam(4, $op3);
		$result->bindParam(5, $op4);
		$result->bindParam(6, $answer);
		$result->bindParam(7, $level);
		$result->execute();
//		print_r($result->errorInfo());
	}catch(PDOException $e) {
		echo $e->getMessage();
		die("We could not insert '$question' into the database, time to contact Osvaldo!");
	}
//	returnID($dbhandle);
}

function executeSQL($dbhandle, $sqlStat) {
	$result = $dbhandle->prepare($sqlStat);
	$result->execute();
	return $result;
}

function returnID($dbhandle) {
	//echo "entering returnID";
	echo $dbhandle->lastInsertId();
}

function determineSQLResult($dbhandle, $result) {
	$r = $result->fetchAll();
	if(count($r) < 1) {
		return -1;
	}
//	if($result->rowCount() < 1) {
//		return -1;
//	}
	return $r[0][0];
}

function isWord($str){
    return preg_match('/^[a-z\s]+$/i',$str);
}

function isWordOrNum($str){
    return preg_match('/^[0-9a-z\s]+$/i',$str);
}

?>
