<?php
//
// Description
// -----------
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
function ciniki_wineproduction_updateAppointment($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
        'wineproduction_ids'=>array('required'=>'yes', 'type'=>'idlist', 'blank'=>'no', 'errmsg'=>'No order specified'), 
        'bottling_duration'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No bottling duration specified'), 
        'bottling_flags'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No bottling flags specified'), 
        'bottling_nocolour_flags'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No bottling flags specified'), 
        'bottling_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'datetime', 'errmsg'=>'No bottling date specified'), 
        'bottling_notes'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No bottling notes specified'), 
        'bottled'=>array('required'=>'no', 'errmsg'=>'No bottled flag specified'), 
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.updateAppointment'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//  
	// Turn off autocommit
	//  
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbTransactionStart.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbTransactionRollback.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbTransactionCommit.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbQuote.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbQuoteIDs.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbUpdate.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbAddModuleHistory.php');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'wineproduction');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   

	//
	// FIXME: Add timezone information
	//
	date_default_timezone_set('America/Toronto');
	$todays_date = strftime("%Y-%m-%d");

	//
	// Add the order to the database
	//
	$strsql = "UPDATE ciniki_wineproductions SET last_updated = UTC_TIMESTAMP() ";

	//
	// Add all the fields to the change log
	//
	if( isset($args['bottled']) && $args['bottled'] == 'yes' ) {
		$strsql .= ", bottle_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "', status = 60 ";
		foreach($args['wineproduction_ids'] as $wid) {
			$rc = ciniki_core_dbAddModuleHistory($ciniki, 'wineproduction', 'ciniki_wineproduction_history', $args['business_id'], 
				2, 'ciniki_wineproductions', $wid, 'bottle_date', $todays_date);
			$rc = ciniki_core_dbAddModuleHistory($ciniki, 'wineproduction', 'ciniki_wineproduction_history', $args['business_id'], 
				2, 'ciniki_wineproductions', $wid, 'status', '60');
		}
	}

	//
	// Update the bottling status if specified
	//
	foreach($args['wineproduction_ids'] as $wid) {
		if( isset($ciniki['request']['args']['order_' . $wid . '_bottling_status']) && $ciniki['request']['args']['order_' . $wid . '_bottling_status'] != '' ) {
			$strsql_a = "UPDATE ciniki_wineproductions SET "
				. "bottling_status = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args']['order_' . $wid . '_bottling_status']) . "' "
				. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
				. "AND id = '" . ciniki_core_dbQuote($ciniki, $wid) . "' ";
			$rc = ciniki_core_dbUpdate($ciniki, $strsql_a, 'wineproduction');
			if( $rc['stat'] != 'ok' ) { 
				ciniki_core_dbTransactionRollback($ciniki, 'wineproduction');
				return $rc;
			}

			$rc = ciniki_core_dbAddModuleHistory($ciniki, 'wineproduction', 'ciniki_wineproduction_history', $args['business_id'], 
				2, 'ciniki_wineproductions', $wid, 'bottling_status', $ciniki['request']['args']['order_' . $wid . '_bottling_status']);
		}
	}

	//
	// Save change log
	//
	$changelog_fields = array(
		'bottling_duration',
		'bottling_flags',
		'bottling_nocolour_flags',
		'bottling_date',
		'bottling_notes',
		);
	foreach($changelog_fields as $field) {
		if( isset($args[$field]) ) {
			$strsql .= ", $field = '" . ciniki_core_dbQuote($ciniki, $args[$field]) . "' ";
			foreach($args['wineproduction_ids'] as $wid) {
				$rc = ciniki_core_dbAddModuleHistory($ciniki, 'wineproduction', 'ciniki_wineproduction_history', $args['business_id'], 
					2, 'ciniki_wineproductions', $wid, $field, $args[$field]);
			}
		}
	}
	$strsql .= "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id IN (" . ciniki_core_dbQuoteIDs($ciniki, $args['wineproduction_ids']) . ") ";
	$rc = ciniki_core_dbUpdate($ciniki, $strsql, 'wineproduction');
	if( $rc['stat'] != 'ok' ) { 
		ciniki_core_dbTransactionRollback($ciniki, 'wineproduction');
		return $rc;
	}
	if( !isset($rc['num_affected_rows']) || $rc['num_affected_rows'] < 1 ) {
		ciniki_core_dbTransactionRollback($ciniki, 'wineproduction');
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'494', 'msg'=>'Unable to add order'));
	}

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
