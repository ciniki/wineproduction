<?php
//
// Description
// -----------
// This method returns the customers who have made wine and the number of batches made per year.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:     The ID of the tenant to get the wineproduction statistics for.
// 
// Returns
// -------
//
function ciniki_wineproduction_reportCellarNights($ciniki) {
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
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.reportCellarNights'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Load the tenant settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];

    //
    // Load maps
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'maps');
    $rc = ciniki_wineproduction_maps($ciniki);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $maps = $rc['maps'];

    //
    // Load the date format strings for the user
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'datetimeFormat');
    $datetime_format = ciniki_users_datetimeFormat($ciniki, 'php');
    
    //
    // Get the list of orders for cellar nights and their bottling status
    //
    $strsql = "SELECT orders.id, "
        . "customers.id AS customer_id, "
        . "customers.display_name, "
        . "orders.invoice_number, "
        . "orders.status, "
        . "orders.status AS status_text, "
        . "orders.bottling_date, "
        . "orders.bottling_status, "
        . "orders.bottling_status AS bottling_status_text "
        . "FROM ciniki_wineproductions AS orders "
        . "LEFT JOIN ciniki_customers AS customers ON ("
            . "orders.customer_id = customers.id "
            . "AND customers.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") "
        . "WHERE orders.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND orders.invoice_number LIKE '%CN%' "
        . "ORDER BY orders.invoice_number ASC "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'orders', 'fname'=>'customer_id', 
            'fields'=>array('id', 'status', 'status_text', 'customer_id', 'display_name', 'invoice_number', 
                'bottling_date', 'bottling_status', 'bottling_status_text',
                ),
            'utctotz'=>array('bottling_date'=>array('timezone'=>$intl_timezone, 'format'=>$datetime_format)),
            'maps'=>array(
                'status_text'=>$maps['wineproduction']['status'],
                'bottling_status_text'=>$maps['wineproduction']['bottling_status'],
                ),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.39', 'msg'=>'Unable to load ', 'err'=>$rc['err']));
    }
    $orders = isset($rc['orders']) ? $rc['orders'] : array();

    foreach($orders as $oid => $order) {
    }

    return array('stat'=>'ok', 'orders'=>$orders);
}
?>
