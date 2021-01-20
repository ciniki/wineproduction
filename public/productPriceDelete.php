<?php
//
// Description
// -----------
// This method will delete an price.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:            The ID of the tenant the price is attached to.
// price_id:            The ID of the price to be removed.
//
// Returns
// -------
//
function ciniki_wineproduction_productPriceDelete(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'price_id'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Price'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.productPriceDelete');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the current settings for the price
    //
    $strsql = "SELECT id, uuid "
        . "FROM ciniki_wineproduction_product_pricing "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['price_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'price');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['price']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.159', 'msg'=>'Price does not exist.'));
    }
    $price = $rc['price'];

    //
    // Check for any dependencies before deleting
    //
    $strsql = "SELECT COUNT(products.id) AS num "
        . "WHERE ("
            . "kit_price_id = '" . ciniki_core_dbQuote($ciniki, $args['price_id']) . "' "
            . "OR processing_price_id = '" . ciniki_core_dbQuote($ciniki, $args['price_id']) . "' "
            . ") "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbSingleCount');
    $rc = ciniki_core_dbSingleCount($ciniki, $strsql, 'ciniki.wineproduction', 'num');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.143', 'msg'=>'Unable to load get the number of items', 'err'=>$rc['err']));
    }
    if( isset($rc['num']) && $rc['num'] > 0 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.164', 'msg'=>'The price is still in use.'));
    }

    //
    // Check if any modules are currently using this object
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectCheckUsed');
    $rc = ciniki_core_objectCheckUsed($ciniki, $args['tnid'], 'ciniki.wineproduction.productPrice', $args['price_id']);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.160', 'msg'=>'Unable to check if the price is still being used.', 'err'=>$rc['err']));
    }
    if( $rc['used'] != 'no' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.161', 'msg'=>'The price is still in use. ' . $rc['msg']));
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
    // Remove the price
    //
    $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.wineproduction.productprice',
        $args['price_id'], $price['uuid'], 0x04);
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
