<?php
//
// Description
// -----------
// This method will update one or more settings for the wine production module.
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
function ciniki_wineproduction_updateSettings($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.updateSettings'); 
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
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbInsert.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbAddChangeLog.php');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'wineproduction');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   


	//
	// The list of allowed fields for updating
	//
	$changelog_fields = array(
		'order.colourtags.colour1',
		'order.colourtags.colour2',
		'order.colourtags.colour3',
		'order.colourtags.colour4',
		'order.colourtags.colour5',
		'order.colourtags.colour6',
		'order.colourtags.colour7',
		'bottling.schedule.start',
		'bottling.schedule.end',
		'bottling.schedule.interval',
		'bottling.schedule.batchduration',
		'racking.autoschedule.madeonsun',
		'racking.autoschedule.madeonmon',
		'racking.autoschedule.madeontue',
		'racking.autoschedule.madeonwed',
		'racking.autoschedule.madeonthu',
		'racking.autoschedule.madeonfri',
		'racking.autoschedule.madeonsat',
		'racking.autocolour.week0sun',
		'racking.autocolour.week0mon',
		'racking.autocolour.week0tue',
		'racking.autocolour.week0wed',
		'racking.autocolour.week0thu',
		'racking.autocolour.week0fri',
		'racking.autocolour.week0sat',
		'racking.autocolour.week1sun',
		'racking.autocolour.week1mon',
		'racking.autocolour.week1tue',
		'racking.autocolour.week1wed',
		'racking.autocolour.week1thu',
		'racking.autocolour.week1fri',
		'racking.autocolour.week1sat',
		'racking.autocolour.week2sun',
		'racking.autocolour.week2mon',
		'racking.autocolour.week2tue',
		'racking.autocolour.week2wed',
		'racking.autocolour.week2thu',
		'racking.autocolour.week2fri',
		'racking.autocolour.week2sat',
		'racking.autocolour.week3sun',
		'racking.autocolour.week3mon',
		'racking.autocolour.week3tue',
		'racking.autocolour.week3wed',
		'racking.autocolour.week3thu',
		'racking.autocolour.week3fri',
		'racking.autocolour.week3sat',
		'filtering.autocolour.week0sun',
		'filtering.autocolour.week0mon',
		'filtering.autocolour.week0tue',
		'filtering.autocolour.week0wed',
		'filtering.autocolour.week0thu',
		'filtering.autocolour.week0fri',
		'filtering.autocolour.week0sat',
		'filtering.autocolour.week1sun',
		'filtering.autocolour.week1mon',
		'filtering.autocolour.week1tue',
		'filtering.autocolour.week1wed',
		'filtering.autocolour.week1thu',
		'filtering.autocolour.week1fri',
		'filtering.autocolour.week1sat',
		'filtering.autocolour.week2sun',
		'filtering.autocolour.week2mon',
		'filtering.autocolour.week2tue',
		'filtering.autocolour.week2wed',
		'filtering.autocolour.week2thu',
		'filtering.autocolour.week2fri',
		'filtering.autocolour.week2sat',
		'filtering.autocolour.week3sun',
		'filtering.autocolour.week3mon',
		'filtering.autocolour.week3tue',
		'filtering.autocolour.week3wed',
		'filtering.autocolour.week3thu',
		'filtering.autocolour.week3fri',
		'filtering.autocolour.week3sat',
		'filtering.autocolour.week4sun',
		'filtering.autocolour.week4mon',
		'filtering.autocolour.week4tue',
		'filtering.autocolour.week4wed',
		'filtering.autocolour.week4thu',
		'filtering.autocolour.week4fri',
		'filtering.autocolour.week4sat',
		'filtering.autocolour.week5sun',
		'filtering.autocolour.week5mon',
		'filtering.autocolour.week5tue',
		'filtering.autocolour.week5wed',
		'filtering.autocolour.week5thu',
		'filtering.autocolour.week5fri',
		'filtering.autocolour.week5sat',
		'filtering.autocolour.week6sun',
		'filtering.autocolour.week6mon',
		'filtering.autocolour.week6tue',
		'filtering.autocolour.week6wed',
		'filtering.autocolour.week6thu',
		'filtering.autocolour.week6fri',
		'filtering.autocolour.week6sat',
		'filtering.autocolour.week7sun',
		'filtering.autocolour.week7mon',
		'filtering.autocolour.week7tue',
		'filtering.autocolour.week7wed',
		'filtering.autocolour.week7thu',
		'filtering.autocolour.week7fri',
		'filtering.autocolour.week7sat',
		'filtering.autocolour.week8sun',
		'filtering.autocolour.week8mon',
		'filtering.autocolour.week8tue',
		'filtering.autocolour.week8wed',
		'filtering.autocolour.week8thu',
		'filtering.autocolour.week8fri',
		'filtering.autocolour.week8sat',
		'filtering.autocolour.week9sun',
		'filtering.autocolour.week9mon',
		'filtering.autocolour.week9tue',
		'filtering.autocolour.week9wed',
		'filtering.autocolour.week9thu',
		'filtering.autocolour.week9fri',
		'filtering.autocolour.week9sat',
		'filtering.autocolour.week10sun',
		'filtering.autocolour.week10mon',
		'filtering.autocolour.week10tue',
		'filtering.autocolour.week10wed',
		'filtering.autocolour.week10thu',
		'filtering.autocolour.week10fri',
		'filtering.autocolour.week10sat',
		'filtering.autocolour.week11sun',
		'filtering.autocolour.week11mon',
		'filtering.autocolour.week11tue',
		'filtering.autocolour.week11wed',
		'filtering.autocolour.week11thu',
		'filtering.autocolour.week11fri',
		'filtering.autocolour.week11sat',
		'filtering.autocolour.week12sun',
		'filtering.autocolour.week12mon',
		'filtering.autocolour.week12tue',
		'filtering.autocolour.week12wed',
		'filtering.autocolour.week12thu',
		'filtering.autocolour.week12fri',
		'filtering.autocolour.week12sat',
		'bottling.flags.1.name',
		'bottling.flags.1.colour',
		'bottling.flags.1.fontcolour',
		'bottling.flags.2.name',
		'bottling.flags.2.colour',
		'bottling.flags.2.fontcolour',
		'bottling.flags.3.name',
		'bottling.flags.3.colour',
		'bottling.flags.3.fontcolour',
		'bottling.flags.4.name',
		'bottling.flags.4.colour',
		'bottling.flags.4.fontcolour',
		'bottling.flags.5.name',
		'bottling.flags.5.colour',
		'bottling.flags.5.fontcolour',
		'bottling.flags.6.name',
		'bottling.flags.6.colour',
		'bottling.flags.6.fontcolour',
		'bottling.flags.7.name',
		'bottling.flags.7.colour',
		'bottling.flags.7.fontcolour',
		'bottling.flags.8.name',
		'bottling.flags.8.colour',
		'bottling.flags.8.fontcolour',
		'order.flags.1.name',
		'order.flags.1.colour',
		'order.flags.1.fontcolour',
		'order.flags.2.name',
		'order.flags.2.colour',
		'order.flags.2.fontcolour',
		'order.flags.3.name',
		'order.flags.3.colour',
		'order.flags.3.fontcolour',
		'order.flags.4.name',
		'order.flags.4.colour',
		'order.flags.4.fontcolour',
		'order.flags.5.name',
		'order.flags.5.colour',
		'order.flags.5.fontcolour',
		'order.flags.6.name',
		'order.flags.6.colour',
		'order.flags.6.fontcolour',
		'order.flags.7.name',
		'order.flags.7.colour',
		'order.flags.7.fontcolour',
		'order.flags.8.name',
		'order.flags.8.colour',
		'order.flags.8.fontcolour',
		);
	//
	// Check each valid setting and see if a new value was passed in the arguments for it.
	// Insert or update the entry in the ciniki_wineproduction_settings table
	//
	foreach($changelog_fields as $field) {
		if( isset($ciniki['request']['args'][$field]) ) {
			$strsql = "INSERT INTO ciniki_wineproduction_settings (business_id, detail_key, detail_value, date_added, last_updated) "
				. "VALUES ('" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args']['business_id']) . "'"
				. ", '" . ciniki_core_dbQuote($ciniki, $field) . "'"
				. ", '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args'][$field]) . "'"
				. ", UTC_TIMESTAMP(), UTC_TIMESTAMP()) "
				. "ON DUPLICATE KEY UPDATE detail_value = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args'][$field]) . "' "
				. ", last_updated = UTC_TIMESTAMP() "
				. "";
			$rc = ciniki_core_dbInsert($ciniki, $strsql, 'wineproduction');
			if( $rc['stat'] != 'ok' ) {
				ciniki_core_dbTransactionRollback($ciniki, 'wineproduction');
				return $rc;
			}
			ciniki_core_dbAddChangeLog($ciniki, 'wineproduction', $args['business_id'], 'ciniki_wineproduction_settings', $field, 'detail_value', $ciniki['request']['args'][$field]);
		}
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
