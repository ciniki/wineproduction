<?php
//
// Description
// -----------
// This function will return the stats information for the schedule
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
//		<racking past='1' future='0'>
//			<stat month='08' weekday='Mon' day='18' count='4' />
//		</racking>
//		<filtering past='5' future='45'>
//			<stat month='08' day='18' count='4' />
//			<stat month='08' day='22' count='24' />
//		</filtering>
// </stats>
//
function ciniki_wineproduction_statsSchedule($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
        'start_date'=>array('required'=>'yes', 'type'=>'date', 'blank'=>'no', 'errmsg'=>'No business specified'), 
        'days'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.statsSchedule'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
	require_once($ciniki['config']['core']['modules_dir'] . '/users/private/dateFormat.php');
	$date_format = ciniki_users_dateFormat($ciniki);

	//
	// FIXME: Add timezone information
	//
	date_default_timezone_set('America/Toronto');
	$todays_date = strftime("%Y-%m-%d");

	$stats = array('racking'=>array(), 'filtering'=>array());
	$start_date = new DateTime($args['start_date']);
	for($i=0;$i<$args['days'];$i++) {
		$stats['racking'][$i] = array('stat'=>array('year'=>date_format($start_date, 'Y'), 'month'=>date_format($start_date, 'm'), 'day'=>date_format($start_date, 'j'), 'weekday'=>date_format($start_date, 'D'), 'count'=>'0'));
		$stats['filtering'][$i] = array('stat'=>array('year'=>date_format($start_date, 'Y'), 'month'=>date_format($start_date, 'm'), 'day'=>date_format($start_date, 'j'), 'weekday'=>date_format($start_date, 'D'), 'count'=>'0'));
		$start_date = date_create("@" . (date_format($start_date, 'U') + 86400));
	}

	//
	// Get the number of orders for racking for the next X days
	//
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbRspQuery.php');
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbHashQuery.php');
	$strsql = "SELECT DATE_FORMAT(racking_date, '%Y') AS year, "
		. "DATE_FORMAT(racking_date, '%m') AS month, DATE_FORMAT(racking_date, '%d') AS day, "
		. "DATE_FORMAT(racking_date, '%a') AS weekday, "
		. "DATEDIFF(racking_date, '" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "') AS offset, "
		. "DATE_FORMAT(racking_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as racking_date, "
		. "COUNT(id) AS count "
		. "FROM wineproductions "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND status < 30 "
		. "AND racking_date >= '" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "' "
		. "AND racking_date < DATE_ADD('" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "', INTERVAL '" . ciniki_core_dbQuote($ciniki, $args['days']) . "' DAY) "
		. "GROUP BY racking_date "
		. "";
	$rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'wineproduction', 'racking', 'stat', array('stat'=>'ok', 'racking'=>array()));
    if( $rc['stat'] != 'ok' ) { 
		return array('stat'=>'fail', 'err'=>array('code'=>'428', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
	if( !isset($rc['racking']) ) {
		return array('stat'=>'ok', 'stats'=>$stats);
	}
	foreach($rc['racking'] as $stat) {
		$stats['racking'][$stat['stat']['offset']] = $stat;
	}

	// 
	// Get the past and future values for racking
	//
	$strsql = "SELECT COUNT(id) AS count FROM wineproductions "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND status < 30 "
		. "AND racking_date > '0000-00-00' "
		. "AND racking_date < '" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'wineproduction', 'racking');
    if( $rc['stat'] != 'ok' ) { 
		return array('stat'=>'fail', 'err'=>array('code'=>'429', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
	$stats['racking']['past'] = $rc['racking']['count'];

	$strsql = "SELECT COUNT(id) AS count FROM wineproductions "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND status < 30 "
		. "AND racking_date >= DATE_ADD('" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "', INTERVAL '" . ciniki_core_dbQuote($ciniki, $args['days']) . "' DAY) "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'wineproduction', 'racking');
    if( $rc['stat'] != 'ok' ) { 
		return array('stat'=>'fail', 'err'=>array('code'=>'431', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
	$stats['racking']['future'] = $rc['racking']['count'];
	

	//
	// Get the number of orders for filtering for the next X days
	//
	$strsql = "SELECT DATE_FORMAT(filtering_date, '%Y') AS year, DATE_FORMAT(filtering_date, '%m') AS month, DATE_FORMAT(filtering_date, '%d') AS day, "
		. "DATE_FORMAT(filtering_date, '%a') AS weekday, "
		. "DATEDIFF(filtering_date, '" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "') AS offset, "
		. "DATE_FORMAT(filtering_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as filtering_date, "
		. "COUNT(id) AS count "
		. "FROM wineproductions "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND status < 40 "
		. "AND filtering_date >= '" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "' "
		. "AND filtering_date < DATE_ADD('" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "', INTERVAL '" . ciniki_core_dbQuote($ciniki, $args['days']) . "' DAY) "
		. "GROUP BY filtering_date "
		. "";
	$rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'wineproduction', 'filtering', 'stat', array('stat'=>'ok', 'filtering'=>array()));
    if( $rc['stat'] != 'ok' ) { 
		return array('stat'=>'fail', 'err'=>array('code'=>'430', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
	if( !isset($rc['filtering']) ) {
		return array('stat'=>'ok', 'stats'=>$stats);
	}
	foreach($rc['filtering'] as $stat) {
		$stats['filtering'][$stat['stat']['offset']] = $stat;
	}

	// 
	// Get the past and future values for filtering
	//
	$strsql = "SELECT COUNT(id) AS count FROM wineproductions "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND status > 25 AND status < 40 "
		. "AND filtering_date > '0000-00-00' "
		. "AND filtering_date < '" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'wineproduction', 'filtering');
    if( $rc['stat'] != 'ok' ) { 
		return array('stat'=>'fail', 'err'=>array('code'=>'418', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
	$stats['filtering']['past'] = $rc['filtering']['count'];

	$strsql = "SELECT COUNT(id) AS count FROM wineproductions "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND status < 40 "
		. "AND filtering_date >= DATE_ADD('" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "', INTERVAL '" . ciniki_core_dbQuote($ciniki, $args['days']) . "' DAY) "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'wineproduction', 'filtering');
    if( $rc['stat'] != 'ok' ) { 
		return array('stat'=>'fail', 'err'=>array('code'=>'417', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
	$stats['filtering']['future'] = $rc['filtering']['count'];

	
	return array('stat'=>'ok', 'stats'=>$stats);
}
?>
