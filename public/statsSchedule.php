<?php
//
// Description
// -----------
// This method will return the stats information for the production schedule.
//
// API Arguments
// -------------
// api_key:
// auth_token:
// tnid:     The ID of the tenant to get the stats schedule for.
// start_date:      The first date to get the stats from.
// days:            The number of days to collect stats for.
// 
// Returns
// -------
// <stats>
//      <racking past='1' future='0'>
//          <stat month='08' weekday='Mon' day='18' count='4' />
//      </racking>
//      <filtering past='5' future='45'>
//          <stat month='08' day='18' count='4' />
//          <stat month='08' day='22' count='24' />
//      </filtering>
// </stats>
//
function ciniki_wineproduction_statsSchedule($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'start_date'=>array('required'=>'yes', 'type'=>'date', 'blank'=>'no', 'name'=>'Start Date'), 
        'days'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Days'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.statsSchedule'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
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
        $stats['bottling'][$i] = array('stat'=>array('year'=>date_format($start_date, 'Y'), 'month'=>date_format($start_date, 'm'), 'day'=>date_format($start_date, 'j'), 'weekday'=>date_format($start_date, 'D'), 'count'=>'0'));
        $start_date = date_create("@" . (date_format($start_date, 'U') + 86400));
    }

    //
    // Get the number of orders for racking for the next X days
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbRspQuery');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    $strsql = "SELECT DATE_FORMAT(racking_date, '%Y') AS year, "
        . "DATE_FORMAT(racking_date, '%m') AS month, DATE_FORMAT(racking_date, '%d') AS day, "
        . "DATE_FORMAT(racking_date, '%a') AS weekday, "
        . "DATEDIFF(racking_date, '" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "') AS offset, "
        . "DATE_FORMAT(racking_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as racking_date, "
        . "COUNT(id) AS count "
        . "FROM ciniki_wineproductions "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND status < 30 "
        . "AND racking_date >= '" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "' "
        . "AND racking_date < DATE_ADD('" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "', INTERVAL '" . ciniki_core_dbQuote($ciniki, $args['days']) . "' DAY) "
        . "GROUP BY racking_date "
        . "";
    $rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'ciniki.wineproduction', 'racking', 'stat', array('stat'=>'ok', 'racking'=>array()));
    if( $rc['stat'] != 'ok' ) { 
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.29', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
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
    $strsql = "SELECT COUNT(id) AS count FROM ciniki_wineproductions "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND status < 30 "
        . "AND racking_date > '0000-00-00' "
        . "AND DATE(racking_date) < '" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'racking');
    if( $rc['stat'] != 'ok' ) { 
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.30', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
    $stats['racking']['past'] = $rc['racking']['count'];

    $strsql = "SELECT COUNT(id) AS count FROM ciniki_wineproductions "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND status < 30 "
        . "AND racking_date >= DATE_ADD('" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "', INTERVAL '" . ciniki_core_dbQuote($ciniki, $args['days']) . "' DAY) "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'racking');
    if( $rc['stat'] != 'ok' ) { 
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.31', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
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
        . "FROM ciniki_wineproductions "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND status < 40 "
        . "AND filtering_date >= '" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "' "
        . "AND filtering_date < DATE_ADD('" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "', INTERVAL '" . ciniki_core_dbQuote($ciniki, $args['days']) . "' DAY) "
        . "GROUP BY filtering_date "
        . "";
    $rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'ciniki.wineproduction', 'filtering', 'stat', array('stat'=>'ok', 'filtering'=>array()));
    if( $rc['stat'] != 'ok' ) { 
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.32', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
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
    $strsql = "SELECT COUNT(id) AS count FROM ciniki_wineproductions "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND status > 25 AND status < 40 "
        . "AND filtering_date > '0000-00-00' "
        . "AND DATE(filtering_date) < '" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'filtering');
    if( $rc['stat'] != 'ok' ) { 
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.33', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
    $stats['filtering']['past'] = $rc['filtering']['count'];

    $strsql = "SELECT COUNT(id) AS count FROM ciniki_wineproductions "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND status < 40 "
        . "AND filtering_date >= DATE_ADD('" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "', INTERVAL '" . ciniki_core_dbQuote($ciniki, $args['days']) . "' DAY) "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'filtering');
    if( $rc['stat'] != 'ok' ) { 
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.34', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
    $stats['filtering']['future'] = $rc['filtering']['count'];

    //
    // Get the number of orders for bottling for the next X days
    //
    $strsql = "SELECT DATE_FORMAT(bottling_date, '%Y') AS year, DATE_FORMAT(bottling_date, '%m') AS month, DATE_FORMAT(bottling_date, '%d') AS day, "
        . "DATE_FORMAT(bottling_date, '%a') AS weekday, "
        . "DATEDIFF(bottling_date, '" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "') AS offset, "
        . "DATE_FORMAT(bottling_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as bottling_date, "
        . "COUNT(id) AS count "
        . "FROM ciniki_wineproductions "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND status < 60 "
        . "AND DATE(bottling_date) >= '" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "' "
        . "AND DATE(bottling_date) < DATE_ADD('" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "', INTERVAL '" . ciniki_core_dbQuote($ciniki, $args['days']) . "' DAY) "
        . "GROUP BY DATE(bottling_date) "
        . "";
    $rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'ciniki.wineproduction', 'bottling', 'stat', array('stat'=>'ok', 'bottling'=>array()));
    if( $rc['stat'] != 'ok' ) { 
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.35', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
    if( !isset($rc['bottling']) ) {
        return array('stat'=>'ok', 'stats'=>$stats);
    }
    foreach($rc['bottling'] as $stat) {
        $stats['bottling'][$stat['stat']['offset']] = $stat;
    }

    // 
    // Get the past and future values for bottling
    //
    $strsql = "SELECT COUNT(id) AS count FROM ciniki_wineproductions "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND status < 60 "
//      . "AND bottling_date > '0000-00-00' "
        . "AND bottling_date < '" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'bottling');
    if( $rc['stat'] != 'ok' ) { 
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.36', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
    $stats['bottling']['past'] = $rc['bottling']['count'];

    $strsql = "SELECT COUNT(id) AS count FROM ciniki_wineproductions "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND status < 60 "
        . "AND bottling_date >= DATE_ADD('" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "', INTERVAL '" . ciniki_core_dbQuote($ciniki, $args['days']) . "' DAY) "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'bottling');
    if( $rc['stat'] != 'ok' ) { 
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.37', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
    $stats['bottling']['future'] = $rc['bottling']['count'];

    
    return array('stat'=>'ok', 'stats'=>$stats);
}
?>
