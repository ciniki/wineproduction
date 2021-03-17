<?php
//
// Description
// -----------
// This method will add a new wine production order for the tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to add the Wine Production Order to.
//
// Returns
// -------
//
function ciniki_wineproduction_orderAdd(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'parent_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Primary Order'),
        'customer_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Customer'),
        'invoice_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Invoice'),
        'invoice_number'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Invoice Number'),
        'batch_letter'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Batch'),
        'product_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Product'),
        'wine_type'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Wine Type'),
        'kit_length'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Kit Length'),
        'status'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Status'),
        'rack_colour'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Rack Colour'),
        'filter_colour'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Filter Colour'),
        'location'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Location'),
        'flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Options'),
        'order_flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Order Flags'),
        'order_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'Order Date'),
        'start_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'Start Date'),
        'tsg_reading'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Transfer SG Reading'),
        'transferring_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'Transferring Date'),
        'transfer_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'Transferred Date'),
        'sg_reading'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'SG Reading'),
        'racking_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'Racking Date'),
        'rack_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'Racked Date'),
        'filtering_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'Filtering Date'),
        'filter_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'Filtered Date'),
        'bottling_flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Bottling Options'),
        'bottling_nocolour_flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Bottling Options 2'),
        'bottling_duration'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Bottling Duration'),
        'bottling_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'datetimetoutc', 'name'=>'Bottling Date'),
        'bottling_status'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Bottling Status'),
        'bottling_notes'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Bottling Notes'),
        'bottle_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'Bottled Date'),
        'notes'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Notes'),
        'batch_code'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Batch Code'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.orderAdd');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Start transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.wineproduction');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Add the wine production order to the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.wineproduction.order', $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.wineproduction');
        return $rc;
    }
    $order_id = $rc['id'];

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

    //
    // Update the web index if enabled
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'hookExec');
    ciniki_core_hookExec($ciniki, $args['tnid'], 'ciniki', 'web', 'indexObject', array('object'=>'ciniki.wineproduction.order', 'object_id'=>$order_id));

    return array('stat'=>'ok', 'id'=>$order_id);
}
?>
