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

    $strsql = "SELECT products.id, "
        . "products.name AS title, "
        . "products.ptype, "
        . "products.permalink, "
        . "products.primary_image_id AS image_id, "
        . "products.unit_amount, "
        . "products.unit_discount_amount, "
        . "products.unit_discount_percentage, "
        . "products.taxtype_id, "
        . "products.inventory_current_num, "
        . "products.flags, "
        . "products.synopsis AS description, "
        . "'yes' AS is_details, "
        . "IFNULL(tags.tag_name, '') AS subtitle "
        . "FROM ciniki_wineproduction_product_tags AS t1 "
        . "INNER JOIN ciniki_wineproduction_products AS products ON ("
            . "t1.product_id = products.id "
            . "AND products.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND products.start_date < UTC_TIMESTAMP() "
            . "AND (products.end_date = '0000-00-00 00:00:00' "
                . "OR products.end_date > UTC_TIMESTAMP()"
                . ") "
            . "AND products.status < 60 "
            . "AND (products.flags&0x01) > 0 "
            . ") "
        . "INNER JOIN ciniki_wineproduction_product_tags AS t2 ON ("
            . "products.id = t2.product_id "
            . "AND t2.permalink = '" . ciniki_core_dbQuote($ciniki, $subcategory['permalink']) . "' "
            . "AND t2.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' ";
//    if( isset($category['tag_type']) && $category['tag_type'] > 0 ) {
//        $strsql .= "AND t2.tag_type = '" . ciniki_core_dbQuote($ciniki, $subcategory['tag_type']) . "' ";
//    } else {
        $strsql .= "AND t2.tag_type > 10 AND t2.tag_type < 30 ";
//    }
    $strsql .= ") "
        . "LEFT JOIN ciniki_wineproduction_product_tags AS tags on ("
            . "products.id = tags.product_id "
            . "AND tags.tag_type IN (13,14,15) "
            . "AND tags.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "WHERE t1.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND t1.tag_type = 10 "
        . "AND t1.permalink = '" . ciniki_core_dbQuote($ciniki, $category['permalink']) . "' "
        . "ORDER BY products.name COLLATE latin1_general_cs ASC, tags.tag_type, tags.tag_name "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.products', array(
        array('container'=>'products', 'fname'=>'id', 
            'fields'=>array('id', 'ptype', 'title', 'permalink', 'image_id', 'description', 'is_details',
                'unit_amount', 'unit_discount_amount', 'unit_discount_percentage', 'taxtype_id', 'inventory_current_num', 'flags',
                'subtitle'),
            'dlists'=>array('subtitle'=>', '),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['products']) ) {
        $products = $rc['products'];
    } else {
        $products = array();
    }
        error_log(print_r($products,true));
    

    return array('stat'=>'ok', 'products'=>$products);
}
?>
