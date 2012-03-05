<?php

$location = "localhost";
	$username = "familymath_miam";
	$password = "w3bUszJfTeYKPTt8";
	$database = "goroadtrip";

	$conn = mysql_connect("$location", "$username", "$password");
	if (!$conn) die ("Could not connect MySQL");
	mysql_select_db($database, $conn) or die ("Could not open database");

	$category = $_POST['category'];
	//GET methods
//	$sessionNum = $_GET['sid'];
//	$name = $_GET['name'];
	//POST methods
	switch($category) {
		case "addplayer":
			addPlayerToDB($_POST['name'], $_POST['sid']);
			break;
		case "adduser":
			addUserToDB($_POST['firstname'], $_POST['lastname']);
			break;
		case "addguess":
			addGuessToDB($_POST['name'], $_POST['sid'], $_POST['prob'], $_POST['guess'], $_POST['answer']);
			break;
		case "updatescore":
			add2ScoreInDB($_POST['name'], $_POST['points'], $_POST['sid']);
			break;
		case "createsession":
			createSessionInDB($_POST['uid']);
			break;
		case "getuser":
			echo getUserID($_POST['firstname'], $_POST['lastname']);
			break;
		default:
			die("Error: The category does not exist");
	}
//	echo "Success!";	
	
function addUserToDB($firstname, $lastname) {
	if(!isWordOrNum($firstname) || !isWordOrNum($lastname)) {
		die("first or last name must only contain letters or spaces");
	}
	$sessionNum = $_SESSION['sessionNum'];
	$totalname = convertToUsername($firstname, $lastname);
	// This is from addPlayer....
//	echo $totalname;
	$result = mysql_query("insert into users(`firstname`, `lastname`, `username`) values('$firstname', '$lastname', '$totalname')");
	
	if(!$result) {
		die("We could not insert '$firstname','$lastname' into the database, most likely that name already exists.  Please use a different name.");
	}
	returnID();
}

function createSessionInDB($userid) {
	if(!is_numeric($userid)) {
		die("invalid userid '$userid' specified");
	}
	$result = mysql_query("insert into session() values()");
	if($result) {
		$result = mysql_query("select last_insert_id()");
		$result = mysql_fetch_array($result, MYSQL_BOTH);
		$sessionNum = $result[0];
	}else{
		die("could not insert into database");
	}
	/* grab the name of the user */
	$user = mysql_query("select firstname from `users` where `userid` = $userid");
	$r = mysql_fetch_array($user);
	/* use that to insert a new record into the db, with the name and the current session from up top */
	$result = mysql_query("insert into players(`sessionid`, `name`, `userid`) values($sessionNum, '$r[0]', $userid)");
	$_SESSION['sessionNum'] = $sessionNum;
	echo "$sessionNum";
}

function addPlayerToDB($name, $sessionID) {
	if(!isWordOrNum($name) || !is_numeric($sessionID)) {
		die("invalid playerName, '$name', and/or invalid sessionID, '$sessionID'");
	}
//	$sessionNum = $_SESSION['sessionNum'];
	$sessionNum = $sessionID;
	$result = mysql_query("insert into players(`sessionid`, `name`) values($sessionNum, '$name')");
	if(!$result) {
		die("could not insert new player into DB");
	}
	returnID();
}

function addGuessToDB($playerName, $sessionID, $problem, $guess, $answer){
	if(!isWord($playerName) || !is_numeric($sessionID)) {
		die("invalid name and/or session ID passsed into addGuess");
	}
	$problem = mysql_real_escape_string($problem);
	$guess = mysql_real_escape_string($guess);
	$answer = mysql_real_escape_string($answer);
	$playerID = getPlayerID($playerName, $sessionID);
	$result = mysql_query("INSERT INTO `guesses` (`problem`,`playerid`,`guess`,`answer`) VALUES ('$problem', $playerID, '$guess', '$answer')");
	if(!$result) {
		die("could not insert guess into DB");
	}
	returnID();
}

function add2ScoreInDB($playerName, $points, $sessionID) {
	if(!isWord($playerName) || !is_numeric($points) || !is_numeric($sessionID)) {
		die("invalid name, points, and/or session");
	}
	//$sessionID = $_SESSION['sessionNum'];
	$result = mysql_query("UPDATE players SET points = points + $points WHERE `name` = '$playerName' AND `sessionid` = $sessionID");	
	if (mysql_affected_rows() < 1) {
		echo "-1";
	}
	$result = mysql_query("SELECT points FROM players WHERE `name` = '$playerName' AND `sessionid` = $sessionID");
	$tPoints = mysql_fetch_array($result);
	echo "$tPoints[0]";
}

function returnID() {
	$r = mysql_query("select last_insert_id()");
	$id = mysql_fetch_array($r);
	echo "$id[0]";
}

function determineSQLResult($result) {
	if(mysql_num_rows($result) < 1) {
		return -1;
	}
	$r = mysql_fetch_array($result, MYSQL_BOTH);
	return $r[0];
}

function getPlayerID($playerName, $sessionID) {
	if(!isWord($playerName) || !is_numeric($sessionID)){
		die("invalid playerName and/or invalid sessionID");
	}
	$result = mysql_query("SELECT `playerid` FROM `players` WHERE `name` = '$playerName' AND `sessionid` = $sessionID");
	return determineSQLResult($result);
}

function convertToUsername($firstname, $lastname) {
	$lname = strtolower($firstname.$lastname);
	$lname = str_replace(' ', '', $lname);
	return substr($lname, 0, 15);
}

function getUserID($firstname, $lastname) {
	if(!isWord($firstname) || !isWord($lastname)) {
		die("first or last name must only contain letters or spaces");
	}
	$totalname = convertToUsername($firstname, $lastname);
//	return $totalname;
	$result = mysql_query("SELECT `userid` FROM `users` WHERE `username` = '$totalname'");
	return determineSQLResult($result);
}

function isWord($str){
    return preg_match('/^[a-z\s]+$/i',$str);
}

function isWordOrNum($str){
    return preg_match('/^[0-9a-z\s]+$/i',$str);
}

?>