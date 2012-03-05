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

	$category = $_POST['category'];
	//GET methods
//	$sessionNum = $_GET['sid'];
//	$name = $_GET['name'];
	//POST methods
	switch($category) {
		case "addplayer":
			addPlayerToDB($db, $_POST['name'], $_POST['sid'], $_POST['avatar']);
			break;
		case "adduser":
			addUserToDB($db, $_POST['firstname'], $_POST['lastname']);
			break;
		case "addguess":
			addGuessToDB($db, $_POST['name'], $_POST['sid'], $_POST['prob'], $_POST['guess'], $_POST['answer']);
			break;
		case "updatescore":
			add2ScoreInDB($db, $_POST['name'], $_POST['points'], $_POST['sid']);
			break;
		case "createsession":
			createSessionInDB($db, $_POST['uid']);
			break;
		case "getuser":
			echo getUserID($db, $_POST['firstname'], $_POST['lastname']);
			break;
		case "updateavatar":
			updateAvatarInDB($db, $_POST['name'], $_POST['sid'], $_POST['avatar'], $_POST['uid']);
			break;
		default:
			die("Error: The category:'$category' does not exist");
	}
//	echo "Success!";	
	
function addUserToDB($dbhandle, $firstname, $lastname) {
	if(!isWordOrNum($firstname) || !isWordOrNum($lastname)) {
		die("first or last name must only contain letters or spaces");
	}
	$sessionNum = $_SESSION['sessionNum'];
	$totalname = convertToUsername($firstname, $lastname);
	// This is from addPlayer....
//	echo $totalname;
	try {
		$result = $dbhandle->prepare("insert into users(`firstname`, `lastname`, `username`, `password`) values(?, ?, ?, '')");
		$result->bindParam(1, $firstname);
		$result->bindParam(2, $lastname);
		$result->bindParam(3, $totalname);
		$result->execute();
	}catch(PDOException $e) {
		echo $e->getMessage();
		die("We could not insert '$firstname','$lastname' into the database, most likely that name already exists.  Please use a different name.");
	}
	returnID($dbhandle);
}

function createSessionInDB($dbhandle, $userid) {
	if(!is_numeric($userid)) {
		die("invalid userid '$userid' specified");
	}
	$result = $dbhandle->prepare("insert into session(`starttime`, `endtime`) values(datetime(), 0)");
	$result->execute();
	$sessionNum = $dbhandle->lastInsertId();
	/* grab the name of the user */
	$user = $dbhandle->prepare("select firstname from `users` where `userid` = ?");
	$user->execute(array($userid));
	$r = $user->fetchAll();
	/* use that to insert a new record into the db, with the name and the current session from up top */
	$result = $dbhandle->prepare("INSERT INTO players(`sessionid`, `name`, `userid`, `points`, `avatar`) VALUES (?, ?, ?, 0, '')");
	$result->bindParam(1, $sessionNum);
	$result->bindParam(2, $r[0][0]);
	$result->bindParam(3, $userid);
	$result->execute();
	$_SESSION['sessionNum'] = $sessionNum;
	echo $sessionNum;
}

function addPlayerToDB($dbhandle, $name, $sessionID, $avatar) {
	if(!isWordOrNum($name) || !is_numeric($sessionID)) {
		die("invalid playerName, '$name', and/or invalid sessionID, '$sessionID'");
	}
//	$sessionNum = $_SESSION['sessionNum'];
	$sessionNum = $sessionID;
	try {
		$result = $dbhandle->prepare("INSERT INTO players(`sessionid`, `name`, `points`, `avatar`) VALUES (?, ?, 0, ?)");
		$result->bindParam(1, $sessionNum);
		$result->bindParam(2, $name);
		$result->bindParam(3, $avatar);
		$result->execute();
	}catch(PDOException $e) {
		echo $e->getMessage();
		die("could not insert new player into DB");
	}
	returnID($dbhandle);
}

function executeSQL($dbhandle, $sqlStat) {
	$result = $dbhandle->prepare($sqlStat);
	$result->execute();
	return $result;
}

