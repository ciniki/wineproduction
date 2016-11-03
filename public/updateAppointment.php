<?php
//
// Description
// -----------
//
// Info
// ----
// Status:          defined
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_wineproduction_updateAppointment(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'wineproduction_ids'=>array('required'=>'yes', 'type'=>'idlist', 'blank'=>'no', 'name'=>'Order'), 
        'bottling_duration'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Bottling Duration'), 
        'bottling_flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Bottling Flags'), 
        'bottling_nocolour_flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Bottling Colour Flags'), 
        'bottling_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'datetimetoutc', 'name'=>'Bottling Date'), 
        'bottling_notes'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Bottling notes'), 
        'bottled'=>array('required'=>'no', 'name'=>'Bottled Flag'), 
        'customer_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Customer'), 
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.updateAppointment'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //  
    // Turn off autocommit
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuoteIDs');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpdate');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.wineproduction');
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
            $rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.wineproduction', 
                'ciniki_wineproduction_history', $args['business_id'], 
                2, 'ciniki_wineproductions', $wid, 'bottle_date', $todays_date);
            $rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.wineproduction', 
                'ciniki_wineproduction_history', $args['business_id'], 
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
            $rc = ciniki_core_dbUpdate($ciniki, $strsql_a, 'ciniki.wineproduction');
            if( $rc['stat'] != 'ok' ) { 
                ciniki_core_dbTransactionRollback($ciniki, 'ciniki.wineproduction');
                return $rc;
            }

            $rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['business_id'], 
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
        'customer_id',
        );
    foreach($changelog_fields as $field) {
        if( isset($args[$field]) ) {
            $strsql .= ", $field = '" . ciniki_core_dbQuote($ciniki, $args[$field]) . "' ";
            foreach($args['wineproduction_ids'] as $wid) {
                $rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.wineproduction', 
                    'ciniki_wineproduction_history', $args['business_id'], 
                    2, 'ciniki_wineproductions', $wid, $field, $args[$field]);
            }
        }
    }
    $strsql .= "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "AND id IN (" . ciniki_core_dbQuoteIDs($ciniki, $args['wineproduction_ids']) . ") ";
    $rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.wineproduction');
    if( $rc['stat'] != 'ok' ) { 
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.wineproduction');
        return $rc;
    }
    if( !isset($rc['num_affected_rows']) || $rc['num_affected_rows'] < 1 ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.wineproduction');
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.38', 'msg'=>'Unable to add order'));
    }

    //
    // Commit the database changes
    //
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.wineproduction');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the business modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
    ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'wineproduction');

    foreach($args['wineproduction_ids'] as $wid) {
        $ciniki['syncqueue'][] = array('push'=>'ciniki.wineproduction.order',
            'args'=>array('id'=>$wid));
    }

    return array('stat'=>'ok');
}
?>
