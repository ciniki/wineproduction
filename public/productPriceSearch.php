<?php
//
// Description
// -----------
// This method searchs for a Prices for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Price for.
// start_needle:       The search string to search for.
// limit:              The maximum number of entries to return.
//
// Returns
// -------
//
function ciniki_wineproduction_productPriceSearch($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'start_needle'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Search String'),
        'limit'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Limit'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.productPriceSearch');
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
        . "AND ("
            . "name LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR name LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
        . ") "
        . "";
    if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
        $strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";
    } else {
        $strsql .= "LIMIT 25 ";
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'prices', 'fname'=>'id', 
            'fields'=>array('id', 'price_type', 'name', 'sequence', 'unit_amount')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['prices']) ) {
        $prices = $rc['prices'];
        $price_ids = array();
        foreach($prices as $iid => $price) {
            $price_ids[] = $price['id'];
        }
    } else {
        $prices = array();
        $price_ids = array();
    }

    return array('stat'=>'ok', 'prices'=>$prices, 'nplist'=>$price_ids);
}
?>
