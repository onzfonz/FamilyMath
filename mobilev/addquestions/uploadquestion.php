<?php

$location = "localhost";
	$username = "familymath_miam";
	$password = "w3bUszJfTeYKPTt8";
	//$database = "/afs/ir.stanford.edu/users/o/j/ojimenez/cgi-bin/grt/sqlite/goroadtrip";
	$database = "../sqlite/goroadtrip";
	
//	$conn = mysql_connect("$location", "$username", "$password");
//	if (!$conn) die ("Could not connect MySQL");
//	mysql_select_db($database, $conn) or die ("Could not open database");

/*	$sqliteerror = "could not connect to sqlite db";
	$db = sqlite_open($database, 0666, $sqliteerror);
	if(!$db) die ($sqliteerror);*/
	try {
	//	$db = new PDO('sqlite:/afs/ir.stanford.edu/users/o/j/ojimenez/cgi-bin/grt/sqlite/goroadtrip');
		$db = new PDO('sqlite:../../sqlite/goroadtrip');
	} catch (Exception $e) {
		die("could not connect to sqlite");
	}

	$category = $_POST['cat'];
	$tablename = $_POST['tablename'];
	switch($category) {
		case "add":
			addQuestionToDB($db, $tablename, stripslashes($_POST['q']), stripslashes($_POST['op1']), $op2 = stripslashes($_POST['op2']), stripslashes($_POST['op3']), stripslashes($_POST['op4']), stripslashes($_POST['answer']), $_POST['qLevel']);
			break;
		case "browse":
			getSpecificQuestionFromDB($db, $tablename, stripslashes($_POST['qid']));
			break;
		case "edit":
			editQuestionInDB($db, $tablename, stripslashes($_POST['qid']), stripslashes($_POST['q']), stripslashes($_POST['op1']), $op2 = stripslashes($_POST['op2']), stripslashes($_POST['op3']), stripslashes($_POST['op4']), stripslashes($_POST['answer']), $_POST['qLevel']);
			break;
		default:
			die("Error: the category:'$category' does not exist");
	}
	//header("Location: http://www.stanford.edu/~ojimenez/cgi-bin/grt/mobilev/addquestions/");
	//GET methods
//	$sessionNum = $_GET['sid'];
//	$name = $_GET['name'];
	//POST methods
//	echo "Success!";	
	
function addQuestionToDB($dbhandle, $table, $question, $op1, $op2, $op3, $op4, $answer, $level) {
	// This is from addPlayer....
//	echo $totalname;
	try {
		$querystring = "insert into ".$table."(`question`, `optone`, `opttwo`, `optthree`, `optfour`, `answer`, `level`) values(?, ?, ?, ?, ?, ?, ?)";
		$result = $dbhandle->prepare($querystring);
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

function editQuestionInDB($dbhandle, $table, $qid, $question, $op1, $op2, $op3, $op4, $answer, $level) {
	// This is from addPlayer....
//	echo $totalname;
	try {
		$querystring = "UPDATE ".$table." SET `question`=?, `optone`=?, `opttwo`=?, `optthree`=?, `optfour`=?, `answer`=?, `level`=? WHERE `qid`=?";
		$result = $dbhandle->prepare($querystring);
		$result->execute(array($question, $op1, $op2, $op3, $op4, $answer, $level, $qid));
//		print_r($result->errorInfo());
		$qid = $qid + 1;
		getSpecificQuestionFromDB($dbhandle, $table, $qid);
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

function getQuestionFromDB($dbhandle, $table, $level) {
    if(!is_numeric($level)) {
        die("invalid question category '$level' in getQuestion");
    }
    $querystring = "SELECT * FROM ".$table." WHERE `level` = ?";
    $result = $dbhandle->prepare($querystring);
    $result->execute(array($level));
//    $result->execute(array(5));
    echo buildRandomQuestionString($dbhandle, $result);
}

function getSpecificQuestionFromDB($dbhandle, $table, $qid) {
	if(!is_numeric($qid)) {
		die("invalid questiond id '$qid' in getSpecificQuestion");
	}
	$querystring = "SELECT * FROM ".$table." WHERE `qid` = ?";
	$result = $dbhandle->prepare($querystring);
    $result->execute(array($qid));
    echo buildQuestionString($dbhandle, $result, 0);
}

function buildRandomQuestionString($dbhandle, $result) {
    $r = $result->fetchAll();
	$num = rand(0, count($r) - 1);
	return buildQuestionString($dbhandle, $result, $num);
}

function buildQuestionString($dbhandle, $result, $num) {
	$r = $result->fetchAll();
	if(count($r) < 1) {
		return -1;
	}
//	if($result->rowCount() < 1) {
//		return -1;
//	}
//	$num = rand(0, count($r)-1);
//	$num = count($r)-1;
	return $r[$num][0].'|'.$r[$num][1].'|'.$r[$num][2].'|'.$r[$num][3].'|'.$r[$num][4].'|'.$r[$num][5].'|'.$r[$num][6].'|'.$r[$num][7];
//	return $r[$num][0].'|'.stripslashes($r[$num][1]).'|'.stripslashes($r[$num][2]).'|'.stripslashes($r[$num][3]).'|'.stripslashes($r[$num][4]).'|'.stripslashes($r[$num][5]).'|'.stripslashes($r[$num][6]).'|'.$r[$num][7];
}

function isWord($str){
    return preg_match('/^[a-z\s]+$/i',$str);
}

function isWordOrNum($str){
    return preg_match('/^[0-9a-z\s]+$/i',$str);
}

?>
