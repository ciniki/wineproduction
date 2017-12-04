<?php
//
// Description
// -----------
// This method will return the detail for a wineproduction order.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant to get the wineproduction order for.
// wineproduction_id:   The ID of the wineproduction order to get.
// 
// Returns
// -------
//
function ciniki_wineproduction_getOrder($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'wineproduction_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Order'),
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.getOrder'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Load timezone info
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'datetimeFormat');

    $date_format = ciniki_users_dateFormat($ciniki);
    $php_date_format = ciniki_users_dateFormat($ciniki, 'php');
    $datetime_format = ciniki_users_datetimeFormat($ciniki);
    $php_datetime_format = ciniki_users_datetimeFormat($ciniki, 'php');

    $strsql = "SELECT ciniki_wineproductions.id, ciniki_wineproductions.customer_id, "
        . "invoice_number, "
        . "ciniki_products.id as product_id, ciniki_products.name AS wine_name, wine_type, kit_length, "
        . "ciniki_wineproductions.status, colour_tag, order_flags, rack_colour, filter_colour, "
        . "DATE_FORMAT(ciniki_wineproductions.order_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as order_date, "
        . "DATE_FORMAT(ciniki_wineproductions.start_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as start_date, "
        . "DATE_FORMAT(ciniki_wineproductions.racking_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as racking_date, "
        . "DATE_FORMAT(ciniki_wineproductions.rack_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as rack_date, "
        . "sg_reading, "
        . "DATE_FORMAT(ciniki_wineproductions.filtering_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as filtering_date, "
        . "DATE_FORMAT(ciniki_wineproductions.filter_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as filter_date, "
//      . "DATE_FORMAT(ciniki_wineproductions.bottling_date, '" . ciniki_core_dbQuote($ciniki, $datetime_format) . "') as bottling_date, "
        . "bottling_date, "
        . "DATE_FORMAT(ciniki_wineproductions.bottle_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as bottle_date, "
        . "bottling_flags, bottling_nocolour_flags, bottling_status, bottling_duration, "
        . "ciniki_wineproductions.notes, "
        . "ciniki_wineproductions.batch_code "
        . "FROM ciniki_wineproductions "
//      . "LEFT JOIN ciniki_customers ON (ciniki_wineproductions.customer_id = ciniki_customers.id "
//          . "AND ciniki_customers.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "') "
        . "LEFT JOIN ciniki_products ON (ciniki_wineproductions.product_id = ciniki_products.id "
            . "AND ciniki_products.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "') "
        . "WHERE ciniki_wineproductions.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ciniki_wineproductions.id = '" . ciniki_core_dbQuote($ciniki, $args['wineproduction_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'orders', 'fname'=>'id', 'name'=>'order',
            'fields'=>array('id', 'customer_id', 'invoice_number', 'product_id', 'wine_name', 'wine_type', 'kit_length', 'status', 'colour_tag', 'order_flags',
                'rack_colour', 'order_flags', 'rack_colour', 'filter_colour', 
                'order_date', 'start_date', 'racking_date', 'rack_date', 'sg_reading', 'filtering_date', 'filter_date', 'bottling_date', 'bottle_date',
                'bottling_flags', 'bottling_nocolour_flags', 'bottling_status', 'bottling_duration', 'notes', 'batch_code', 
                ),
            'utctotz'=>array('bottling_date'=>array('timezone'=>$intl_timezone, 'format'=>$php_datetime_format))),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['orders'][0]['order']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.16', 'msg'=>'Invalid order'));
    }
    $order = $rc['orders'][0]['order'];

    //
    // Get the customer details
    //
    $order['customer'] = array();
    $order['customer_name'] = '';
    if( $order['customer_id'] > 0 ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'private', 'customerDetails');
        $rc = ciniki_customers__customerDetails($ciniki, $args['tnid'], $order['customer_id'], 
            array('phones'=>'yes', 'emails'=>'yes', 'addresses'=>'no', 'subscriptions'=>'no'));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $order['customer'] = $rc['details'];
//      $strsql = "SELECT ciniki_customers.id, type, "
//          . "ciniki_customers.display_name, "
//          . "ciniki_customers.company, "
//          . "ciniki_customer_emails.email AS emails "
//          . "FROM ciniki_customers "
//          . "LEFT JOIN ciniki_customer_emails ON (ciniki_customers.id = ciniki_customer_emails.customer_id "
//              . "AND ciniki_customer_emails.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
//              . ") "
//          . "WHERE ciniki_customers.id = '" . ciniki_core_dbQuote($ciniki, $order['customer_id']) . "' "
//          . "AND ciniki_customers.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
//          . "";
//      $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.customers', array(
//          array('container'=>'customers', 'fname'=>'id', 'name'=>'customer',
//              'fields'=>array('id', 'type', 'display_name',
//                  'emails'),
//              'lists'=>array('emails'),
//              ),
//          ));
//      if( $rc['stat'] != 'ok' ) {
//          return $rc;
//      }
//      if( isset($rc['customers']) && isset($rc['customers'][0]['customer']) ) {
//          $order['customer'] = $rc['customers'][0]['customer'];
//      }
    }

    $order['bottling_date'] = preg_replace('/ 12:00 AM/', '', $order['bottling_date']);
    return array('stat'=>'ok', 'order'=>$order);
}
?>
