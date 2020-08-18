<?php
//
// Description
// -----------
// Return the summary of wines processed today
//
// Arguments
// ---------
// ciniki:
// tnid:         The ID of the tenant to get the birthdays for.
// args:                The options for the query.
//
// Additional Arguments
// --------------------
// days:                The number of days past to look for new wineproduction.
// 
// Returns
// -------
//
function ciniki_wineproduction_reporting_blockWinesProcessedSummary(&$ciniki, $tnid, $args) {
    //
    // Get the tenant settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $tnid);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuoteIDs');

    $date_format = "M j, Y";
    $datetime_format = "M j, Y g:i A";

    //
    // Load maps
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'maps');
    $rc = ciniki_wineproduction_maps($ciniki);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $maps = $rc['maps'];

    $start_dt = new DateTime('now', new DateTimezone($intl_timezone));
    $today_date = clone $start_dt;
    $start_dt->setTime(23,59,59);
    $end_dt = clone $start_dt;
    if( isset($args['days']) && $args['days'] > 0 ) {
        $start_dt->sub(new DateInterval('P' . $args['days'] . 'D'));
    } else {
        $start_dt->sub(new DateInterval('P1D'));
    }
    $start_dt->setTimezone(new DateTimezone('UTC'));
    $end_dt->setTimezone(new DateTimezone('UTC'));

    //
    // Get the number of wines ordered today
    //
    $strsql = "SELECT COUNT(*) AS num "
        . "FROM ciniki_wineproductions "
        . "WHERE ciniki_wineproductions.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND ciniki_wineproductions.order_date = '" . ciniki_core_dbQuote($ciniki, $today_date->format('Y-m-d')) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbSingleCount');
    $rc = ciniki_core_dbSingleCount($ciniki, $strsql, 'ciniki.wineproduction', 'num');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.103', 'msg'=>'Unable to load get the number of ordered wines', 'err'=>$rc['err']));
    }
    $num_ordered = isset($rc['num']) ? $rc['num'] : '';

    //
    // Get the number of wines started today
    //
    $strsql = "SELECT COUNT(*) AS num "
        . "FROM ciniki_wineproductions "
        . "WHERE ciniki_wineproductions.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND ciniki_wineproductions.start_date = '" . ciniki_core_dbQuote($ciniki, $today_date->format('Y-m-d')) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbSingleCount');
    $rc = ciniki_core_dbSingleCount($ciniki, $strsql, 'ciniki.wineproduction', 'num');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.103', 'msg'=>'Unable to load get the number of started wines', 'err'=>$rc['err']));
    }
    $num_started = isset($rc['num']) ? $rc['num'] : '';

    //
    // Get the number of wines racked today
    //
    $strsql = "SELECT COUNT(*) AS num "
        . "FROM ciniki_wineproductions "
        . "WHERE ciniki_wineproductions.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND ciniki_wineproductions.rack_date = '" . ciniki_core_dbQuote($ciniki, $today_date->format('Y-m-d')) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbSingleCount');
    $rc = ciniki_core_dbSingleCount($ciniki, $strsql, 'ciniki.wineproduction', 'num');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.103', 'msg'=>'Unable to load get the number of racked wines', 'err'=>$rc['err']));
    }
    $num_racked = isset($rc['num']) ? $rc['num'] : '';

    //
    // Get the number of wines filtered today
    //
    $strsql = "SELECT COUNT(*) AS num "
        . "FROM ciniki_wineproductions "
        . "WHERE ciniki_wineproductions.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND ciniki_wineproductions.filter_date = '" . ciniki_core_dbQuote($ciniki, $today_date->format('Y-m-d')) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbSingleCount');
    $rc = ciniki_core_dbSingleCount($ciniki, $strsql, 'ciniki.wineproduction', 'num');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.103', 'msg'=>'Unable to load get the number of filtered wines', 'err'=>$rc['err']));
    }
    $num_filtered = isset($rc['num']) ? $rc['num'] : '';

    //
    // Get the number of wines bottled today
    //
    $strsql = "SELECT COUNT(*) AS num "
        . "FROM ciniki_wineproductions "
        . "WHERE ciniki_wineproductions.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND ciniki_wineproductions.bottle_date = '" . ciniki_core_dbQuote($ciniki, $today_date->format('Y-m-d')) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbSingleCount');
    $rc = ciniki_core_dbSingleCount($ciniki, $strsql, 'ciniki.wineproduction', 'num');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.103', 'msg'=>'Unable to load get the number of bottled wines', 'err'=>$rc['err']));
    }
    $num_bottled = isset($rc['num']) ? $rc['num'] : '';

    //
    // Create the report blocks
    //
    $chunk = array(
        'type'=>'table',
        'columns'=>array(
            array('label'=>'Ordered', 'pdfwidth'=>'20%', 'field'=>'ordered'),
            array('label'=>'Started', 'pdfwidth'=>'20%', 'field'=>'started'),
            array('label'=>'Racked', 'pdfwidth'=>'20%', 'field'=>'racked'),
            array('label'=>'Filtered', 'pdfwidth'=>'20%', 'field'=>'filtered'),
            array('label'=>'Bottled', 'pdfwidth'=>'20%', 'field'=>'bottled'),
            ),
        'data'=>array(
            array('ordered'=>' ' . $num_ordered,
                'started'=>' ' . $num_started,
                'racked'=>' ' . $num_racked,
                'filtered'=>' ' . $num_filtered,
                'bottled'=>' ' . $num_bottled,
                ),
            ),
        'textlist'=>"Ordered: {$num_ordered}\n"
            . "Started: {$num_started}\n"
            . "Racked: {$num_racked}\n"
            . "Filtered: {$num_filtered}\n"
            . "Bottled: {$num_bottled}\n"
            . "",
        );
    $chunks[] = $chunk;
    
    return array('stat'=>'ok', 'chunks'=>$chunks);
}
?>
