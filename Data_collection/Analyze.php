<?php

$collection = json_decode(file_get_contents("Collection.txt"));

print_r($collection);

function days($d) {
	return false;
}

function analyze ($events) {
	$e = json_decode($events);
	$tempStart;
	$tempEnd;
	
	
	
	//Denna for loop fixar dagar man ej vill plugga
	for ($i = 0; $i < count($e); $i++) {
		if ($e[$i]->AVAILABLE) {
			if (days($e[$i]->DTSTART) && days($e[$i]->DTEND)) {
				unset($e[$i]);
			} else {
				$date = $e[$i]->DTSTART;
				if(days($e[$i]->DTSTART)) {
					$e[$i]->DTSTART = date('Ymd', strtotime($e[$i]->DTSTART .' +1 day')) . "T0000Z";//$e[$i]->DTSTART = next day 00.00;
					$date = $e[$i]->DTSTART;
				}
				for ( ; $date < $e[$i]->DTEND; date('Ymd', strtotime($date .' +1 day'))) {
					if (days($date)) {
						if ($e[$i]->DTEND == $date && $e[$i]->DTSTART == $date) {
						 unset($e[$i]);
						} else if ($e[$i]->DTEND == $date) {
							$e[$i]->DTEND = date('Ymd', strtotime($e[$i]->DTEND .' -1 day')) . "T2359Z";//$e[$i]->DTEND = previous day 23.59
						}
						else {
							$e[$i]->DTSTART = date('Ymd', strtotime($e[$i]->DTSTART .' +1 day')) . "T0000Z";//$e[$i]->DTSTART next day 00.00
						}
					}
				}				
			}
		}
	}
	
	//Denna for loop fixar s�mnschema
	for ($i = 0; $i < count($e); $i++) {
		if ($e[$i]->AVAILABLE) {
			if ($e[$i]->DTSTART < $collection->sleepfrom && $e[$i]->DTEND > $collection->sleepfrom && $e[$i]->DTEND <= $collection->sleepto) {
				$e[$i]->DTEND = $collection->sleepfrom;
			} else if ($e[$i]->DTSTART >= $collection->sleepfrom && $e[$i]->DTEND <= $collection->sleepto) {
				unset($e[$i]);
			} else if ($e[$i]->DTSTART >= $collection->sleepfrom && $e[$i]->DTSTART < $collection->sleepto && $e[$i]->DTEND > $collection->sleepto) {
				$e[$i]->DTSTART = $collection->sleepto;
			} else if ($e[$i]->DTSTART < $collection->sleepfrom && $e[$i]->DTEND > $collection->sleepto) {
				//Splitta och beh�ll efter samt innan sova
				$avEvent = $e[$i];
				$avEvent->DTSTART = $collection->sleepto;
				$e[$i]->DTEND = $collection->sleepfrom;
				array_splice($e, $i+1, 0, $avEvent);
			}
		}
	}
	
	$firstEvent = true;
	$lastEvent;
	//Denna loop fixar restider
	for($i = 0; $i < count($e) ; $i++) {
		if (!$e[$i]->AVAILABLE) {
			if ($firstEvent) {
				//l�gg restid innan f�rsta event
			}
			for ($y = $i; $y < count($e); $y++) {
				if(!$e[$y]->AVAILABLE) {
					//j�mf�ra ny dag
					if(date('Ymd', strtotime($e[$i]->DTEND) !== date('Ymd', strtotime($e[$y]->DTSTART){		
						findAvailBetween($i,$y,$collection->traveltime, $collection->traveltime, $e);			
					//j�mf�ra om det �r samma sorts event (skola � skola eller samma habit � habit
					else if(($e[$i]->SUMMARY == $e[$y]->SUMMARY)||){
						
					}
					$lastEvent = false;
					//om det inte �r samma sort, l�gg restid mellan
					else{
					findAvailBetween($i,$y,$collection->traveltime, $collection->traveltime, $e)
					} 
				}
			}
			if ($lastEvent) {
				//l�gg restid efter sista event
			}
			$lastEvent = true;
			$firstEvent = false;
		}
	}
	//Denna loop fixar pauser
}
// Hittar, klipper till och/eller tar bort events f�r restiden i schemat
findAvailBetween($i,$y,$ttime1,$ttime2, $e){
	$pause1end = $e[$i]->DTSTART // + $ttime1
	$pause2start = $e[$y]->DTSTART // - $ttime2
	for($x = $i; $x < $y; $x++){
		$u = false;
		if($e[$x]->DTSTART >= $e[$i]->DTEND && $e[$x]->DTEND <= $pause1end){ // Om avail �r innuti restiden
			unset($e[$x]);
			$u = true;
			$x--;
		}
		if($e[$x]->DTSTART < $pause1end && $e[$x]->DTEND > $pause1end && !$u){  
			$e[$x]->DTSTART = $pause1end;
		}
		if($e[$x]->DTEND > $pause2start && $e[$x]->DTSTART >= $pause2start && !$u) {
			unset($e[$x]);
			$u = true;
			$x--;
		}
		if ($e[$x]->DTEND > $pause2start && !$u) {
			$e[$x]->DTEND = $pause2start;
		}
	}	
}












?>