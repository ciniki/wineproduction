<?php
//
// Description
// -----------
// This function will return the stats of how many order orders
// are in which states
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
//	<stat status='10' count='13' workdone=0 latewines=0 />
// </stats>
//
function ciniki_wineproduction_stats($ciniki) {
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.stats'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//
	// FIXME: Add timezone information
	//
	date_default_timezone_set('America/Toronto');
	$todays_date = strftime("%Y-%m-%d");
	$today_plus_four = strftime("%Y-%m-%d", time()+345600);

	//
	// Get the complete count information
	//
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbRspQuery.php');
	$strsql = "SELECT status, COUNT(status) AS count FROM wineproductions "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "GROUP BY status ";
	$rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'wineproduction', 'stats', 'stat', array('stat'=>'ok', 'stats'=>array()));
    if( $rc['stat'] != 'ok' ) { 
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'362', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
	if( !isset($rc['stats']) ) {
		return array('stat'=>'ok', 'stats'=>array());
	}
	$stats = $rc['stats'];

	
	//
	// Get past stats
	//
	$strsql = "SELECT status, COUNT(status) AS count FROM wineproductions "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND ("
			. "(status = 10 AND start_date < '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
			. " OR (status = 20 AND racking_date < '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
			. " OR (status = 25 AND racking_date < '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
			. " OR (status = 30 AND filtering_date < '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
			. " OR (status = 40 AND DATE(bottling_date) < '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
			. ") "
		. "GROUP BY status ";
	$rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'wineproduction', 'stats', 'stat', array('stat'=>'ok', 'stats'=>array()));
    if( $rc['stat'] != 'ok' ) { 
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'393', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
	if( !isset($rc['stats']) ) {
		return array('stat'=>'ok', 'stats'=>array());
	}
	$past_stats = $rc['stats'];

	//
	// Get todays or previous order stats
	//
	$strsql = "SELECT status, COUNT(status) AS count FROM wineproductions "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND ("
			. "(status = 10 AND start_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
			. " OR (status = 20 AND racking_date >= '" . ciniki_core_dbQuote($ciniki, $todays_date) . "' AND racking_date <= '" . ciniki_core_dbQuote($ciniki, $today_plus_four) . "') "
			. " OR (status = 25) "
			. " OR (status = 30 AND filtering_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
			. " OR (status = 40 AND DATE(bottling_date) = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
			. ") "
		. "GROUP BY status ";
	$rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'wineproduction', 'stats', 'stat', array('stat'=>'ok', 'stats'=>array()));
    if( $rc['stat'] != 'ok' ) { 
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'395', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
	if( !isset($rc['stats']) ) {
		return array('stat'=>'ok', 'stats'=>array());
	}
	$todays_stats = $rc['stats'];

	//
	// Get future stats
	//
	$strsql = "SELECT status, COUNT(status) AS count FROM wineproductions "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND ("
			. "(status = 10 AND start_date > '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
			. " OR (status = 20 AND racking_date > '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
			. " OR (status = 25 AND racking_date > '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
			. " OR (status = 30 AND filtering_date > '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
			. " OR (status = 40 AND DATE(bottling_date) > '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
			. ") "
		. "GROUP BY status ";
	$rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'wineproduction', 'stats', 'stat', array('stat'=>'ok', 'stats'=>array()));
    if( $rc['stat'] != 'ok' ) { 
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'394', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
	if( !isset($rc['stats']) ) {
		return array('stat'=>'ok', 'stats'=>array());
	}

	$future_stats = $rc['stats'];

	//
	// Get Work Completed stats
	//
	$strsql = "SELECT status, COUNT(status) AS count FROM wineproductions "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND ((status = 10 && order_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') OR "
			. "(status = 20 && start_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') OR "
			. "(status = 25 && start_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') OR "
			. "(status = 30 && rack_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') OR " 
			. "(status = 40 && filter_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') OR "
			. "(status = 60 && bottle_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') ) "
		. "GROUP BY status "
		. "";
	$rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'wineproduction', 'stats', 'stat', array('stat'=>'ok', 'stats'=>array()));
    if( $rc['stat'] != 'ok' ) { 
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'408', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
	if( !isset($rc['stats']) ) {
		return array('stat'=>'ok', 'stats'=>array());
	}
	$workdone = $rc['stats'];

	//
	// Get Late Wines stats
	//
	$strsql = "SELECT status, COUNT(status) AS count FROM wineproductions "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND status < 40 "
		. "AND bottling_date > 0 AND (bottling_date < filtering_date "
			. "OR (filtering_date = 0 AND bottling_date < DATE_ADD(racking_date, INTERVAL (kit_length-2) WEEK)) "
			. "OR (racking_date = 0 AND bottling_date < DATE_ADD(start_date, INTERVAL kit_length WEEK)) "
			. "OR bottling_date < start_date) "
		. "GROUP BY status "
		. "";
	$rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'wineproduction', 'stats', 'stat', array('stat'=>'ok', 'stats'=>array()));
    if( $rc['stat'] != 'ok' ) { 
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'409', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
	if( !isset($rc['stats']) ) {
		return array('stat'=>'ok', 'stats'=>array());
	}
	$latewines = $rc['stats'];

	//
	// Get Call to Book Stats
	//
	$strsql = "SELECT status, COUNT(status) AS count FROM wineproductions "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND status < 60 "
		. "AND (TIME(bottling_date) = '00:00:00' OR bottling_date = '') "
		. "AND (filtering_date > 0 AND filtering_date < NOW()) "
		. "GROUP BY status "
		. "";
	$rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'wineproduction', 'stats', 'stat', array('stat'=>'ok', 'stats'=>array()));
    if( $rc['stat'] != 'ok' ) { 
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'473', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
	if( !isset($rc['stats']) ) {
		return array('stat'=>'ok', 'stats'=>array());
	}
	$ctb = $rc['stats'];

	return array('stat'=>'ok', 'stats'=>$stats, 'past'=>$past_stats, 'todays'=>$todays_stats, 'future'=>$future_stats, 'workdone'=>$workdone, 'latewines'=>$latewines, 'ctb'=>$ctb);
}
?>
