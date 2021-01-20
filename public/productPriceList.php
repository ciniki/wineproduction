<?php
//
// Description
// -----------
// This method will return the list of Prices for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Price for.
//
// Returns
// -------
//
function ciniki_wineproduction_productPriceList($ciniki) {
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.productPriceList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of prices
    //
    $strsql = "SELECT ciniki_wineproduction_product_pricing.id, "
        . "ciniki_wineproduction_product_pricing.price_type, "
        . "ciniki_wineproduction_product_pricing.name, "
        . "ciniki_wineproduction_product_pricing.sequence, "
        . "ciniki_wineproduction_product_pricing.unit_amount "
        . "FROM ciniki_wineproduction_product_pricing "
        . "WHERE ciniki_wineproduction_product_pricing.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND price_type = 10 "
        . "ORDER BY sequence, name, unit_amount "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'prices', 'fname'=>'id', 
            'fields'=>array('id', 'price_type', 'name', 'sequence', 'unit_amount')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $kit_prices = isset($rc['prices']) ? $rc['prices'] : array();
    foreach($kit_prices as $pid => $price) {
        $kit_prices[$pid]['unit_amount_display'] = '$' . number_format($price['unit_amount'], 2);
    }

    //
    // Get the list of prices
    //
    $strsql = "SELECT ciniki_wineproduction_product_pricing.id, "
        . "ciniki_wineproduction_product_pricing.price_type, "
        . "ciniki_wineproduction_product_pricing.name, "
        . "ciniki_wineproduction_product_pricing.sequence, "
        . "ciniki_wineproduction_product_pricing.unit_amount "
        . "FROM ciniki_wineproduction_product_pricing "
        . "WHERE ciniki_wineproduction_product_pricing.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND price_type = 20 "
        . "ORDER BY sequence, name, unit_amount "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'prices', 'fname'=>'id', 
            'fields'=>array('id', 'price_type', 'name', 'sequence', 'unit_amount')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $processing_prices = isset($rc['prices']) ? $rc['prices'] : array();
    foreach($processing_prices as $pid => $price) {
        $processing_prices[$pid]['unit_amount_display'] = '$' . number_format($price['unit_amount'], 2);
    }

    return array('stat'=>'ok', 'kit_prices'=>$kit_prices, 'processing_prices'=>$processing_prices, 'nplist'=>array());
}
?>
