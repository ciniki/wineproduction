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
function ciniki_wineproduction_getBottlingSchedule($ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
		'date'=>array('required'=>'no', 'default'=>'today', 'blank'=>'yes', 'errmsg'=>'No date specified'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id as owner, or sys admin
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
	$rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.getBottlingSchedule');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Grab the settings for the business from the database
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQuery');
	$rc = ciniki_core_dbDetailsQuery($ciniki, 'ciniki_wineproduction_settings', 'business_id', $args['business_id'], 'ciniki.wineproduction', 'settings', '');
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

	$strsql = "SELECT ciniki_wineproductions.id, CONCAT_WS(' ', first, last) AS customer_name, invoice_number, ciniki_products.name AS wine_name, "
		. "DATE_FORMAT(bottling_date, '%Y-%m-%d') as bottling_date, "
		. "DATE_FORMAT(bottling_date, '%H:%i') as bottling_time, "
		. "DATE_FORMAT(bottling_date, '%l:%i') as bottling_12hour, "
		. "UNIX_TIMESTAMP(bottling_date) as bottling_timestamp "
		. "FROM ciniki_wineproductions "
		. "JOIN ciniki_products ON (ciniki_wineproductions.product_id = ciniki_products.id "
			. "AND ciniki_products.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "') "
		. "LEFT JOIN ciniki_customers ON (ciniki_wineproductions.customer_id = ciniki_customers.id "
			. "AND ciniki_customers.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "') "
		. "WHERE ciniki_wineproductions.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND ciniki_wineproductions.product_id = ciniki_products.id "
		. "AND ciniki_products.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND DATE(bottling_date) = '" . ciniki_core_dbQuote($ciniki, $args['date']) . "' "
		. "ORDER BY bottling_date, bottling_time, customer_name, invoice_number "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbRspQuery');
	$rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'ciniki.wineproduction', 'events', 'event', array('stat'=>'ok', 'events'=>array()));
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
