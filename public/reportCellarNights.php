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
        'year'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Year'), 
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
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'php');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'datetimeFormat');
    $datetime_format = ciniki_users_datetimeFormat($ciniki, 'php');

    //
    // Get the years of orders
    //
    $strsql = "SELECT DISTINCT DATE_FORMAT(order_date, '%Y') AS year "
        . "FROM ciniki_wineproductions "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND invoice_number LIKE '%CN%' "
        . "ORDER BY year "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList');
    $rc = ciniki_core_dbQueryList($ciniki, $strsql, 'ciniki.wineproduction', 'years', 'year');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.40', 'msg'=>'Unable to load the list of years', 'err'=>$rc['err']));
    }
    $years = isset($rc['years']) ? $rc['years'] : array();

    //
    // Set the default year to the last year
    //
    if( !isset($args['year']) || !in_array($args['year'], $years)) {
        $args['year'] = end($years);
    } 

    //
    // Get the list of orders for cellar nights and their bottling status
    //
    $strsql = "SELECT orders.id, "
        . "customers.id AS customer_id, "
        . "customers.display_name, "
        . "orders.invoice_number, "
        . "orders.order_date, "
        . "orders.order_date AS order_year, "
        . "orders.status, "
        . "orders.status AS status_text, "
        . "orders.bottling_date, "
        . "orders.bottling_status, "
        . "orders.bottling_status AS bottling_status_text, "
        . "products.name AS product_name "
        . "FROM ciniki_wineproductions AS orders "
        . "LEFT JOIN ciniki_customers AS customers ON ("
            . "orders.customer_id = customers.id "
            . "AND customers.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") "
        . "LEFT JOIN ciniki_products AS products ON ("
            . "orders.product_id = products.id "
            . "AND products.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") "
        . "WHERE orders.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND orders.invoice_number LIKE '%CN%' "
        . "AND YEAR(orders.order_date) = '" . ciniki_core_dbQuote($ciniki, $args['year']) . "' "
        . "ORDER BY orders.invoice_number ASC "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'orders', 'fname'=>'customer_id', 
            'fields'=>array('id', 'status', 'status_text', 'order_date', 'order_year',
                'customer_id', 'display_name', 'invoice_number', 
                'bottling_date', 'bottling_status', 'bottling_status_text', 'product_name',
                ),
            'utctotz'=>array(
                'order_date'=>array('timezone'=>$intl_timezone, 'format'=>$date_format),
                'order_year'=>array('timezone'=>$intl_timezone, 'format'=>'Y'),
                'bottling_date'=>array('timezone'=>$intl_timezone, 'format'=>$datetime_format),
                ),
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

    return array('stat'=>'ok', 'orders'=>$orders, 'year'=>$args['year'], 'years'=>$years);
}
?>
