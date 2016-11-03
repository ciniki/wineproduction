<?php
//
// Description
// -----------
// This function will return the list of bottling appointments for a business
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business to get the details for.
//
// Returns
// -------
//  <events>
//      <event customer_name="" invoice_number="" wine_name="" />
//  </events>
//
function ciniki_wineproduction_appointments($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'date'=>array('required'=>'no', 'default'=>'today', 'blank'=>'yes', 'name'=>'Date'), 
        'startdate'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Start Date'), 
        'enddate'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'End Date'), 
        'appointment_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Appointment'), 
        'customer_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Customer'), 
        'status'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Status'), 
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check that date or customer_id is specified
    //
    if( !isset($args['date']) || !isset($args['customer_id']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.13', 'msg'=>'No customer or date specified'));
    }
    
    //
    // Check access to business_id as owner, or sys admin
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.appointments');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'hooks', 'appointments');
    return ciniki_wineproduction_hooks_appointments($ciniki, $args['business_id'], $args);
}
?>
