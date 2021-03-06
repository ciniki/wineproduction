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
// tnid:     The ID of the tenant to get the wineproduction statistics for.
// 
// Returns
// -------
// <stats>
//  <stat status='10' count='13' workdone=0 latewines=0 />
// </stats>
//
function ciniki_wineproduction_statsCTB($ciniki) {
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.statsCTB'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // FIXME: Add timezone information
    //
//  date_default_timezone_set('America/Toronto');
//  $todays_date = strftime("%Y-%m-%d");
//  $today_plus_four = strftime("%Y-%m-%d", time()+345600);

    //
    // Get Call to Book Stats
    //
    $strsql = "SELECT 'ctb', COUNT(*) AS count FROM ciniki_wineproductions "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND status < 60 "
        . "AND (TIME(bottling_date) = '00:00:00' OR bottling_date = '0000-00-00 00:00:00') "
        . "AND (filtering_date > 0 AND filtering_date < NOW()) "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbRspQuery');
    $rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'ciniki.wineproduction', 'stats', 'stat', array('stat'=>'ok', 'stats'=>array()));
    if( $rc['stat'] != 'ok' ) { 
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.28', 'msg'=>'Unable to retrieve statistics', 'err'=>$rc['err']));
    }
    if( !isset($rc['stats']) || !isset($rc['stats'][0]['stat'])) {
        return array('stat'=>'ok', 'ctb'=>'0');
    }
    $ctb = $rc['stats'][0]['stat']['count'];

    return array('stat'=>'ok', 'ctb'=>$ctb);
}
?>
