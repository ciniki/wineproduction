<?php
//
// Description
// -----------
// This method will return a list of orders for a customer.
//
// Info
// ----
// Status:          started
//
// Arguments
// ---------
// user_id:         The user making the request
// 
// Returns
// -------
//
function ciniki_wineproduction_customerOrders($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'customer_id'=>array('required'=>'yes', 'default'=>'', 'name'=>'Customer'),
        'status'=>array('required'=>'no', 'default'=>'', 'name'=>'Status'),
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1512', 'msg'=>'Unable to understand request', 'err'=>$rc['err']));
    }   
    $args = $rc['args'];
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.customerOrders'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Load the status maps for the text description of each status
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'statusMaps');
    $rc = ciniki_wineproduction_statusMaps($ciniki);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $status_maps = $rc['maps'];

    //
    // FIXME: Add timezone information from business settings
    //
    date_default_timezone_set('America/Toronto');
    $todays_date = strftime("%Y-%m-%d");

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki);
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');

    //
    // Get the customer details
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'private', 'customerDetails');
    $rc = ciniki_customers__customerDetails($ciniki, $args['business_id'], $args['customer_id'], 
        array('emails'=>'yes', 'addresses'=>'no', 'subscriptions'=>'no'));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $customer = $rc['details'];

    //
    // Get the wine production orders for a customer
    //
    $strsql = "SELECT ciniki_wineproductions.id, "
        . "invoice_number, "
        . "ciniki_products.name AS wine_name, wine_type, kit_length, "
        . "ciniki_wineproductions.status, "
        . "ciniki_wineproductions.status AS status_text, "
        . "DATE_FORMAT(order_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS order_date, "
        . "DATE_FORMAT(ciniki_wineproductions.start_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS start_date, "
        . "DATE_FORMAT(racking_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS racking_date, "
        . "DATE_FORMAT(filtering_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS filtering_date, "
        . "DATE_FORMAT(bottling_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS bottling_date, "
        . "DATE_FORMAT(IF(rack_date > 0, DATE_ADD(rack_date, INTERVAL (kit_length) DAY), "
            . "DATE_ADD(ciniki_wineproductions.start_date, INTERVAL kit_length WEEK)), '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS approx_filtering_date "
        . "FROM ciniki_wineproductions "
        . "LEFT JOIN ciniki_products ON (ciniki_wineproductions.product_id = ciniki_products.id "
            . "AND ciniki_products.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "') "
        . "WHERE ciniki_wineproductions.customer_id = '" . ciniki_core_dbQuote($ciniki, $args['customer_id']) . "' "
        . "AND ciniki_wineproductions.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "";

    if( isset($args['status']) && $args['status'] != '' ) {
        $strsql .= "AND ciniki_wineproductions.status = '" . ciniki_core_dbQuote($ciniki, $args['status']) . "' ";
    }

    $strsql .= "ORDER BY ciniki_wineproductions.order_date DESC ";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'orders', 'fname'=>'id', 'name'=>'order',
            'fields'=>array('id', 'invoice_number', 'wine_name', 'wine_type', 'kit_length',
                'status', 'status_text',
                'order_date', 'start_date', 'racking_date', 'filtering_date', 'bottling_date',
                'approx_filtering_date'),
            'maps'=>array('status_text'=>$status_maps)),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    if( !isset($rc['orders']) ) {
        return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1513', 'msg'=>'Unable to find any orders'));
    }

    return array('stat'=>'ok', 'customer'=>$customer, 'orders'=>$rc['orders']);
}
?>
