<?php
//
// Description
// -----------
// This method will delete an product.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:            The ID of the tenant the product is attached to.
// product_id:            The ID of the product to be removed.
//
// Returns
// -------
//
function ciniki_wineproduction_productDelete(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'product_id'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Product'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.productDelete');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the current settings for the product
    //
    $strsql = "SELECT id, uuid "
        . "FROM ciniki_wineproduction_products "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['product_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'product');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['product']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.118', 'msg'=>'Product does not exist.'));
    }
    $product = $rc['product'];

    //
    // Check for any dependencies before deleting
    //
    $strsql = "SELECT COUNT(id) AS num_items "
        . "FROM ciniki_wineproduction_product_tags "
        . "WHERE product_id = '" . ciniki_core_dbQuote($ciniki, $args['product_id']) . "' "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbSingleCount');
    $rc = ciniki_core_dbSingleCount($ciniki, $strsql, 'ciniki.ags', 'num');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( $rc['num'] > 0 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.121', 'msg'=>'There are orders for this product and cannot be removed.'));
    }

    //
    // Check if any modules are currently using this object
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectCheckUsed');
    $rc = ciniki_core_objectCheckUsed($ciniki, $args['tnid'], 'ciniki.wineproduction.product', $args['product_id']);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.119', 'msg'=>'Unable to check if the product is still being used.', 'err'=>$rc['err']));
    }
    if( $rc['used'] != 'no' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.120', 'msg'=>'The product is still in use. ' . $rc['msg']));
    }

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
    // Remove the product
    //
    $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.wineproduction.product',
        $args['product_id'], $product['uuid'], 0x04);
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
