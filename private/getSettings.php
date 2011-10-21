<?php
//
// Description
// -----------
// This function will return the list of colours available to the wine production module.
// In the future, these values can be pulled from the database.
//
// Info
// ----
// Status: 			beta
//
// Arguments
// ---------
// user_id: 		The user making the request
// 
// Returns
// -------
//
function ciniki_wineproduction__getSettings($ciniki, $business_id) {


	date_default_timezone_set('America/Toronto');

	//
	// Grab the settings for the business from the database
	//
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbDetailsQuery.php');
	return ciniki_core_dbDetailsQuery($ciniki, 'wineproduction_settings', 'business_id', $args['business_id'], 'wineproductions', 'settings', '');

	// ** OLD **

	return array('stat'=>'ok', 'settings'=>array(
		'racking.autocolour.week0sun'=>'yellow',
		'racking.autocolour.week0mon'=>'yellow',
		'racking.autocolour.week0tue'=>'tan',
		'racking.autocolour.week0wed'=>'tan',
		'racking.autocolour.week0thu'=>'tan',
		'racking.autocolour.week0fri'=>'red',
		'racking.autocolour.week0sat'=>'red',
		'racking.autocolour.week1sun'=>'red',
		'racking.autocolour.week1mon'=>'red',
		'racking.autocolour.week1tue'=>'blue',
		'racking.autocolour.week1wed'=>'blue',
		'racking.autocolour.week1thu'=>'blue',
		'racking.autocolour.week1fri'=>'green',
		'racking.autocolour.week1sat'=>'green',
		'racking.autocolour.week2sun'=>'green',
		'racking.autocolour.week2mon'=>'green',
		'racking.autocolour.week2tue'=>'lightblue',
		'racking.autocolour.week2wed'=>'lightblue',
		'racking.autocolour.week2thu'=>'lightblue',
		'racking.autocolour.week2fri'=>'darkred',
		'racking.autocolour.week2sat'=>'darkred',
		'racking.autocolour.week3sun'=>'darkred',
		'racking.autocolour.week3mon'=>'darkred',
		'racking.autocolour.week3tue'=>'black',
		'racking.autocolour.week3wed'=>'black',
		'racking.autocolour.week3thu'=>'black',
		'racking.autocolour.week3fri'=>'yellow',
		'racking.autocolour.week3sat'=>'yellow',
		'filtering.autocolour.week0sun'=>'yellow',
		'filtering.autocolour.week0mon'=>'yellow',
		'filtering.autocolour.week0tue'=>'orange',
		'filtering.autocolour.week0wed'=>'orange',
		'filtering.autocolour.week0thu'=>'orange',
		'filtering.autocolour.week0fri'=>'red',
		'filtering.autocolour.week0sat'=>'red',
		'filtering.autocolour.week1sun'=>'red',
		'filtering.autocolour.week1mon'=>'red',
		'filtering.autocolour.week1tue'=>'blue',
		'filtering.autocolour.week1wed'=>'blue',
		'filtering.autocolour.week1thu'=>'blue',
		'filtering.autocolour.week1fri'=>'green',
		'filtering.autocolour.week1sat'=>'green',
		'filtering.autocolour.week2sun'=>'green',
		'filtering.autocolour.week2mon'=>'green',
		'filtering.autocolour.week2tue'=>'lightblue',
		'filtering.autocolour.week2wed'=>'lightblue',
		'filtering.autocolour.week2thu'=>'lightblue',
		'filtering.autocolour.week2fri'=>'darkred',
		'filtering.autocolour.week2sat'=>'darkred',
		'filtering.autocolour.week3sun'=>'darkred',
		'filtering.autocolour.week3mon'=>'darkred',
		'filtering.autocolour.week3tue'=>'black',
		'filtering.autocolour.week3wed'=>'black',
		'filtering.autocolour.week3thu'=>'black',
		'filtering.autocolour.week3fri'=>'yellow',
		'filtering.autocolour.week3sat'=>'yellow',
		'racking.autoschedule.madeonsun'=>'11',
		'racking.autoschedule.madeonmon'=>'10',
		'racking.autoschedule.madeontue'=>'13',
		'racking.autoschedule.madeonwed'=>'12',
		'racking.autoschedule.madeonthu'=>'11',
		'racking.autoschedule.madeonfri'=>'10',
		'racking.autoschedule.madeonsat'=>'12',
		'bottling.schedule.start'=>'10:00',
		'bottling.schedule.end'=>'20:00',
		'bottling.schedule.interval'=>'30',
		'bottling.schedule.batchduration'=>'30',
		));
}