function addGuessToDB($dbhandle, $playerName, $sessionID, $problem, $guess, $answer){
	if(!isWordOrNum($playerName) || !is_numeric($sessionID)) {
		die("invalid name and/or session ID passsed into addGuess");
	}
	$problem = sqlite_escape_string($problem);
	$guess = sqlite_escape_string($guess);
	$answer = sqlite_escape_string($answer);
	$playerID = getPlayerID($dbhandle, $playerName, $sessionID);
	try {
		$result = $dbhandle->prepare("INSERT INTO `guesses` (`problem`,`playerid`,`guess`,`answer`, `timestamp`) VALUES (?, ?, ?, ?, datetime())");
		$result->bindParam(1, $problem);
		$result->bindParam(2, $playerID);
		$result->bindParam(3, $guess);
		$result->bindParam(4, $answer);
		$result->execute();
		print_r($result->errorInfo());
	}catch(PDOException $e) {
		echo $e->getMessage();
		die("could not insert guess into DB");
	}
	returnID($dbhandle);
}

function add2ScoreInDB($dbhandle, $playerName, $points, $sessionID) {
	if(!isWordOrNum($playerName) || !is_numeric($points) || !is_numeric($sessionID)) {
		die("invalid name '$playerName', points '$points', and/or session '$sessionID'");
	}
	//$sessionID = $_SESSION['sessionNum'];
	$result = $dbhandle->prepare("UPDATE players SET points = points + ? WHERE `name` = ? AND `sessionid` = ?");	
//	$result->bindParam(1, $points);
//	$result->bindParam(2, $playerName);
//	$result->bindParam(3, $sessionID);
	$result->execute(array($points, $playerName, $sessionID));
	print_r($result->errorInfo());
	$result = $dbhandle->prepare("SELECT points FROM players WHERE `name` = ? AND `sessionid` = ?");
//	$result->bindParam(1, $playerName);
//	$result->bindParam(2, $sessionID);
	$result->execute(array($playerName, $sessionID));
	echo determineSQLResult($dbhandle, $result);
}

function updateAvatarInDB($dbhandle, $name, $sessionID, $avatar, $userid) {
	if(!isWord($name) || !is_numeric($sessionID)) {
		die("invalide playername and/or invalid session in update");
	}
	$result = $dbhandle->prepare("UPDATE players SET avatar = ? WHERE `name` = ? AND `sessionid` = ?");	
	$result->execute(array("bunny.png", "os", 10));
//	$result->execute(array($avatar, $name, $sessionID));
//	echo determineSQLResult($dbhandle, $result); 
	echo $result->queryString;
	print_r($result->errorInfo());
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

function getPlayerID($dbhandle, $playerName, $sessionID) {
	if(!isWordOrNum($playerName) || !is_numeric($sessionID)){
		die("invalid playerName and/or invalid sessionID in getPlayerID");
	}
	$result = $dbhandle->prepare("SELECT `playerid` FROM `players` WHERE `name` = ? AND `sessionid` = ?");
	$result->execute(array($playerName, $sessionID));
	return determineSQLResult($dbhandle, $result);
}

function convertToUsername($firstname, $lastname) {
	$lname = strtolower($firstname.$lastname);
	$lname = str_replace(' ', '', $lname);
	return substr($lname, 0, 15);
}

function getUserID($dbhandle, $firstname, $lastname) {
	if(!isWord($firstname) || !isWord($lastname)) {
		die("getUserID: first or last name must only contain letters or spaces");
	}
	$totalname = convertToUsername($firstname, $lastname);
	$result = $dbhandle->prepare("SELECT userid FROM users WHERE username = ?");
	$result->execute(array($totalname));
//debug	$result = $dbhandle->prepare("SELECT username FROM users WHERE userid = ?");
//	$result->execute(array(1));
//works	$result = $dbhandle->exec("insert into users (firstname, lastname, username, password) VALUES ('carl', 'weathers', 'carlweathers', '')");
	return determineSQLResult($dbhandle, $result);
}

function isWord($str){
    return preg_match('/^[a-z\s]+$/i',$str);
}

function isWordOrNum($str){
    return preg_match('/^[0-9a-z\s]+$/i',$str);
}

?>
