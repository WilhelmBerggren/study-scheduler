<?php
/*
'genCal.php'
This file is a collection of functions relating to displaying a user's calendar in HTML.
TODO: 	leave database calls to whoever is calling these functions, as this file should not be executing code.
*/
	include_once '../scripts/DB.php';
	include_once '../algorithm/distribute.php';
	include_once '../scripts/importCal.php';

	date_default_timezone_set('UTC');
	function cmp_date($date1,$date2){ return cmp_date_val($date1) > cmp_date_val($date2); }
	function cmp_date_val($date) 	{ return substr($date,0,8).substr($date,9,4); }
	function cmp_day2($date1,$date2) { return intval(substr($date2,0,8)) - intval(substr($date1,0,8)); }
	function cmp_day($date1,$date2) { return date('j',(strtotime(substr($date2,0,8))-strtotime(substr($date1,0,8))))-1; }
	function pretty_time($date) 	{ return substr($date,9,2) . ":".substr($date,11,2); }
	function ugly_time($date) 		{ return substr($date,9,2) . substr($date,11,2); }
	function TimeToSec($time) {
		$sec = 0;
		foreach (array_reverse(explode(':', $time)) as $k => $v) $sec += pow(60, $k) * $v;
		return $sec*60;
	}

	$colors = array("event1", "event2", "event3");
	global $colors;
	$eventColor = array();
	global $eventColor;
	//begin database calls, which should not actually be in this function.
	global $f;
	$db = new DB();
	$result = null;
	if(session_id() == "") session_start();
	if(isset($_SESSION['uuid'])){
		$query = "SELECT CURRENT FROM calendar WHERE ID='".$_SESSION['uuid']."'";
		$result = $db -> select($query);
		//var_dump($result);
	}
	$current = (isset($result[0]['CURRENT'])) ? $result[0]['CURRENT'] : null;
	$f = $current;
	//end database calls.

	function collect($date_start, $date_end) {
		/*
		this function finds all events within the two given dates and returns
		an array with the format [week] = [day1,day2,...] = [[event1,event2,...],...]
		*/
		global $f;
		$file = json_decode($f);
		$events = array();
		if($file !== null) {
			foreach($file as $event) {
				if($event->DTSTART > $date_start
					&& $date_end > $event->DTEND && !$event->AVAILABLE)
					array_push($events,$event);
			}
		}
		$week = array();
		$weeklength = 7;
		for($i = 0; $i < $weeklength; $i++) {
			array_push($week,array());
		}
		foreach($events as $event) {
			//echo "<br>cmp: ".cmp_day($date_start, $event->DTSTART)." d_s: ".$date_start." e_d: ".$event->DTSTART." <br>";
			if (isset($week[cmp_day($date_start,$event->DTSTART)]) && is_array($week[cmp_day($date_start,$event->DTSTART)])) {
				array_push($week[cmp_day($date_start,$event->DTSTART)],$event);
			}
		}
		return $week;
	}


	function find_clash ($events)
	{
		$events2 = array();
		$arr = array();
		if($events)
		{
			array_push($arr, $events[0]);
			$end = $events[0]->DTEND;

			for($i = 1; $i<count($events); $i++)
			{
				if($events[$i]->DTSTART < $end)
				{
					if($i == (count($events) - 1))
					{
						array_push($arr, $events[$i]);
						array_push($events2, $arr);
					}
					else
					{
						array_push($arr, $events[$i]);
						if($events[$i]->DTEND > $end) {$end = $events[$i]->DTEND;}
					}
				}
				else
				{
					array_push($events2, $arr);
					$arr = array();

					if($i == (count($events) - 1))
					{
						array_push($events2, $arr);
						array_push($arr, $events[$i]);
					}
					else
					{
							array_push($arr, $events[$i]);
				   		$end = $events[$i]->DTEND;
				   }
				}

			}
		}
		return $events2;
	}


	function gen_event($events, $event1){
		/*
		This function takes an event (class event from importCal.php) and returns an HTML formatted event to be used in a calendar view.
		It also accepts the previous event, in which case it sets the margin-top of this event to be proportional to the time between the two.
		*/
	 	$html ="";
		$order   = array("\\r\\n", "\\n", "\\r");
		$replace = ' <br />';
		$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
		$divide=(60*60*24); /*seconds in a day*/

			for ($i = 0; $i<count($events); $i++)
			{
				$cl = check($events[$i]);
				$json = json_encode($events[$i]);
				$width = 100/(count($events));
				$heightCal = 72;
				$length = (((TimeToSec(pretty_time($events[$i]->DTEND))-TimeToSec(pretty_time($events[$i]->DTSTART))))/$divide)*$heightCal;

				if ($event1) {
				$mar = (((TimeToSec(pretty_time($events[$i]->DTSTART))-TimeToSec(pretty_time($event1->DTEND))))/$divide)*$heightCal;
				} else {
				$mar = ((TimeToSec(pretty_time($events[$i]->DTSTART)))/$divide)*$heightCal;
				}

				$str = str_replace($order, $replace, $events[$i]->DESCRIPTION);

				$html  .= "<div class='event $cl' value ='$json' style='height:".$length."vh; width: ".$width."%; margin-top:".$mar."vh;";
				$html .= "'>";
				$html .= "<div class='pretty_time'>".pretty_time($events[$i]->DTSTART)." - ".pretty_time($events[$i]->DTEND)."</div>";
				$html .= "<div class='SUMMARY'>". str_replace($order, $replace, $events[$i]->SUMMARY) ."</div>";
				$html .= "<div class ='extra'> <br> Notes:";
				if(isset($events[$i]->NOTES)) $html .= $events[$i]->NOTES;
				$html .= "<br> </div>";
				if (preg_match($reg_exUrl, $str, $url)) {
					$html .= "<br><div class='extra'>" . preg_replace($reg_exUrl, '<a href="' . $url[0] . '">' . $url[0] . '</a>', $str) . "<br> Plats: " . str_replace($order, $replace, $events[$i]->LOCATION) . "</div>";
				} else {
				$html .= "<br><div class='extra'>" . $str . "<br> Plats: " . $str . "</div>";
				}
				if (!$events[$i]->AVAILABLE) { $html .= "<br><div><button class='edit'>Edit</button><button class='note'>Add note</button></div>"; }
				$html .= "</div>";
			}

		$html2 = "<div class='outerbox' >";
		$html2 .= $html;
		$html2 .= "</div>";

		return $html2;
	}

	function gen_day($events){
		/*
		This function receives all events in a day with the format [day] = [event1,event2,...] where event is
		of the class event from 'importCal.php' and returns their contents in HTML format for use in displaying a user's calendar.
		*/

		$events2 = find_clash($events);

		$html  = "<div class='day'>";
		for($i = 0; $i < count($events2); $i++) {
				if (isset($events2[$i-1]))
				{
					$last = $events2[$i-1][0];
					for($j = 0; $j<count($events2[$i-1]); $j++)
					{
						if(($events2[$i-1][$j]->DTEND) > $last->DTEND){$last = $events2[$i-1][$j];}
					}
					$html .= gen_event($events2[$i], $last);
				}
				else
				{
					$html .= gen_event($events2[$i], null);
				}
		}
		$html .= "</div>";
		return $html;
	}
	function gen_week($date1,$date2) {
		/*
		This function collects all events between the two given dates and returns an array of HTML formatted events in an array with the format [week] = [day1,day2,...] where day is a string of HTML.
		*/
		$events = collect($date1,$date2);
		$ar = array();
		if($events == null) return $ar;
		foreach($events as $event){
			array_push($ar,gen_day($event));
		}
		return $ar;
	}

	function check($clickedEvent){
		/*
		This function checks an event's summary and sees if it belongs to KTH or not, for giving it an HTML class when called in gen_event
		*/
		global $eventColor;
		global $colors;
		if(preg_match('(\([A-Z][A-Z]\d\d\d\d\))', $clickedEvent->SUMMARY))
		{
			$str ='KTH';
			return $str;
		}
		else if(strpos($clickedEvent->SUMMARY, "STUDY-SCHEDULER") !== false){
			if(in_array($clickedEvent->SUMMARY, $eventColor)){
				$str = $colors[array_search($clickedEvent->SUMMARY, $eventColor)];
				return $str;
			}
			else{
				array_push($eventColor, $clickedEvent->SUMMARY);
				$str = $colors[array_search($clickedEvent->SUMMARY, $eventColor)];
				return $str;
			}
			$str = 'studyEvent';
			return $str;
		}
		else
		{
			$str = 'Other';
			return $str;
		}
	}
?>
