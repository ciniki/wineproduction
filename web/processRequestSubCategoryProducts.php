<?php
//
// Description
// -----------
// This function will generate the gallery page for the website
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_wineproduction_web_processRequestSubCategoryProducts(&$ciniki, $settings, $tnid, $category, $subcategory) {

    $strsql = "SELECT ciniki_wineproduction_products.id, "
        . "ciniki_wineproduction_products.name AS title, "
        . "ciniki_wineproduction_products.ptype, "
        . "ciniki_wineproduction_products.permalink, "
        . "ciniki_wineproduction_products.primary_image_id AS image_id, "
        . "ciniki_wineproduction_products.unit_amount, "
        . "ciniki_wineproduction_products.unit_discount_amount, "
        . "ciniki_wineproduction_products.unit_discount_percentage, "
        . "ciniki_wineproduction_products.taxtype_id, "
        . "ciniki_wineproduction_products.inventory_current_num, "
        . "ciniki_wineproduction_products.flags, "
        . "ciniki_wineproduction_products.synopsis AS description, "
        . "'yes' AS is_details "
        . "FROM ciniki_wineproduction_product_tags AS t1 "
        . "INNER JOIN ciniki_wineproduction_products ON ("
            . "t1.product_id = ciniki_wineproduction_products.id "
            . "AND ciniki_wineproduction_products.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND ciniki_wineproduction_products.start_date < UTC_TIMESTAMP() "
            . "AND (ciniki_wineproduction_products.end_date = '0000-00-00 00:00:00' "
                . "OR ciniki_wineproduction_products.end_date > UTC_TIMESTAMP()"
                . ") "
            . "AND ciniki_wineproduction_products.status < 60 "
            . "AND (ciniki_wineproduction_products.flags&0x01) > 0 "
            . ") "
        . "INNER JOIN ciniki_wineproduction_product_tags AS t2 ON ("
            . "ciniki_wineproduction_products.id = t2.product_id "
            . "AND t2.permalink = '" . ciniki_core_dbQuote($ciniki, $subcategory['permalink']) . "' "
            . "AND t2.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' ";
//    if( isset($category['tag_type']) && $category['tag_type'] > 0 ) {
//        $strsql .= "AND t2.tag_type = '" . ciniki_core_dbQuote($ciniki, $subcategory['tag_type']) . "' ";
//    } else {
        $strsql .= "AND t2.tag_type > 10 AND t2.tag_type < 30 ";
//    }
    $strsql .= ") "
        . "WHERE t1.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND t1.tag_type = 10 "
        . "AND t1.permalink = '" . ciniki_core_dbQuote($ciniki, $category['permalink']) . "' "
        . "ORDER BY ciniki_wineproduction_products.name COLLATE latin1_general_cs ASC "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.products', array(
        array('container'=>'products', 'fname'=>'id', 
            'fields'=>array('id', 'ptype', 'title', 'permalink', 'image_id', 'description', 'is_details',
                'unit_amount', 'unit_discount_amount', 'unit_discount_percentage', 'taxtype_id', 'inventory_current_num', 'flags',
            )),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['products']) ) {
        $products = $rc['products'];
    } else {
        $products = array();
    }

    return array('stat'=>'ok', 'products'=>$products);
}
?>
