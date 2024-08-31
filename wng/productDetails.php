<?php
//
// Description
// -----------
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_wineproduction_wng_productDetails($ciniki, $settings, $tnid, $args) {
    //
    // Load currency and timezone settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $tnid);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');


    $modules = array();
    if( isset($ciniki['tenant']['modules']) ) {
        $modules = $ciniki['tenant']['modules'];
    }

    //
    // Get the product details
    //
    $strsql = "SELECT ciniki_wineproduction_products.id, "
        . "ciniki_wineproduction_products.name, "
        . "ciniki_wineproduction_products.permalink, "
        . "ciniki_wineproduction_products.synopsis, "
        . "ciniki_wineproduction_products.description, "
        . "ciniki_wineproduction_products.flags, "
        . "ciniki_wineproduction_products.unit_amount, "
        . "ciniki_wineproduction_products.unit_discount_amount, "
        . "ciniki_wineproduction_products.unit_discount_percentage, "
        . "ciniki_wineproduction_products.taxtype_id, "
        . "ciniki_wineproduction_products.inventory_current_num, "
        . "ciniki_wineproduction_products.primary_image_id "
        . "FROM ciniki_wineproduction_products "
        . "WHERE ciniki_wineproduction_products.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND ciniki_wineproduction_products.permalink = '" . ciniki_core_dbQuote($ciniki, $args['product_permalink']) . "' "
        . "AND ciniki_wineproduction_products.start_date < UTC_TIMESTAMP() "
        . "AND ciniki_wineproduction_products.status = 10 "
        . "AND (ciniki_wineproduction_products.end_date = '0000-00-00 00:00:00' "
            . "OR ciniki_wineproduction_products.end_date > UTC_TIMESTAMP()"
            . ") "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.artclub', array(
        array('container'=>'products', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'permalink', 'image-id'=>'primary_image_id', 
            'synopsis', 'description', 'flags',
            'unit_amount', 'unit_discount_amount', 'unit_discount_percentage', 'taxtype_id',
            'inventory_current_num')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['products']) || count($rc['products']) < 1 ) {
        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.wineproduction.115', 'msg'=>"I'm sorry, but we can't find the product you requested."));
    }
    $product = array_pop($rc['products']);

    //
    // Get the number of unit unshipped in purchase orders
    //
    $reserved_quantity = 0;
    if( isset($ciniki['tenant']['modules']['ciniki.sapos']) ) {
        $cur_invoice_id = 0;
        if( isset($ciniki['session']['cart']['sapos_id']) && $ciniki['session']['cart']['sapos_id'] > 0 ) {
            $cur_invoice_id = $ciniki['session']['cart']['sapos_id'];
        }
        ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'private', 'getReservedQuantities');
        $rc = ciniki_sapos_getReservedQuantities($ciniki, $tnid, 
            'ciniki.wineproduction.product', array($product['id']), $cur_invoice_id);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['quantities'][$product['id']]) ) {
            $reserved_quantity = $rc['quantities'][$product['id']]['quantity_reserved'];
        }
    }

    //
    // Get any images 
    //
    $strsql = "SELECT id, image_id, name, permalink, sequence, webflags, description, "
        . "UNIX_TIMESTAMP(last_updated) AS last_updated "
        . "FROM ciniki_wineproduction_product_images "
        . "WHERE product_id = '" . ciniki_core_dbQuote($ciniki, $product['id']) . "' "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND (webflags&0x01) = 1 "        // Visible images
        . "ORDER BY sequence, date_added, name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.products', array(
        array('container'=>'images', 'fname'=>'id', 'name'=>'image',
            'fields'=>array('id', 'image-id'=>'image_id', 'title'=>'name', 'permalink', 'sequence', 'webflags', 
                'description', 'last_updated')),
        ));
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    if( isset($rc['images']) ) {
        $product['images'] = $rc['images'];
    } else {
        $product['images'] = array();
    }

    //
    // Check if any files are attached to the product
    //
    $strsql = "SELECT id, uuid, name, extension, permalink, description "
        . "FROM ciniki_wineproduction_product_files "
        . "WHERE ciniki_wineproduction_product_files.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND ciniki_wineproduction_product_files.product_id = '" . ciniki_core_dbQuote($ciniki, $product['id']) . "' "
        . "AND (ciniki_wineproduction_product_files.webflags&0x01) = 1 "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.products', array(
        array('container'=>'files', 'fname'=>'id', 
            'fields'=>array('id', 'uuid', 'name', 'extension', 'permalink', 'description')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['files']) ) {
        $product['files'] = $rc['files'];
    }

    //
    // Check if any similar products
    //
