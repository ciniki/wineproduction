<?php
//
// Description
// ===========
// This method will return all the information about an product.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant the product is attached to.
// product_id:          The ID of the product to get the details for.
//
// Returns
// -------
//
function ciniki_wineproduction_productGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'product_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Product'),
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.productGet');
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
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'php');

    //
    // Return default for new Product
    //
    if( $args['product_id'] == 0 ) {
        $product = array('id'=>0,
            'name'=>'',
            'permalink'=>'',
            'ptype'=>'10',
            'flags'=>'0',
            'status'=>'10',
            'start_date'=>'',
            'end_date'=>'',
            'supplier_id'=>'',
            'supplier_item_number'=>'',
            'wine_type'=>'',
            'kit_length'=>'',
            'cost'=>'',
            'unit_amount'=>'',
            'unit_discount_amount'=>'',
            'unit_discount_percentage'=>'',
            'taxtype_id'=>'',
            'inventory_current_num'=>'',
            'primary_image_id'=>'0',
            'synopsis'=>'',
            'description'=>'',
            'tags10'=>'',
            'tags11'=>'',
            'tags12'=>'',
            'tags13'=>'',
            'tags14'=>'',
            'tags15'=>'',
        );
    }

    //
    // Get the details for an existing Product
    //
    else {
        $strsql = "SELECT ciniki_wineproduction_products.id, "
            . "ciniki_wineproduction_products.name, "
            . "ciniki_wineproduction_products.permalink, "
            . "ciniki_wineproduction_products.ptype, "
            . "ciniki_wineproduction_products.flags, "
            . "ciniki_wineproduction_products.status, "
            . "ciniki_wineproduction_products.start_date, "
            . "ciniki_wineproduction_products.end_date, "
            . "ciniki_wineproduction_products.supplier_id, "
            . "ciniki_wineproduction_products.supplier_item_number, "
            . "ciniki_wineproduction_products.wine_type, "
            . "ciniki_wineproduction_products.kit_length, "
            . "ciniki_wineproduction_products.list_price, "
            . "ciniki_wineproduction_products.list_discount_percent, "
            . "ciniki_wineproduction_products.cost, "
            . "ciniki_wineproduction_products.kit_price_id, "
            . "ciniki_wineproduction_products.processing_price_id, "
            . "ciniki_wineproduction_products.unit_amount, "
            . "ciniki_wineproduction_products.unit_discount_amount, "
            . "ciniki_wineproduction_products.unit_discount_percentage, "
            . "ciniki_wineproduction_products.taxtype_id, "
            . "ciniki_wineproduction_products.inventory_current_num, "
            . "ciniki_wineproduction_products.primary_image_id, "
            . "ciniki_wineproduction_products.synopsis, "
            . "ciniki_wineproduction_products.description "
            . "FROM ciniki_wineproduction_products "
            . "WHERE ciniki_wineproduction_products.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_wineproduction_products.id = '" . ciniki_core_dbQuote($ciniki, $args['product_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
            array('container'=>'products', 'fname'=>'id', 
                'fields'=>array('name', 'permalink', 'ptype', 'flags', 'status', 'start_date', 'end_date', 
                    'supplier_id', 'supplier_item_number', 'wine_type', 'kit_length', 
                    'list_price', 'list_discount_percent', 'cost', 
                    'kit_price_id', 'processing_price_id', 
                    'unit_amount', 'unit_discount_amount', 'unit_discount_percentage', 'taxtype_id', 
                    'inventory_current_num', 'primary_image_id', 'synopsis', 'description'),
                'utctotz'=>array(
                    'start_date'=>array('format'=>$date_format, 'timezone'=>'UTC'),
                    'end_date'=>array('format'=>$date_format, 'timezone'=>'UTC'),
                    ),  
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.122', 'msg'=>'Product not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['products'][0]) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.123', 'msg'=>'Unable to find Product'));
        }
        $product = $rc['products'][0];

        $product['list_price'] = '$' . number_format($product['list_price'], 2);
        $product['list_discount_percent'] = (float)$product['list_discount_percent'] . '%';
        $product['cost'] = '$' . number_format($product['cost'], 2);
        $product['unit_amount'] = '$' . number_format($product['unit_amount'], 2);
        $product['unit_discount_amount'] = '$' . number_format($product['unit_discount_amount'], 2);
        $product['unit_discount_percentage'] = (float)$product['unit_discount_percentage'] . '%';

        //
        // Get the categories
        //
        $strsql = "SELECT tag_type, tag_name AS lists "
            . "FROM ciniki_wineproduction_product_tags "
            . "WHERE product_id = '" . ciniki_core_dbQuote($ciniki, $args['product_id']) . "' "
            . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "ORDER BY tag_type, tag_name "
            . "";
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
            array('container'=>'tags', 'fname'=>'tag_type', 
                'fields'=>array('tag_type', 'lists'), 'dlists'=>array('lists'=>'::')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $product['tags10'] = '';
        $product['tags11'] = '';
        $product['tags12'] = '';
        $product['tags13'] = '';
        $product['tags14'] = '';
        $product['tags15'] = '';
        if( isset($rc['tags']) ) {
            foreach($rc['tags'] as $tags) {
                if( $tags['tag_type'] == 10 ) {
                    $product['tags10'] = $tags['lists'];
                } elseif( $tags['tag_type'] == 11 ) {
                    $product['tags11'] = $tags['lists'];
                } elseif( $tags['tag_type'] == 12 ) {
                    $product['tags12'] = $tags['lists'];
                } elseif( $tags['tag_type'] == 13 ) {
                    $product['tags13'] = $tags['lists'];
                } elseif( $tags['tag_type'] == 14 ) {
                    $product['tags14'] = $tags['lists'];
                } elseif( $tags['tag_type'] == 15 ) {
                    $product['tags15'] = $tags['lists'];
                }
            }
        }

        //
        // Load the images for the product
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadCacheThumbnail');
        $strsql = "SELECT id, "
            . "image_id, "
            . "name, "
            . "sequence, "
            . "webflags, "
            . "description "
            . "FROM ciniki_wineproduction_product_images "
            . "WHERE product_id = '" . ciniki_core_dbQuote($ciniki, $args['product_id']) . "' "
            . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "ORDER BY sequence, date_added, name "
            . "";
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.products', array(
            array('container'=>'images', 'fname'=>'id', 'name'=>'image',
                'fields'=>array('id', 'image_id', 'name', 'sequence', 'webflags', 'description')),
            ));
        if( $rc['stat'] != 'ok' ) { 
            return $rc;
        }
        if( isset($rc['images']) ) {
            $product['images'] = $rc['images'];
            foreach($product['images'] as $img_id => $img) {
                if( isset($img['image_id']) && $img['image_id'] > 0 ) {
                    $rc = ciniki_images_loadCacheThumbnail($ciniki, $args['tnid'], $img['image_id'], 75);
                    if( $rc['stat'] != 'ok' ) {
                        return $rc;
                    }
                    $product['images'][$img_id]['image_data'] = 'data:image/jpg;base64,' . base64_encode($rc['image']);
                }
            }
        } else {
            $product['images'] = array();
        }

        //
        // Load the files for a product
        //
        $strsql = "SELECT id, name, extension, permalink "
            . "FROM ciniki_wineproduction_product_files "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND product_id = '" . ciniki_core_dbQuote($ciniki, $args['product_id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
            array('container'=>'files', 'fname'=>'id', 'fields'=>array('id', 'name', 'extension', 'permalink')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $product['files'] = isset($rc['files']) ? $rc['files'] : array();
    }

    $rsp = array('stat'=>'ok', 'product'=>$product);

    //
    // Get the tags
    //
    $strsql = "SELECT DISTINCT tag_type, tag_name, permalink "
        . "FROM ciniki_wineproduction_product_tags "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "ORDER BY tag_type, tag_name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.tutorials', array(
        array('container'=>'types', 'fname'=>'tag_type', 
            'fields'=>array('tag_type', 'tag_name'), 'lists'=>array('tag_name')),
//        array('container'=>'categories', 'fname'=>'tag_name', 'name'=>'tag',
//            'fields'=>array('name'=>'tag_name')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $rsp['tags10'] = array();
    $rsp['tags11'] = array();
    $rsp['tags12'] = array();
    $rsp['tags13'] = array();
    $rsp['tags14'] = array();
    $rsp['tags15'] = array();
    if( isset($rc['types']) ) {
        foreach($rc['types'] as $type) {
            if( $type['tag_type'] == 10 ) {
                $rsp['tags10'] = explode(',', $type['tag_name']);
            } elseif( $type['tag_type'] == 11 ) {
                $rsp['tags11'] = explode(',', $type['tag_name']);
            } elseif( $type['tag_type'] == 12 ) {
                $rsp['tags12'] = explode(',', $type['tag_name']);
            } elseif( $type['tag_type'] == 13 ) {
                $rsp['tags13'] = explode(',', $type['tag_name']);
            } elseif( $type['tag_type'] == 14 ) {
                $rsp['tags14'] = explode(',', $type['tag_name']);
            } elseif( $type['tag_type'] == 15 ) {
                $rsp['tags15'] = explode(',', $type['tag_name']);
            }
        }
    }

    //
    // Get the list of suppliers
    //
    $strsql = "SELECT id, name "
        . "FROM ciniki_wineproduction_suppliers "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'suppliers', 'fname'=>'id', 
            'fields'=>array('value'=>'id', 'label'=>'name')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.107', 'msg'=>'Unable to load ', 'err'=>$rc['err']));
    }
    $rsp['suppliers'] = isset($rc['suppliers']) ? $rc['suppliers'] : array();
    array_unshift($rsp['suppliers'], array(
        'value' => 0,
        'label' => 'No Supplier',
        ));

    //
    // Get the list of kit prices
    //
    $strsql = "SELECT id, name, unit_amount "
        . "FROM ciniki_wineproduction_product_pricing "
        . "WHERE price_type = 10 "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "ORDER BY sequence, name, unit_amount "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'prices', 'fname'=>'id', 'fields'=>array('id', 'name', 'unit_amount')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.140', 'msg'=>'Unable to load prices', 'err'=>$rc['err']));
    }
    $rsp['kit_prices'] = isset($rc['prices']) ? $rc['prices'] : array();
    foreach($rsp['kit_prices'] as $pid => $price) {
        $rsp['kit_prices'][$pid]['label'] = $price['name'] . ' ($' . number_format($price['unit_amount'], 2) . ')';
    }
    array_unshift($rsp['kit_prices'], array(
        'id' => 0,
        'label' => 'No Pricing',
        ));

    //
    // Get the list of processing prices
    //
    $strsql = "SELECT id, name, unit_amount "
        . "FROM ciniki_wineproduction_product_pricing "
        . "WHERE price_type = 20 "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "ORDER BY sequence, name, unit_amount "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'prices', 'fname'=>'id', 'fields'=>array('id', 'name', 'unit_amount')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.140', 'msg'=>'Unable to load prices', 'err'=>$rc['err']));
    }
    $rsp['processing_prices'] = isset($rc['prices']) ? $rc['prices'] : array();
    foreach($rsp['processing_prices'] as $pid => $price) {
        $rsp['processing_prices'][$pid]['label'] = $price['name'] . ' ($' . number_format($price['unit_amount'], 2) . ')';
    }
    array_unshift($rsp['processing_prices'], array(
        'id' => 0,
        'label' => 'No Pricing',
        ));

    //
    // Get the taxtypes
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'taxes', 'hooks', 'taxTypes');
    $rc = ciniki_taxes_hooks_taxTypes($ciniki, $args['tnid'], array('notax'=>'yes'));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $rsp['taxtypes'] = $rc['types'];

    return $rsp;
}
?>
