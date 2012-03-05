var RECORD_RADIO_PREFIX = "<input type='radio' name='";
var RECORD_DIV_PREFIX = "<div class='record' id='";
var RECORD_INPUT_PREFIX = "<input type='text' name='' value='' class='recordinput' id='";
var HTML_TAG_SUFFIX = "'>"
var DIV_END = "</div>"
var DIV_SUFFIX = HTML_TAG_SUFFIX+DIV_END;
var DIV_PREFIX = "<div id='";
var LINK_PREFIX = "<a href='";
var LINK_END_A = "</a>"; 
var LINK_CLASS = "' class='";
var BR_TAG = "<br>";
var BTN_PREFIX = "<input type='button' name='";
var BTN_VAL = "' value='";
var BTN_ONCLICK = "' onclick='";
var BTN_CLASS = "()' class='";
var BTN_ID = "' id='";

//-------------------------------------------------------------
// TOOLS
//-------------------------------------------------------------

function isInteger(s) {
  return (s.toString().search(/^-?[0-9]+$/) == 0);
}

function randomNum(low, high) {
    return Math.floor(Math.random()*(Math.abs(high-low)))+Math.min(low, high)
}

function randomNum(high) {
	return Math.floor(Math.random()*high);
}

function pause(millis) {
	var date = new Date();
	var curDate = null;

	curDate = new Date();
	while(curDate-date < millis);
}

function changeCssProperty(id, tag, property) {
	$("#"+id).css(tag, property);
}

function get(element) {
    return document.getElementById(element);
}

function trim(x) {
	return x.replace(/^\s+|\s+$/g,"");
}

function getTextInput(id) {
	return $("#"+id).val();
}

function setTextInput(id, txt) {
	$("#"+id).val(txt);
}

function getTextLabel(id) {
	return $("#"+id).text();
}

function changeLabelText(id, message) {
    $("#"+id).text(message);
	$("#"+id).show();
}

function createTextField(baseContId, newId) {
	$("#"+baseContId).append(RECORD_INPUT_PREFIX+newId+HTML_TAG_SUFFIX);
}
	
function createRadioOption(baseContId, newId, groupId) {
	$("#"+baseContId).append(RECORD_RADIO_PREFIX+groupId+RECORD_RADIO_MIDDLE+newId+RECORD_RADIO_SUFFIX);
}

function createDiv(id, newId) {
	$("#"+id).append(DIV_PREFIX+newId+DIV_SUFFIX);
}

function createDivWithClass(id, classId, newId) {
	$("#"+id).append(DIV_PREFIX+newId+"' class='"+classId+DIV_SUFFIX);
}

function createTextDivWithClass(baseid, linkid, text, clas) {
	$("#"+baseid).append(DIV_PREFIX+linkid+LINK_CLASS+clas+HTML_TAG_SUFFIX+text+DIV_END);
}

function createButton(baseid, n, val, clss, btnid, onclk) {
	$("#"+baseid).append(BTN_PREFIX+n+BTN_VAL+val+BTN_ONCLICK+onclk+BTN_CLASS+clss+BTN_ID+btnid+HTML_TAG_SUFFIX);
}

function createNewLine(baseid) {
	$("#"+baseid).append(BR_TAG);
}

function appendHTMLTextWithTag(baseid, tag, txt) {
	$("#"+baseid).append("<"+tag+">"+txt+"</"+tag+">");
}

function appendHTMLTag(baseid, tag) {
	$("#"+baseid).append("<"+tag+">");
}

function appendHTML(baseid, htmlcode) {
	$("#"+baseid).append(htmlcode);
}

function isDefined(name) {
	return !(typeof name == "undefined" || name == "undefined");
}

String.prototype.capitalize = function(){
	return this.replace(/(^|\s)([a-z])/g, function(m, p1, p2){
		return p1 + p2.toUpperCase();
	});
}

Date.prototype.dateString = function() {
	return (this.getMonth()+1) + "/" + this.getDate() + "/" + this.getFullYear();
}

Date.prototype.timeString = function() {
	var hours = this.getHours();
	var suffix = "am";
	if(hours >= 12) {
		suffix = "pm";
		hours = hours-12;
	}
	if(hours == 0) {
		hours = hours+12;
	}
	var min = Number(this.getMinutes());
	if(min < 10) {
		min = "0" + min;
	}
	return hours + ":" + min + suffix;
}

