/* ---------------------
 * Cookie functions
 * ---------------------*/
var NUM_DAYS_FOR_COOKIE = 365;

function setCookie(category,value,exdays) {
	var exdate=new Date();
	exdate.setDate(exdate.getDate() + exdays);
	var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
	document.cookie=category + "=" + c_value;
}

function getCookie(category) {
	var i,x,y,ARRcookies=document.cookie.split(";");
	for (i=0;i<ARRcookies.length;i++) {
		x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
		y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
		x=trim(x) //replace all leading and trailing spaces
		if (x==category) {
			    return unescape(y);
		}
	}
}

function setLongCookie(category, value) {
	setCookie(category, value, NUM_DAYS_FOR_COOKIE);
}

function destroyCookie(category) {
	setCookie(category, "", 0);
}

//Sample Cookie Usage
function checkCookie() {
	var username=getCookie("userid");
	if (username!=null && username!="") {
		alert("Welcome again " + username);
	} else {
		username=prompt("Please enter your name:","");
		if (username!=null && username!="") {
			setCookie("username",username,365);
		}
	}
}
