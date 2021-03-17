<?php
//
// Description
// -----------
// This method will return the list of actions that were applied to an element of an wine production order.
// This method is typically used by the UI to display a list of changes that have occured
// on an element through time. This information can be used to revert elements to a previous value.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant to get the details for.
// order_id:          The ID of the wine production order to get the history for.
// field:                   The field to get the history for.
//
// Returns
// -------
//
function ciniki_wineproduction_orderHistory($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'order_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Wine Production Order'),
        'field'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'field'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.orderHistory');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    if( $args['field'] == 'order_date' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistoryReformat');
        return ciniki_core_dbGetModuleHistoryReformat($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['tnid'], 'ciniki_wineproductions', $args['order_id'], $args['field'], 'date');
    }

    if( $args['field'] == 'start_date' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistoryReformat');
        return ciniki_core_dbGetModuleHistoryReformat($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['tnid'], 'ciniki_wineproductions', $args['order_id'], $args['field'], 'date');
    }

    if( $args['field'] == 'transferring_date' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistoryReformat');
        return ciniki_core_dbGetModuleHistoryReformat($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['tnid'], 'ciniki_wineproductions', $args['order_id'], $args['field'], 'date');
    }

    if( $args['field'] == 'transfer_date' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistoryReformat');
        return ciniki_core_dbGetModuleHistoryReformat($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['tnid'], 'ciniki_wineproductions', $args['order_id'], $args['field'], 'date');
    }

    if( $args['field'] == 'racking_date' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistoryReformat');
        return ciniki_core_dbGetModuleHistoryReformat($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['tnid'], 'ciniki_wineproductions', $args['order_id'], $args['field'], 'date');
    }

    if( $args['field'] == 'rack_date' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistoryReformat');
        return ciniki_core_dbGetModuleHistoryReformat($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['tnid'], 'ciniki_wineproductions', $args['order_id'], $args['field'], 'date');
    }

    if( $args['field'] == 'filtering_date' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistoryReformat');
        return ciniki_core_dbGetModuleHistoryReformat($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['tnid'], 'ciniki_wineproductions', $args['order_id'], $args['field'], 'date');
    }

    if( $args['field'] == 'filter_date' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistoryReformat');
        return ciniki_core_dbGetModuleHistoryReformat($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['tnid'], 'ciniki_wineproductions', $args['order_id'], $args['field'], 'date');
    }

    if( $args['field'] == 'bottle_date' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistoryReformat');
        return ciniki_core_dbGetModuleHistoryReformat($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['tnid'], 'ciniki_wineproductions', $args['order_id'], $args['field'], 'date');
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistory');
    return ciniki_core_dbGetModuleHistory($ciniki, 'ciniki.wineproduction', 'ciniki_wineproduction_history', $args['tnid'], 'ciniki_wineproductions', $args['order_id'], $args['field']);
}
?>
