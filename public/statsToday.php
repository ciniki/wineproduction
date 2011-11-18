<?php
//
// Description
// -----------
// This function will return the stats for how many orders there are to deal
// with today.
//
// Info
// ----
// Status: 			defined
//
// Arguments
// ---------
// 
// user_id: 		The user making the request
// 
// Returns
// -------
// <stats>
//	<stat status='10' count='13' />
// </stats>
//
function ciniki_wineproduction_statsToday($ciniki) {
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.statsToday'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//
	// Get the number of orders in each status for the business, 
	// if no rows found, then return empty array
	//
	$strsql = "SELECT status, COUNT(status) AS count FROM ciniki_wineproductions "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND ("
			. "(status = 10 AND start_date <= NOW()) "
			. " OR (status = 20 AND racking_date <= NOW()) "
			. " OR (status = 30 AND filtering_date <= NOW()) "
			. " OR (status = 40 AND bottling_date <= NOW()) "
			. ") "
		. "GROUP BY status ";
	$rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'wineproduction', 'stats', 'stat', array('stat'=>'ok', 'stats'=>array()));
    if( $rc['stat'] != 'ok' ) { 
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'375', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
	if( !isset($rc['stats']) ) {
		return array('stat'=>'ok', 'stats'=>array());
	}

	return array('stat'=>'ok', 'stats'=>$rc['stats']);
}
?>