/*    if( isset($modules['ciniki.products']['flags']) 
        && ($modules['ciniki.products']['flags']&0x01) > 0
        ) {
        $strsql = "SELECT ciniki_wineproduction_products.id, "
            . "ciniki_wineproduction_products.name, "
            . "ciniki_wineproduction_products.permalink, "
            . "ciniki_wineproduction_products.description, "
            . "ciniki_wineproduction_products.primary_image_id, "
            . "ciniki_wineproduction_products.synopsis, "
            . "'yes' AS is_details, "
            . "UNIX_TIMESTAMP(ciniki_wineproduction_products.last_updated) AS last_updated "
            . "FROM ciniki_product_relationships "
            . "LEFT JOIN ciniki_wineproduction_products ON ((ciniki_product_relationships.product_id = ciniki_wineproduction_products.id "
                    . "OR ciniki_product_relationships.related_id = ciniki_wineproduction_products.id) "
                . "AND ciniki_wineproduction_products.id <> '" . ciniki_core_dbQuote($ciniki, $product['id']) . "' "
                . "AND ciniki_wineproduction_products.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                . ") "
            // Check for a relationship where the requested product is the primary, 
            // OR where the product is the secondary and it's a cross linked relationship_type
            . "WHERE ((ciniki_product_relationships.product_id = '" . ciniki_core_dbQuote($ciniki, $product['id']) . "' "
                    . "OR (ciniki_product_relationships.related_id = '" . ciniki_core_dbQuote($ciniki, $product['id']) . "' "
                        . "AND ciniki_product_relationships.relationship_type = 10) "
                    . ") "
                . ") "
            . "AND ciniki_product_relationships.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ""; 
        $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.products', array(
            array('container'=>'products', 'fname'=>'id',
                'fields'=>array('id', 'image_id'=>'primary_image_id', 'title'=>'name', 'permalink', 
                    'description'=>'synopsis', 'is_details', 'last_updated')),
            ));
        if( $rc['stat'] == 'ok' && isset($rc['products']) ) {
            $product['similar'] = $rc['products'];
        }
    }
*/
    //
    // Check for any recipes
    //
/*    if( isset($modules['ciniki.products']['flags']) 
        && ($modules['ciniki.products']['flags']&0x02) > 0 
        && isset($modules['ciniki.recipes']) ) {
        $strsql = "SELECT ciniki_recipes.id, "
            . "ciniki_recipes.name, "
            . "ciniki_recipes.permalink, "
            . "ciniki_recipes.primary_image_id AS image_id, "
            . "ciniki_recipes.description, "
            . "'yes' AS is_details, "
            . "UNIX_TIMESTAMP(ciniki_recipes.last_updated) AS last_updated "
            . "FROM ciniki_product_refs "
            . "LEFT JOIN ciniki_recipes ON (ciniki_product_refs.object_id = ciniki_recipes.id "
                . "AND ciniki_recipes.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                . ") "
            . "WHERE ciniki_product_refs.product_id = '" . ciniki_core_dbQuote($ciniki, $product['id']) . "' "
            . "AND ciniki_product_refs.object = 'ciniki.recipes.recipe' "
            . "AND ciniki_product_refs.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ""; 
        $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.products', array(
            array('container'=>'recipes', 'fname'=>'id',
                'fields'=>array('id', 'image_id', 'title'=>'name', 'permalink',
                    'description', 'is_details', 'last_updated')),
            ));
        if( $rc['stat'] != 'ok' ) { 
            return $rc;
        }
        if( isset($rc['recipes']) ) {
            $product['recipes'] = $rc['recipes'];
        }
    }
*/
    //
    // Get all the categories, sub-categories and tags associated with this product 
    // for use in the share-buttons
    //
    $strsql = "SELECT tag_name "
        . "FROM ciniki_wineproduction_product_tags "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND product_id = '" . ciniki_core_dbQuote($ciniki, $product['id']) . "' "
        . "ORDER BY tag_type "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList');
    $rc = ciniki_core_dbQueryList($ciniki, $strsql, 'ciniki.products', 'tags', 'tag_name');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['tags']) ) {
        $product['social-tags'] = array();
        foreach($rc['tags'] as $tid => $tag) {
            $product['social-tags'][] = preg_replace("/[^a-zA-Z0-9]/", '', $tag);
        }
    } else {
        $product['social-tags'] = array();
    }

    //
    // If specified, get the category title
    //
    if( isset($args['category_permalink']) && $args['category_permalink'] != '' ) {
        $strsql = "SELECT t1.tag_name, c1.name "
            . "FROM ciniki_wineproduction_product_tags AS t1 "
            . "LEFT JOIN ciniki_wineproduction_product_tagdetails AS c1 ON ("
                . "t1.permalink = c1.permalink "
                . "AND t1.tag_type = c1.tag_type "
                . "AND c1.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                . ") "
            . "WHERE t1.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND t1.permalink = '" . ciniki_core_dbQuote($ciniki, $args['category_permalink']) . "' "
            . "AND t1.product_id = '" . ciniki_core_dbQuote($ciniki, $product['id']) . "' "
            . "AND t1.tag_type = 10 "
            . "LIMIT 1 "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.products', 'tag');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['tag']) ) {
            if( $rc['tag']['name'] != '' ) {
                $product['category_title'] = $rc['tag']['name'];
            } else {
                $product['category_title'] = $rc['tag']['tag_name'];
            }
        }
    }

    if( isset($args['subcategory_permalink']) && $args['subcategory_permalink'] != '' ) {
        $strsql = "SELECT tag_name "
            . "FROM ciniki_wineproduction_product_tags "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['subcategory_permalink']) . "' "
            . "AND tag_type > 10 "
            . "AND tag_type < 30 "
            . "LIMIT 1 "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.products', 'tag');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['tag']) ) {
            $product['subcategory_title'] = $rc['tag']['tag_name'];
        }
    }


    return array('stat'=>'ok', 'product'=>$product);
}
?>
