<?php
//
// Description
// -----------
// 
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wineproduction_purchaseOrderImport(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'order_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Purchase Order'),
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.purchaseOrderImport');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'purchaseOrderPullItems');
    $rc = ciniki_wineproduction_purchaseOrderPullItems($ciniki, $args['tnid'], $args['order_id']);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.193', 'msg'=>'Unable to pull items', 'err'=>$rc['err']));
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'public', 'purchaseOrderGet');
    return ciniki_wineproduction_purchaseOrderGet($ciniki);
}
?>
