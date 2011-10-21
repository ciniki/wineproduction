<?php
//
// Description
// -----------
// Apply an action to an order to change the status and add
// a date.
//
// Public
// ------
// This function applies an action to an order.  This can be one of the following
// actions:
// 
// Started - This will change the status to 20, and set the start_date to the current date.
// Racked - This will change the status to 20, and set the start_date to the current date.
// Filtered - This will change the status to 20, and set the start_date to the current date.
// Bottled - This will change the status to 20, and set the start_date to the current date.
//
// Info
// ----
// Status: 			defined
//
// Arguments
// ---------
// user_id: 		The user making the request
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_wineproduction_actionOrder($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
        'wineproduction_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No order specified'), 
        'action'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No action specified'), 
        'sg_reading'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No SG Reading specified'), 
        'kit_length'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No racking length specified'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/wineproduction/private/checkAccess.php');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.actionOrder'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//
	// Grab the settings for the business from the database
	//
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbDetailsQuery.php');
	$rc =  ciniki_core_dbDetailsQuery($ciniki, 'wineproduction_settings', 'business_id', $args['business_id'], 'wineproductions', 'settings', '');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
	$settings = $rc['settings'];

	//
	// FIXME: Add timezone information
	//
	date_default_timezone_set('America/Toronto');
	$todays_date = strftime("%Y-%m-%d");

	//  
	// Turn off autocommit
	//  
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbTransactionStart.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbTransactionRollback.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbTransactionCommit.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbQuote.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbUpdate.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbAddChangeLog.php');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'wineproduction');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   

	//
	// The wine was just started, setup the racking date automatically
	//
	$strsql = "";
	if( $args['action'] == 'Started' ) {
		$strsql = "UPDATE wineproductions SET start_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "', ";
		$racking_autoschedule = "racking.autoschedule.madeon" . strtolower(date('D', strtotime($todays_date)));
		if( isset($settings[$racking_autoschedule]) && $settings[$racking_autoschedule] > 0 ) {
			// FIXME: Replace following with commented line when rackspace updated to php 5.3.x
			// $racking_date = date_format(date_add(date_create($todays_date), date_interval_create_from_date_string($settings[$racking_autoschedule] . " days")), 'Y-m-d');
			$racking_date = new DateTime($todays_date);
			$racking_date->modify('+' . $settings[$racking_autoschedule] . ' days');
			$strsql .= "racking_date = '" . ciniki_core_dbQuote($ciniki, date_format($racking_date, 'Y-m-d')) . "', ";
			$rc = ciniki_core_dbAddChangeLog($ciniki, 'wineproduction', $args['business_id'], 
				'wineproductions', $args['wineproduction_id'], 'racking_date', date_format($racking_date, 'Y-m-d'));
			$rack_week = ((date_format($racking_date, 'U') - 1468800)/604800)%3;
			// $rack_week = (date_format($racking_date, 'W'))%3;
			$rack_dayofweek = strtolower(date_format($racking_date, 'D'));
			$racking_autocolour = "racking.autocolour.week" . $rack_week . $rack_dayofweek;
			if( isset($settings[$racking_autocolour]) && $settings[$racking_autocolour] != '' ) {
				$strsql .= "rack_colour = '" . ciniki_core_dbQuote($ciniki, $settings[$racking_autocolour]) . "', ";
				$rc = ciniki_core_dbAddChangeLog($ciniki, 'wineproduction', $args['business_id'], 
					'wineproductions', $args['wineproduction_id'], 'rack_colour', $settings[$racking_autocolour]);
			}
		}
		$strsql .= "status = 20 "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['wineproduction_id']) . "' ";
		$rc = ciniki_core_dbAddChangeLog($ciniki, 'wineproduction', $args['business_id'], 
			'wineproductions', $args['wineproduction_id'], 'start_date', $todays_date);
		$rc = ciniki_core_dbAddChangeLog($ciniki, 'wineproduction', $args['business_id'], 
			'wineproductions', $args['wineproduction_id'], 'status', '20');
	} 

	//
	// Bump the status to ready if SG in correct range
	//
	elseif( $args['action'] == 'SGRead' ) {
		if( isset($args['sg_reading']) && $args['sg_reading'] >= 992 && $args['sg_reading'] <= 998 ) {
			$strsql = "UPDATE wineproductions SET status = 25 "
				. ", sg_reading = '" . ciniki_core_dbQuote($ciniki, $args['sg_reading']) . "' "
				. ", last_updated = UTC_TIMESTAMP() "
				. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
				. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['wineproduction_id']) . "' ";
			$rc = ciniki_core_dbAddChangeLog($ciniki, 'wineproduction', $args['business_id'], 
				'wineproductions', $args['wineproduction_id'], 'status', '25');
			$rc = ciniki_core_dbAddChangeLog($ciniki, 'wineproduction', $args['business_id'], 
				'wineproductions', $args['wineproduction_id'], 'sg_reading', $args['sg_reading']);
		} else {
			$strsql = "UPDATE wineproductions SET "
				. "sg_reading = '" . ciniki_core_dbQuote($ciniki, $args['sg_reading']) . "' "
				. ", last_updated = UTC_TIMESTAMP() "
				. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
				. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['wineproduction_id']) . "' ";
			$rc = ciniki_core_dbAddChangeLog($ciniki, 'wineproduction', $args['business_id'], 
				'wineproductions', $args['wineproduction_id'], 'sg_reading', $args['sg_reading']);

		}
	}

	//
	// The wine was just racked, update the rack_date, and set the filtering date and colour automatically
	//
	elseif( $args['action'] == 'Racked' ) {
		// FIXME: Check for SG reading first, must be filled in
		$strsql = "UPDATE wineproductions SET rack_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "', ";
		if( isset($args['kit_length']) && $args['kit_length'] > 0 ) {
			// FIXME: Replace following with commented line when rackspace updated to php 5.3.x
			//$filtering_date = date_format(date_add(date_create($todays_date), date_interval_create_from_date_string($args['kit_length'] . " weeks")), 'Y-m-d');
			// $filtering_date = date_format(date_modify(date_create($todays_date), "+" . $args['weeks'] . " weeks"), 'Y-m-d');
			$filtering_date = new DateTime($todays_date);
			$filtering_date->modify('+' . ($args['kit_length'] - 2) . ' weeks');
			$strsql .= "filtering_date = '" . ciniki_core_dbQuote($ciniki, date_format($filtering_date, 'Y-m-d')) . "', ";
			$rc = ciniki_core_dbAddChangeLog($ciniki, 'wineproduction', $args['business_id'], 
				'wineproductions', $args['wineproduction_id'], 'filtering_date', date_format($filtering_date, 'Y-m-d'));
			$filter_week = ((date_format($filtering_date, 'U') - 1468800)/604800)%7;
			// $filter_week = date_format($filtering_date, 'W')%7;
			$filter_dayofweek = strtolower(date_format($filtering_date, 'D'));
			$filtering_autocolour = "filtering.autocolour.week" . $filter_week . $filter_dayofweek;
			if( isset($settings[$filtering_autocolour]) && $settings[$filtering_autocolour] != '' ) {
				$strsql .= "filter_colour = '" . ciniki_core_dbQuote($ciniki, $settings[$filtering_autocolour]) . "', ";
				$rc = ciniki_core_dbAddChangeLog($ciniki, 'wineproduction', $args['business_id'], 
					'wineproductions', $args['wineproduction_id'], 'filter_colour', $settings[$filtering_autocolour]);
			}
		}
		$strsql .= "status = 30 "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['wineproduction_id']) . "' ";
		$rc = ciniki_core_dbAddChangeLog($ciniki, 'wineproduction', $args['business_id'], 
			'wineproductions', $args['wineproduction_id'], 'rack_date', $todays_date);
		$rc = ciniki_core_dbAddChangeLog($ciniki, 'wineproduction', $args['business_id'], 
			'wineproductions', $args['wineproduction_id'], 'status', '30');
	} 

	//
	// The wine was filtered
	//
	elseif( $args['action'] == 'Filtered' ) {
		$strsql = "UPDATE wineproductions SET filter_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "', status = 40 "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['wineproduction_id']) . "' ";
		$rc = ciniki_core_dbAddChangeLog($ciniki, 'wineproduction', $args['business_id'], 
			'wineproductions', $args['wineproduction_id'], 'filter_date', $todays_date);
		$rc = ciniki_core_dbAddChangeLog($ciniki, 'wineproduction', $args['business_id'], 
			'wineproductions', $args['wineproduction_id'], 'status', '40');
	} 

	//
	// Wines can be pulled and filtered early if necessary
	//
	elseif( $args['action'] == 'Filter Today' ) {
		$strsql = "UPDATE wineproductions SET filtering_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "' "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['wineproduction_id']) . "' ";
		$rc = ciniki_core_dbAddChangeLog($ciniki, 'wineproduction', $args['business_id'], 
			'wineproductions', $args['wineproduction_id'], 'filtering_date', $todays_date);
	}

	//
	// The wine has been bottled
	//
	elseif( $args['action'] == 'Bottled' ) {
		$strsql = "UPDATE wineproductions SET bottle_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "', status = 60 "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['wineproduction_id']) . "' ";
		$rc = ciniki_core_dbAddChangeLog($ciniki, 'wineproduction', $args['business_id'], 
			'wineproductions', $args['wineproduction_id'], 'bottle_date', $todays_date);
		$rc = ciniki_core_dbAddChangeLog($ciniki, 'wineproduction', $args['business_id'], 
			'wineproductions', $args['wineproduction_id'], 'status', '60');
	}

	if( $strsql != "" ) {
		$rc = ciniki_core_dbUpdate($ciniki, $strsql, 'wineproduction');
		if( $rc['stat'] != 'ok' ) { 
			return $rc;
		}
	}

//	if( $rc['num_affected_rows'] != 1 ) {
//		ciniki_core_dbTransactionRollback($ciniki, 'wineproduction');
//		return array('stat'=>'fail', 'err'=>array('code'=>'364', 'msg'=>'Invalid order'));
//	}

	//
	// Commit the database changes
	//
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'wineproduction');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	return array('stat'=>'ok');
}
?>
