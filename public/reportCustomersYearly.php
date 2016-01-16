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
// business_id:		The ID of the business to get the wineproduction statistics for.
// 
// Returns
// -------
//
function ciniki_wineproduction_reportCustomersYearly($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.reportCustomersYearly'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    $start_year = 2011;
    $end_year = 2015;

    $strsql = "SELECT ciniki_wineproductions.customer_id, "
        . "ciniki_customers.display_name, "
        . "DATE_FORMAT(ciniki_wineproductions.order_date, '%Y') AS year, "
        . "COUNT(ciniki_wineproductions.id) AS num_orders "
        . "FROM ciniki_wineproductions "
        . "LEFT JOIN ciniki_customers ON ("
            . "ciniki_wineproductions.customer_id = ciniki_customers.id "
            . "AND ciniki_customers.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . ") "
        . "WHERE ciniki_wineproductions.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "GROUP BY ciniki_wineproductions.customer_id, year "
        . "HAVING year >= '" . ciniki_core_dbQuote($ciniki, $start_year) . "' "
        . "AND year <= '" . ciniki_core_dbQuote($ciniki, $end_year) . "' "
        . "ORDER BY ciniki_customers.display_name, year ASC "
        . "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'customers', 'fname'=>'customer_id', 'fields'=>array('id'=>'customer_id', 'display_name')),
        array('container'=>'years', 'fname'=>'year', 'fields'=>array('num_orders')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    if( !isset($rc['customers']) ) {
        return array('stat'=>'ok', 'customers'=>array());
    }
    
    $customers = $rc['customers'];
    foreach($customers as $cid => $customer) {
        $prev_year = null;
        foreach($customer['years'] as $yid => $year) {
            if( $prev_year != null ) {
                $customers[$cid]['years'][$yid]['pi'] = round((($year['num_orders']/$prev_year['num_orders'])-1) * 100);
            } else {
                $customers[$cid]['years'][$yid]['pi'] = '';
            }
            $prev_year = $year;
        }
    }
    
    return array('stat'=>'ok', 'start_year'=>$start_year, 'end_year'=>$end_year, 'customers'=>$customers);
}
?>
