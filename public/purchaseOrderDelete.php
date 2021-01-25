<?php
//
// Description
// -----------
// This method will delete an purchase order.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:            The ID of the tenant the purchase order is attached to.
// order_id:            The ID of the purchase order to be removed.
//
// Returns
// -------
//
function ciniki_wineproduction_purchaseOrderDelete(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'order_id'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Purchase Order'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.purchaseOrderDelete');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the current settings for the purchase order
    //
    $strsql = "SELECT id, uuid "
        . "FROM ciniki_wineproduction_purchaseorders "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['order_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'purchaseorder');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['purchaseorder']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.184', 'msg'=>'Purchase Order does not exist.'));
    }
    $purchaseorder = $rc['purchaseorder'];

    //
    // Check for any dependencies before deleting
    //

    //
    // Check if any modules are currently using this object
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectCheckUsed');
    $rc = ciniki_core_objectCheckUsed($ciniki, $args['tnid'], 'ciniki.wineproduction.purchaseOrder', $args['order_id']);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.185', 'msg'=>'Unable to check if the purchase order is still being used.', 'err'=>$rc['err']));
    }
    if( $rc['used'] != 'no' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.186', 'msg'=>'The purchase order is still in use. ' . $rc['msg']));
    }

    //
    // Get the list of items
    //
    $strsql = "SELECT items.id, "
        . "items.uuid "
        . "FROM ciniki_wineproduction_purchaseorder_items AS items "
        . "WHERE items.order_id = '" . ciniki_core_dbQuote($ciniki, $purchaseorder['id']) . "' "
        . "AND items.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'items', 'fname'=>'id', 'fields'=>array('id', 'uuid')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.189', 'msg'=>'Unable to load items', 'err'=>$rc['err']));
    }
    $items = isset($rc['items']) ? $rc['items'] : array();

    //
    // Start transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.wineproduction');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Remove items
    //
    foreach($items as $item) {
        $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.wineproduction.purchaseorderitem',
            $item['id'], $item['uuid'], 0x04);
        if( $rc['stat'] != 'ok' ) {
            ciniki_core_dbTransactionRollback($ciniki, 'ciniki.wineproduction');
            return $rc;
        }
    }

    //
    // Remove the purchaseorder
    //
    $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.wineproduction.purchaseorder',
        $args['order_id'], $purchaseorder['uuid'], 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.wineproduction');
        return $rc;
    }

    //
    // Commit the transaction
    //
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.wineproduction');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $args['tnid'], 'ciniki', 'wineproduction');

    return array('stat'=>'ok');
}
?>
