<?php
//
// Description
// ===========
// This method will return all the information about an price.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant the price is attached to.
// price_id:          The ID of the price to get the details for.
//
// Returns
// -------
//
function ciniki_wineproduction_productPriceGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'price_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Price'),
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.productPriceGet');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load tenant settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'php');

    //
    // Return default for new Price
    //
    if( $args['price_id'] == 0 ) {
        $price = array('id'=>0,
            'price_type'=>'',
            'name'=>'',
            'invoice_description'=>'',
            'sequence'=>'',
            'unit_amount'=>'',
        );
    }

    //
    // Get the details for an existing Price
    //
    else {
        $strsql = "SELECT ciniki_wineproduction_product_pricing.id, "
            . "ciniki_wineproduction_product_pricing.price_type, "
            . "ciniki_wineproduction_product_pricing.name, "
            . "ciniki_wineproduction_product_pricing.invoice_description, "
            . "ciniki_wineproduction_product_pricing.sequence, "
            . "ciniki_wineproduction_product_pricing.unit_amount "
            . "FROM ciniki_wineproduction_product_pricing "
            . "WHERE ciniki_wineproduction_product_pricing.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_wineproduction_product_pricing.id = '" . ciniki_core_dbQuote($ciniki, $args['price_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
            array('container'=>'prices', 'fname'=>'id', 
                'fields'=>array('price_type', 'name', 'invoice_description', 'sequence', 'unit_amount'),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.162', 'msg'=>'Price not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['prices'][0]) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.163', 'msg'=>'Unable to find Price'));
        }
        $price = $rc['prices'][0];
        $price['unit_amount'] = '$' . number_format($price['unit_amount'], 2);
    }

    return array('stat'=>'ok', 'price'=>$price);
}
?>
