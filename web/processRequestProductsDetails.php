<?php
//
// Description
// -----------
// This function gets extra details and information for products. It's best not to join all the tables
// at the same time if the information is not required on the webpage, so this allows extra information
// to be obtained when required.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_wineproduction_web_processRequestProductsDetails(&$ciniki, $settings, $tnid, $products, $args) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuoteIDs');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');

    $product_ids = array_keys($products);
    if( count($product_ids) == 0 ) {
        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.wineproduction.116', 'msg'=>'No products found'));
    }

    //
    // If images, get the last_updated for use in caching images
    //
    if( isset($args['image']) && $args['image'] == 'yes' ) {
        $strsql = "SELECT ciniki_wineproduction_products.id, "
            . "IF(ciniki_images.last_updated > ciniki_wineproduction_products.last_updated, "
                . "UNIX_TIMESTAMP(ciniki_images.last_updated), "
                . "UNIX_TIMESTAMP(ciniki_wineproduction_products.last_updated)) AS last_updated "
            . "FROM ciniki_wineproduction_products "
            . "LEFT JOIN ciniki_images ON ("
                . "ciniki_wineproduction_products.primary_image_id = ciniki_images.id "
                . "AND ciniki_images.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                . ") "
            . "WHERE ciniki_wineproduction_products.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND ciniki_wineproduction_products.status < 60 "
            . "AND ciniki_wineproduction_products.id IN (" . ciniki_core_dbQuoteIDs($ciniki, $product_ids) . ") "
            . "";
        $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.products', array(
            array('container'=>'products', 'fname'=>'id', 'fields'=>array('last_updated')),
            ));
        if( $rc['stat'] == 'ok' ) {  
            foreach($products as $pid => $product) {
                $products[$pid]['last_updated'] = $rc['products'][$pid]['last_updated'];
            }
        }
    }

    return array('stat'=>'ok', 'products'=>$products);
}
?>
