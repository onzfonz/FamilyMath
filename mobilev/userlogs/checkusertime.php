<?php

class TimeLog {
	public $totalSecs;
	public $intervalArr;
	public $curTime;
	public $prevTime;
	public $minTimeout;
	public $startTime;
	
	public function __construct($minT) {
		$this->minTimeout = $minT;	
		$this->totalSecs = 0;
		$this->intervalArr = array();
		$this->prevTime = null;
	}
	
	public function addToTimeLog($newTime) {
		//echo $newTime;
		$this->curTime = strtotime($newTime);
		if($this->prevTime != null) {
			$this->totalSecs += $this->timeDiff($newTime);
		}else{
			$this->startTime = $newTime;
		}
		$this->prevTime = $this->curTime;
	}
	
	public function getTotalSeconds() {
		return $this->totalSecs;
	}
	
	public function getTotalTime() {
		return $this->secsToTime($this->getTotalSeconds());
	}
	
	private function secsToTime($secs) {
		$seconds = $secs % 60;
		$minutes = (($secs-$seconds)/60)%60;
		$hours = (($secs-$seconds)-($minutes*60))/3600;
		return $hours." Hours, ".$minutes." Minutes, ".$seconds.", Seconds";
	}
	
	private function timeDiff($newTime) {
		$secsDiff = $this->curTime - $this->prevTime;
		if($secsDiff > $this->minTimeout*60) {
			//echo $this->startTime." till ".$this->prevTime.".<br>";
			$prevtstamp = date('Y-m-d H:i:s', $this->prevTime);
			$this->intervalArr[] = $this->startTime." till ".$prevtstamp." - ".$this->secsToTime($this->prevTime - strtotime($this->startTime));
			$this->startTime = $newTime;
			return 0;
		}else if($this->startTime == null) {
			$this->startTime = $newTime;
		}
		return $secsDiff;
	}
	
	public function __toString() {
		$numItems = count($this->intervalArr);
		$str = $numItems;
		for($i = 0; $i < $numItems; $i++) {
			$str = $str."|".$this->intervalArr[$i];
		}
		return $this->getTotalTime()."|".$str;
	}
}

$location = "localhost";
	$username = "familymath_miam";
	$password = "w3bUszJfTeYKPTt8";
	//$database = "/afs/ir.stanford.edu/users/o/j/ojimenez/cgi-bin/grt/sqlite/goroadtrip";
	$database = "../sqlite/goroadtrip";
	
	$category = $_POST['cat'];
	$dbname = $_POST['dbname'];
//	$conn = mysql_connect("$location", "$username", "$password");
//	if (!$conn) die ("Could not connect MySQL");
//	mysql_select_db($database, $conn) or die ("Could not open database");

/*	$sqliteerror = "could not connect to sqlite db";
	$db = sqlite_open($database, 0666, $sqliteerror);
	if(!$db) die ($sqliteerror);*/
	try {
	//	$db = new PDO('sqlite:/afs/ir.stanford.edu/users/o/j/ojimenez/cgi-bin/grt/sqlite/goroadtrip');
		if(strcmp($dbname, "goroadtrip") == 0) {
			$db = new PDO('sqlite:../../sqlite/goroadtrip');
			$isroadtrip = true;
		}else{
			$db = new PDO('sqlite:../../../hkc/sqlite/homework');
			$isroadtrip = false;
		}
	} catch (Exception $e) {
		die("could not connect to sqlite");
	}

	
	switch($category) {
		case "userlog":
			getTimeUserUsedApp($db, $isroadtrip, stripslashes($_POST['fname']), stripslashes($_POST['lname']));
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

function getTimeUserUsedApp($dbhandle, $isroadtrip, $fname, $lname) {
    $uid = getUserID($dbhandle, $fname, $lname);
    if(!is_numeric($uid)) {
        die("invalid userid '$uid' in getTimeUserUsedApp");
    }
	if($isroadtrip) {
		$querystring = "SELECT timestamp FROM navigation WHERE `uid` = ? ORDER BY timestamp ASC";
		$mins = 15;
	}else{
		$querystring = "SELECT starttime FROM navigation WHERE `uid` = ? ORDER BY starttime ASC";
		$mins = 45;
	}
    $result = $dbhandle->prepare($querystring);
    $result->execute(array($uid));
//    $result->execute(array(5));
    echo buildTimeSpent($dbhandle, $result, $uid, $mins);
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
	return determineSQLResult($dbhandle, $result);
}

function buildTimeSpent($dbhandle, $result, $uid, $minTimeout) {
	$r = $result->fetchAll();
	if(count($r) < 1) {
		if($uid==-1) {
			return $uid.'|No Time Has Been Spent so far by that user';
		}else{
			return '-1|That user was not found';
		}
	}
	$timespent = calculateTimeSpent($r, $minTimeout);
	return $uid."|".$timespent;
}

function calculateTimeSpent($arr, $minTimeout) {
	$timeLog = createTimeLogForUser($arr, $minTimeout);
	return $timeLog;
}

function createTimeLogForUser($arr, $minTimeout) {
	$timeLog = new TimeLog($minTimeout);
	for($i = 0; $i < count($arr); $i++) {
		$timeLog->addToTimeLog($arr[$i][0]);	
	}
	return $timeLog;
}

function isWord($str){
    return preg_match('/^[a-z\s]+$/i',$str);
}

function isWordOrNum($str){
    return preg_match('/^[0-9a-z\s]+$/i',$str);
}

?>
