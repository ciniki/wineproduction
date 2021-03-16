<?php
//
// Description
// -----------
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wineproduction_productPricingUpdate(&$ciniki, $tnid, $args) {

    //
    // Get the list of products to update
    //
    $strsql = "SELECT products.id, "
        . "products.ptype, "
        . "products.list_price, "
        . "products.list_discount_percent, "
        . "products.cost, "
        . "products.kit_price_id, "
        . "IFNULL(kits.unit_amount, 0) AS kit_unit_amount, "
        . "products.processing_price_id, "
        . "IFNULL(processing.unit_amount, 0) AS processing_unit_amount, "
        . "products.unit_amount, "
        . "products.unit_discount_amount, "
        . "products.unit_discount_percentage "
        . "FROM ciniki_wineproduction_products AS products "
        . "LEFT JOIN ciniki_wineproduction_product_pricing AS kits ON ("
            . "products.kit_price_id = kits.id "
            . "AND kits.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "LEFT JOIN ciniki_wineproduction_product_pricing AS processing ON ("
            . "products.processing_price_id = processing.id "
            . "AND processing.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "WHERE products.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    if( isset($args['product_id']) && $args['product_id'] > 0 ) {
        $strsql .= "AND products.id = '" . ciniki_core_dbQuote($ciniki, $args['product_id']) . "' ";
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'products', 'fname'=>'id', 
            'fields'=>array('id', 'ptype', 'list_price', 'list_discount_percent', 'cost', 'kit_price_id', 'kit_unit_amount', 
                'processing_price_id', 'processing_unit_amount', 
                'unit_amount', 'unit_discount_amount', 'unit_discount_percentage',
                ),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.137', 'msg'=>'Unable to load products', 'err'=>$rc['err']));
    }
    $products = isset($rc['products']) ? $rc['products'] : array();
    foreach($products as $pid => $product) {
        $cost = $product['list_price'] - ($product['list_price'] * ($product['list_discount_percent']/100));
        $update_args = array();
        if( $cost != $product['cost'] ) {
            $update_args['cost'] = $cost;
        }
        if( $product['ptype'] == 10 ) {
            $unit_amount = $product['kit_unit_amount'] + $product['processing_unit_amount'];
            if( $product['unit_amount'] != (string)$unit_amount ) {
                $update_args['unit_amount'] = $unit_amount;
            }
        }

        if( count($update_args) > 0 ) { 
            ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
            $rc = ciniki_core_objectUpdate($ciniki, $tnid, 'ciniki.wineproduction.product', $product['id'], $update_args, 0x04);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.138', 'msg'=>'Unable to update the product', 'err'=>$rc['err']));
            }
        }
    }

    return array('stat'=>'ok');
}
?>
