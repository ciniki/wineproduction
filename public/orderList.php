<?php
//
// Description
// -----------
// This method will return the list of Wine Production Orders for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Wine Production Order for.
//
// Returns
// -------
//
function ciniki_wineproduction_orderList($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.orderList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of orders
    //
    $strsql = "SELECT ciniki_wineproductions.id, "
        . "ciniki_wineproductions.parent_id, "
        . "ciniki_wineproductions.customer_id, "
        . "ciniki_wineproductions.invoice_id, "
        . "ciniki_wineproductions.invoice_number, "
        . "ciniki_wineproductions.batch_letter, "
        . "ciniki_wineproductions.product_id, "
        . "ciniki_wineproductions.wine_type, "
        . "ciniki_wineproductions.kit_length, "
        . "ciniki_wineproductions.status, "
        . "ciniki_wineproductions.rack_colour, "
        . "ciniki_wineproductions.filter_colour, "
        . "ciniki_wineproductions.location, "
        . "ciniki_wineproductions.flags, "
        . "ciniki_wineproductions.order_flags, "
        . "ciniki_wineproductions.order_date, "
        . "ciniki_wineproductions.start_date, "
        . "ciniki_wineproductions.tsg_reading, "
        . "ciniki_wineproductions.transferring_date, "
        . "ciniki_wineproductions.transfer_date, "
        . "ciniki_wineproductions.sg_reading, "
        . "ciniki_wineproductions.racking_date, "
        . "ciniki_wineproductions.rack_date, "
        . "ciniki_wineproductions.filtering_date, "
        . "ciniki_wineproductions.filter_date, "
        . "ciniki_wineproductions.bottling_flags, "
        . "ciniki_wineproductions.bottling_nocolour_flags, "
        . "ciniki_wineproductions.bottling_duration, "
        . "ciniki_wineproductions.bottling_date, "
        . "ciniki_wineproductions.bottling_status, "
        . "ciniki_wineproductions.bottle_date, "
        . "ciniki_wineproductions.batch_code "
        . "FROM ciniki_wineproductions "
        . "WHERE ciniki_wineproductions.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'orders', 'fname'=>'id', 
            'fields'=>array('id', 'parent_id', 'customer_id', 'invoice_id', 'invoice_number', 'batch_letter', 'product_id', 'wine_type', 'kit_length', 'status', 'rack_colour', 'filter_colour', 'location', 'flags', 'order_flags', 'order_date', 'start_date', 'tsg_reading', 'transferring_date', 'transfer_date', 'sg_reading', 'racking_date', 'rack_date', 'filtering_date', 'filter_date', 'bottling_flags', 'bottling_nocolour_flags', 'bottling_duration', 'bottling_date', 'bottling_status', 'bottle_date', 'batch_code')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['orders']) ) {
        $orders = $rc['orders'];
        $order_ids = array();
        foreach($orders as $iid => $order) {
            $order_ids[] = $order['id'];
        }
    } else {
        $orders = array();
        $order_ids = array();
    }

    return array('stat'=>'ok', 'orders'=>$orders, 'nplist'=>$order_ids);
}
?>
