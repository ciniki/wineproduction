<?php
//
// Description
// -----------
// This method will update an existing wineproduction order.
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
function ciniki_wineproduction_update(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'wineproduction_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Order'), 
        'customer_id'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Customer'), 
        'invoice_number'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Invoice'), 
        'product_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Wine'), 
        'wine_type'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Type'), 
        'kit_length'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Kit Racking Length'), 
        'status'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Status'), 
        'colour_tag'=>array('required'=>'no', 'blank'=>'yes', 'null'=>'', 'name'=>'Colour Tag'), 
        'rack_colour'=>array('required'=>'no', 'blank'=>'yes', 'null'=>'', 'name'=>'Rack Colour'), 
        'filter_colour'=>array('required'=>'no', 'blank'=>'yes', 'null'=>'', 'name'=>'Filter Colour'), 
        'order_flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Order Date Flags'), 
        'order_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'Order Date'), 
        'start_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'Start Date'), 
        'sg_reading'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'SG Reading'), 
        'racking_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'Racking Date'), 
        'rack_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'Rack Date'), 
        'filtering_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'Filtering Date'), 
        'filter_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'Filter Date'), 
        'bottling_duration'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Bottling Duration'), 
        'bottling_flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Bottling Flags'), 
        'bottling_nocolour_flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Bottling Colour Flags'), 
        'bottling_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'datetimetoutc', 'name'=>'Bottling Date'), 
        'bottling_status'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Bottling Status'), 
        'bottle_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'Bottle Date'), 
        'notes'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Notes'), 
        'batch_code'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Batch Code'), 
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.update'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Update the order
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    return ciniki_core_objectUpdate($ciniki, $args['tnid'], 'ciniki.wineproduction.order', 
        $args['wineproduction_id'], $args, 0x07);
}
?>
