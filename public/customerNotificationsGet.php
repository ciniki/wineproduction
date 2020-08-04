<?php
//
// Description
// -----------
// This method returns the wine production notifications for a customer.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wineproduction_customerNotificationsGet(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'customer_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Customer'),
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.customerNotificationsGet');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load the notifications
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'customerNotifications');
    $rc = ciniki_wineproduction_customerNotifications($ciniki, $args['tnid'], $args['customer_id']);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.65', 'msg'=>'Unable to load notifications', 'err'=>$rc['err']));
    }

    return $rc;
}
?>
