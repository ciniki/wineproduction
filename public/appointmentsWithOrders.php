<?php
//
// Description
// -----------
// This function will return a bottling schedule for a day
//
// Info
// ----
// Status: 				beta
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business to get the details for.
// date:				The date to get the schedule for.
//
// Returns
// -------
//	<events>
//		<event customer_name="" invoice_number="" wine_name="" />
//	</events>
//
function ciniki_wineproduction_appointmentsWithOrders($ciniki) {
	//
	// Find all the required and optional arguments
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
		'date'=>array('required'=>'no', 'default'=>'today', 'blank'=>'yes', 'errmsg'=>'No date specified'), 
		'startdate'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No start date specified'), 
		'enddate'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No end date specified'), 
		'appointment_id'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No appointment ID specified'), 
		'calendars'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No calendars specified'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id as owner, or sys admin
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/wineproduction/private/checkAccess.php');
	$rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.appointments');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Grab the settings for the business from the database
	//
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbDetailsQuery.php');
	$rc =  ciniki_core_dbDetailsQuery($ciniki, 'ciniki_wineproduction_settings', 'business_id', $args['business_id'], 'wineproduction', 'settings', '');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
	$settings = $rc['settings'];

	//
	// FIXME: Add timezone information
	//
	date_default_timezone_set('America/Toronto');
	if( $args['date'] == '' || $args['date'] == 'today' ) {
		$args['date'] = strftime("%Y-%m-%d");
	}
    require_once($ciniki['config']['core']['modules_dir'] . '/users/private/datetimeFormat.php');
	$datetime_format = ciniki_users_datetimeFormat($ciniki);

	$strsql = "SELECT ciniki_wineproductions.id AS order_id, ciniki_wineproductions.customer_id, "
		. "CONCAT_WS('-', UNIX_TIMESTAMP(ciniki_wineproductions.bottling_date), ciniki_wineproductions.customer_id) AS id, "
		. "CONCAT_WS(' ', first, last) AS customer_name, invoice_number, ciniki_products.name AS wine_name, "
		. "DATE_FORMAT(bottling_date, '%Y-%m-%d') As date, "
		. "DATE_FORMAT(bottling_date, '%H:%i') AS time, "
		. "DATE_FORMAT(bottling_date, '%l:%i') AS 12hour, "
		. "UNIX_TIMESTAMP(bottling_date) as bottling_timestamp, bottling_duration AS duration, "
		. "DATE_FORMAT(bottling_date, '" . ciniki_core_dbQuote($ciniki, $datetime_format) . "') as bottling_date, "
		. "ciniki_wineproductions.bottling_flags, "
		. "ciniki_wineproduction_settings.detail_value AS colour, "
		. "DATE_FORMAT(order_date, '%b %e, %Y') AS order_date, "
		. "DATE_FORMAT(start_date, '%b %e, %Y') AS start_date, "
		. "DATE_FORMAT(racking_date, '%b %e, %Y') AS racking_date, "
		. "DATE_FORMAT(filtering_date, '%b %e, %Y') AS filtering_date, "
		. "ciniki_wineproductions.status, IFNULL(s2.detail_value, '') AS bottling_status "
		. "FROM ciniki_wineproductions "
		. "JOIN ciniki_products ON (ciniki_wineproductions.product_id = ciniki_products.id "
			. "AND ciniki_products.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "') "
		. "LEFT JOIN ciniki_customers ON (ciniki_wineproductions.customer_id = ciniki_customers.id "
			. "AND ciniki_customers.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "') "
		. "LEFT JOIN ciniki_wineproduction_settings ON (ciniki_wineproductions.business_id = ciniki_wineproduction_settings.business_id "
			. "AND ciniki_wineproduction_settings.detail_key = CONCAT_WS('.', 'bottling.flags', LOG2(ciniki_wineproductions.bottling_flags)+1, 'colour')) "
		. "LEFT JOIN ciniki_wineproduction_settings s2 ON (ciniki_wineproductions.business_id = s2.business_id "
			. "AND s2.detail_key = CONCAT_WS('.', 'bottling.status', LOG2(ciniki_wineproductions.bottling_status)+1, 'name')) "
		. "WHERE ciniki_wineproductions.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND ciniki_wineproductions.status < 100 "
		. "";
	if( isset($args['appointment_id']) && $args['appointment_id'] != '' && preg_match('/^([0-9]+)-([0-9]+)$/', $args['appointment_id'], $matches)) {
		$strsql .= "AND CONCAT_WS('-', UNIX_TIMESTAMP(ciniki_wineproductions.bottling_date), ciniki_wineproductions.customer_id) = '" . ciniki_core_dbQuote($ciniki, $args['appointment_id']) . "' ";
//		$strsql .= "AND UNIX_TIMESTAMP(bottling_date) = '" . ciniki_core_dbQuote($ciniki, $matches[1]) . "' "
//			. "AND ciniki_wineproductions.customer_id = '" . ciniki_core_dbQuote($ciniki, $matches[2]) . "' "
//			. "";
	} elseif( isset($args['date']) && $args['date'] != '' ) {
		$strsql .= "AND DATE(bottling_date) = '" . ciniki_core_dbQuote($ciniki, $args['date']) . "' ";
	} else {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'493', 'msg'=>'No constraints provided'));
	}
	$strsql .= ""
		. "ORDER BY ciniki_wineproductions.bottling_date, ciniki_wineproductions.customer_id, wine_name, id "
		. "";
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbHashQueryTree.php');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'wineproduction', array(
		array('container'=>'appointments', 'fname'=>'id', 'name'=>'appointment', 'fields'=>array('id', 
			'customer_name', 'date', 'time', '12hour', 'bottling_date', 'duration', 'invoice_number', 'wine_name', 'colour', 'bottling_flags'), 'sums'=>array('duration'), 'countlists'=>array('wine_name')),
		array('container'=>'orders', 'fname'=>'order_id', 'name'=>'order', 'fields'=>array('order_id', 'invoice_number', 'wine_name', 'duration',
			'order_date', 'start_date', 'racking_date', 'filtering_date', 'bottling_date', 'status', 'bottling_status')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Add timestamps, so everything is based on database time.
	//
	if( isset($settings['bottling.schedule.start']) && $settings['bottling.schedule.start'] != '' ) {
		$rc['schedule_start'] = $settings['bottling.schedule.start'];
	} else {
		$rc['schedule_start'] = '10:00';
	}
	if( isset($settings['bottling.schedule.end']) && $settings['bottling.schedule.end'] != '' ) {
		$rc['schedule_end'] = $settings['bottling.schedule.end'];
	} else {
		$rc['schedule_end'] = '20:00';
	}
	if( isset($settings['bottling.schedule.interval']) && $settings['bottling.schedule.interval'] != '' ) {
		$rc['schedule_interval'] = $settings['bottling.schedule.interval'];
	} else {
		$rc['schedule_interval'] = '30';
	}

	$rc['start_timestamp'] = strtotime($args['date'] . " 10:00");
	$rc['interval'] = 1800;
	$rc['end_timestamp'] = strtotime($args['date'] . " 20:00");

	return $rc;
}
?>
