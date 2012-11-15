<?php
//
// Description
// -----------
// This method will return the number of wineproduction orders
// that need to have a bottling date specified, or the bottling
// date is in the past and the order status is not bottled.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to get the wineproduction statistics for.
// 
// Returns
// -------
// <stats>
//	<stat status='10' count='13' workdone=0 latewines=0 />
// </stats>
//
function ciniki_wineproduction_statsCTB($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
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
	ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.statsCTB'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//
	// FIXME: Add timezone information
	//
//	date_default_timezone_set('America/Toronto');
//	$todays_date = strftime("%Y-%m-%d");
//	$today_plus_four = strftime("%Y-%m-%d", time()+345600);

	//
	// Get Call to Book Stats
	//
	$strsql = "SELECT 'ctb', COUNT(*) AS count FROM ciniki_wineproductions "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND status < 60 "
		. "AND (TIME(bottling_date) = '00:00:00' OR bottling_date = '0000-00-00 00:00:00') "
		. "AND (filtering_date > 0 AND filtering_date < NOW()) "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbRspQuery');
	$rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'ciniki.wineproduction', 'stats', 'stat', array('stat'=>'ok', 'stats'=>array()));
    if( $rc['stat'] != 'ok' ) { 
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'584', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
	if( !isset($rc['stats']) || !isset($rc['stats'][0]['stat'])) {
		return array('stat'=>'ok', 'ctb'=>'0');
	}
	$ctb = $rc['stats'][0]['stat']['count'];

	return array('stat'=>'ok', 'ctb'=>$ctb);
}
?>
