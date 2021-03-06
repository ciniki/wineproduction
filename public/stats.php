<?php
//
// Description
// -----------
// This method will return the stats of how many order orders
// are in which states
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:     The ID of the tenant to get the wineproduction statistics for.
// 
// Returns
// -------
// <stats>
//  <stat status='10' count='13' workdone=0 latewines=0 />
// </stats>
//
function ciniki_wineproduction_stats($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.stats'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Load the tenant settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
   
    $dt = new DateTime('now', new DateTimezone($intl_timezone));
    $todays_date = $dt->format('Y-m-d');
    $dt->add(new DateInterval('P4D'));
    $today_plus_four = $dt->format('Y-m-d');
//    date_default_timezone_set('America/Toronto');
//    $todays_date = strftime("%Y-%m-%d");
//    $today_plus_four = strftime("%Y-%m-%d", time()+345600);

    //
    // Get the complete count information
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbRspQuery');
    $strsql = "SELECT status, COUNT(status) AS count FROM ciniki_wineproductions "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "GROUP BY status ";
    $rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'ciniki.wineproduction', 'stats', 'stat', array('stat'=>'ok', 'stats'=>array()));
    if( $rc['stat'] != 'ok' ) { 
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.20', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
    if( !isset($rc['stats']) ) {
        return array('stat'=>'ok', 'stats'=>array());
    }
    $stats = $rc['stats'];

    //
    // Get past stats
    //
    $strsql = "SELECT status, COUNT(status) AS count FROM ciniki_wineproductions "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ("
            . "(status = 10 AND start_date < '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
            . " OR (status = 20 AND racking_date < '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
            . " OR (status = 25 AND racking_date < '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
            . " OR (status = 30 AND filtering_date < '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
            . " OR (status = 40 AND DATE(bottling_date) < '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
            . ") "
        . "GROUP BY status ";
    $rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'ciniki.wineproduction', 'stats', 'stat', array('stat'=>'ok', 'stats'=>array()));
    if( $rc['stat'] != 'ok' ) { 
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.21', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
    if( !isset($rc['stats']) ) {
        return array('stat'=>'ok', 'stats'=>array());
    }
    $past_stats = $rc['stats'];
    
    //
    // Get todays or previous order stats
    //
    $strsql = "SELECT status, COUNT(status) AS count FROM ciniki_wineproductions "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ("
            . "(status = 10 AND start_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
            . " OR (status = 20 AND racking_date >= '" . ciniki_core_dbQuote($ciniki, $todays_date) . "' AND racking_date <= '" . ciniki_core_dbQuote($ciniki, $today_plus_four) . "') "
            . " OR (status = 25) "
            . " OR (status = 30 AND filtering_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
            . ") "
        . "GROUP BY status ";
    $rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'ciniki.wineproduction', 'stats', 'stat', array('stat'=>'ok', 'stats'=>array()));
    if( $rc['stat'] != 'ok' ) { 
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.22', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
    if( !isset($rc['stats']) ) {
        return array('stat'=>'ok', 'stats'=>array());
    }
    $todays_stats = $rc['stats'];

    //
    // Check for wines ready to transfer
    //
    if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.wineproduction', 0x0800) ) {
        $strsql = "SELECT status, COUNT(status) AS count FROM ciniki_wineproductions "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND status = 20 "
            . "AND transferring_date <= '" . ciniki_core_dbQuote($ciniki, $todays_date) . "' "
            . "GROUP BY status ";
        $rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'ciniki.wineproduction', 'stats', 'stat', array('stat'=>'ok', 'stats'=>array()));
        if( $rc['stat'] != 'ok' ) { 
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.22', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
        }
        if( isset($rc['stats'][0]) ) {
            $todays_stats[] = array('stat'=>array('status'=>21, 'count'=>$rc['stats'][0]['stat']['count']));
        }
    }

    //
    // Get todays bottling stats
    //
    $strsql = "SELECT '40' AS status, COUNT(DISTINCT customer_id, bottling_date) AS count FROM ciniki_wineproductions "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND DATE(bottling_date) = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "' "
        . "AND TIME(bottling_date) <> '00:00:00' "
        . "";
    $rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'ciniki.wineproduction', 'stats', 'stat', array('stat'=>'ok', 'stats'=>array()));
    if( $rc['stat'] != 'ok' ) { 
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.23', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
    array_push($todays_stats, $rc['stats'][0]);

    //
    // Get future stats
    //
    $strsql = "SELECT status, COUNT(status) AS count FROM ciniki_wineproductions "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ("
            . "(status = 10 AND start_date > '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
            . " OR (status = 20 AND racking_date > '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
            . " OR (status = 25 AND racking_date > '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
            . " OR (status = 30 AND filtering_date > '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
            . " OR (status = 40 AND DATE(bottling_date) > '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
            . ") "
        . "GROUP BY status ";
    $rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'ciniki.wineproduction', 'stats', 'stat', array('stat'=>'ok', 'stats'=>array()));
    if( $rc['stat'] != 'ok' ) { 
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.24', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
    if( !isset($rc['stats']) ) {
        return array('stat'=>'ok', 'stats'=>array());
    }

    $future_stats = $rc['stats'];

    //
    // Get Work Completed stats
    //
    $strsql = "SELECT status, COUNT(status) AS count FROM ciniki_wineproductions "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ((status = 10 && order_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') OR "
            . "(status = 20 && start_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') OR "
            . "(status = 25 && start_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') OR "
            . "(status = 30 && rack_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') OR " 
            . "(status = 40 && filter_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') OR "
            . "(status = 60 && bottle_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') ) "
        . "GROUP BY status "
        . "";
    $rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'ciniki.wineproduction', 'stats', 'stat', array('stat'=>'ok', 'stats'=>array()));
    if( $rc['stat'] != 'ok' ) { 
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.25', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
    if( !isset($rc['stats']) ) {
        return array('stat'=>'ok', 'stats'=>array());
    }
    $workdone = $rc['stats'];

    //
    // Get Late Wines stats
    //
    $strsql = "SELECT status, COUNT(status) AS count FROM ciniki_wineproductions "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND status < 40 "
        . "AND bottling_date > 0 AND TIME(bottling_date) <> '00:00:00' AND (bottling_date < filtering_date "
            . "OR (filtering_date = 0 AND bottling_date < DATE_ADD(racking_date, INTERVAL (kit_length-2) WEEK)) "
            . "OR (racking_date = 0 AND bottling_date < DATE_ADD(start_date, INTERVAL kit_length WEEK)) "
            . "OR bottling_date < start_date) "
        . "GROUP BY status "
        . "";
    $rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'ciniki.wineproduction', 'stats', 'stat', array('stat'=>'ok', 'stats'=>array()));
    if( $rc['stat'] != 'ok' ) { 
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.26', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
    if( !isset($rc['stats']) ) {
        return array('stat'=>'ok', 'stats'=>array());
    }
    $latewines = $rc['stats'];

    //
    // Get Call to Book Stats
    //
    $strsql = "SELECT status, COUNT(status) AS count FROM ciniki_wineproductions "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND status < 60 "
        . "AND (TIME(bottling_date) = '00:00:00' OR bottling_date = '0000-00-00 00:00:00') "
        . "AND (filtering_date > 0 AND filtering_date < NOW()) "
        . "GROUP BY status "
        . "";
    $rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'ciniki.wineproduction', 'stats', 'stat', array('stat'=>'ok', 'stats'=>array()));
    if( $rc['stat'] != 'ok' ) { 
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.27', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
    if( !isset($rc['stats']) ) {
        return array('stat'=>'ok', 'stats'=>array());
    }
    $ctb = $rc['stats'];

    return array('stat'=>'ok', 'stats'=>$stats, 'past'=>$past_stats, 'todays'=>$todays_stats, 'future'=>$future_stats, 'workdone'=>$workdone, 'latewines'=>$latewines, 'ctb'=>$ctb);
}
?>
