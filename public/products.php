<?php
//
// Description
// -----------
// This method will return the list of Products for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Product for.
//
// Returns
// -------
//
function ciniki_wineproduction_products($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'tag10'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Category'),
        'tag11'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Sub Category'),
        'tag12'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Variety'),
        'tag13'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Oak'),
        'tag14'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Body'),
        'tag15'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Sweetness'),
        'supplier_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Supplier'),
        'list'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'List'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.productList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
        
    //
    // Load maps
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'maps');
    $rc = ciniki_wineproduction_maps($ciniki);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $maps = $rc['maps'];

    //
    // Load the tax types
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'taxes', 'hooks', 'taxTypesRates');
    $rc = ciniki_taxes_hooks_taxTypesRates($ciniki, $args['tnid'], array());
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.141', 'msg'=>'Unable to get tax rates', 'err'=>$rc['err']));
    }
    $tax_types = isset($rc['types']) ? $rc['types'] : array();

    //
    // Get the tags
    //
    $strsql = "SELECT tags.tag_type, tags.tag_name, tags.permalink, COUNT(products.id) AS num_items "
        . "FROM ciniki_wineproduction_product_tags AS tags, ciniki_wineproduction_products AS products "
        . "WHERE tags.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
//        . "AND tags.tag_type = 12 "
        . "AND tags.product_id = products.id "
        . "AND products.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND products.status = 10 "
        . "GROUP BY tags.tag_type, tags.tag_name "
        . "ORDER BY tags.tag_type, tags.tag_name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'types', 'fname'=>'tag_type', 'fields'=>array('tag_type')),
        array('container'=>'tags', 'fname'=>'permalink', 'fields'=>array('tag_name', 'permalink', 'num_items')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.124', 'msg'=>'Unable to load tags', 'err'=>$rc['err']));
    }
    $types = isset($rc['types']) ? $rc['types'] : array();
    $tags10 = array();
    $tags11 = array();
    $tags12 = array();
    $tags13 = array();
    $tags14 = array();
    $tags15 = array();
    foreach($types as $type) {
        if( $type['tag_type'] == 10 && isset($type['tags']) ) {
            $tags10 = $type['tags'];
        } elseif( $type['tag_type'] == 11 && isset($type['tags']) ) {
            $tags11 = $type['tags'];
        } elseif( $type['tag_type'] == 12 && isset($type['tags']) ) {
            $tags12 = $type['tags'];
        } elseif( $type['tag_type'] == 13 && isset($type['tags']) ) {
            $tags13 = $type['tags'];
        } elseif( $type['tag_type'] == 14 && isset($type['tags']) ) {
            $tags14 = $type['tags'];
        } elseif( $type['tag_type'] == 15 && isset($type['tags']) ) {
            $tags15 = $type['tags'];
        }
    }
    $strsql = "SELECT COUNT(*) AS num "
        . "FROM ciniki_wineproduction_products "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND status = 60 "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbSingleCount');
    $rc = ciniki_core_dbSingleCount($ciniki, $strsql, 'ciniki.wineproduction', 'num');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.106', 'msg'=>'Unable to load get the number of items', 'err'=>$rc['err']));
    }
    if( isset($rc['num']) && $rc['num'] > 0 ) {
        array_push($tags10, array(
            'tag_name' => 'Discontinued',
            'permalink' => 'discontinued',
            'num_items' => $rc['num'],
            ));
    }

    //
    // Get the list of products
    //
    $strsql = "SELECT products.id, "
        . "products.name, "
        . "products.permalink, "
        . "products.ptype, "
        . "products.flags, "
        . "IF((products.flags&0x01)=0x01, 'Visible', '') AS visible, "
        . "products.status, "
        . "products.start_date, "
        . "products.end_date, "
        . "products.supplier_id, "
        . "products.supplier_item_number, "
        . "products.wine_type, "
        . "products.kit_length, "
        . "products.list_price, "
        . "products.list_discount_percent, "
        . "products.cost, "
        . "IFNULL(kit_pricing.unit_amount, 0) AS kit_unit_amount, "
        . "IFNULL(processing_pricing.unit_amount, 0) AS processing_unit_amount, "
        . "products.unit_amount, "
        . "products.unit_amount, "
        . "products.unit_discount_amount, "
        . "products.unit_discount_percentage, "
        . "products.taxtype_id, "
        . "products.inventory_current_num, "
        . "products.primary_image_id, "
        . "products.synopsis, "
        . "products.last_updated, "
        . "categories.tag_name AS categories, "
        . "subcategories.tag_name AS subcategories, "
        . "suppliers.name AS supplier_name "
        . "FROM ciniki_wineproduction_products AS products "
        . "";
    for($i = 10; $i <= 15; $i++ ) {
        if( isset($args['tag' . $i]) && $args['tag' . $i] != '' ) {
            $strsql .= "INNER JOIN ciniki_wineproduction_product_tags AS tags{$i} ON ("
                . "products.id = tags{$i}.product_id "
                . "AND tags{$i}.tag_type = {$i} "
                . "AND tags{$i}.permalink = '" . ciniki_core_dbQuote($ciniki, $args['tag' . $i]) . "' "
                . "AND tags{$i}.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                . ") ";
        }
    }
    $strsql .= "LEFT JOIN ciniki_wineproduction_product_tags AS categories ON ("
        . "products.id = categories.product_id "
        . "AND categories.tag_type = 10 "
        . "AND categories.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . ") ";
    $strsql .= "LEFT JOIN ciniki_wineproduction_product_tags AS subcategories ON ("
        . "products.id = subcategories.product_id "
        . "AND subcategories.tag_type = 11 "
        . "AND subcategories.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . ") ";
//    if( isset($args['list']) && $args['list'] == 'pricing' ) {
        $strsql .= "LEFT JOIN ciniki_wineproduction_product_pricing AS kit_pricing ON ("
            . "products.kit_price_id = kit_pricing.id "
            . "AND kit_pricing.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") ";
//    }
//    if( isset($args['list']) && $args['list'] == 'pricing' ) {
        $strsql .= "LEFT JOIN ciniki_wineproduction_product_pricing AS processing_pricing ON ("
            . "products.processing_price_id = processing_pricing.id "
            . "AND processing_pricing.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") ";
//    }
    $strsql .= "LEFT JOIN ciniki_wineproduction_suppliers AS suppliers ON ("
        . "products.supplier_id = suppliers.id "
        . "AND suppliers.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . ") ";
    $strsql .= "WHERE products.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' ";
    if( isset($args['tag10']) && $args['tag10'] == 'discontinued' ) {
        $strsql .= "AND products.status = 60 ";
    } elseif( isset($args['list']) && $args['list'] == 'discontinued' ) {
        $strsql .= "AND products.status = 60 ";
    } else {
        $strsql .= "AND products.status < 60 ";
    }
    if( isset($args['supplier_id']) && $args['supplier_id'] == '0' ) {
        $strsql .= "HAVING ISNULL(suppliers.name) ";
    } elseif( isset($args['supplier_id']) && $args['supplier_id'] != '' ) {
        $strsql .= "AND products.supplier_id = '" . ciniki_core_dbQuote($ciniki, $args['supplier_id']) . "' ";
    }
    $strsql .= "ORDER BY products.name, categories, subcategories "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'products', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'permalink', 'ptype', 'flags', 'status', 'start_date', 'end_date', 
                'visible',
                'supplier_id', 'supplier_item_number', 
                'wine_type', 'kit_length', 'list_price', 'list_discount_percent', 'cost', 
                'kit_unit_amount', 'processing_unit_amount',
                'unit_amount', 'unit_discount_amount', 'unit_discount_percentage', 'taxtype_id', 
                'inventory_current_num', 'primary_image_id', 'synopsis', 'categories', 'subcategories', 'supplier_name',
                'last_updated'),
            'maps'=>array(
                'status_text'=>$maps['product']['status'],
                ),
            'dlists'=>array(
                'categories'=>', ',
                'subcategories'=>', ',
                ),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['products']) ) {
        $products = $rc['products'];
        $product_ids = array();
        ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'hooks', 'loadThumbnail');
        foreach($products as $iid => $product) {
            if( isset($args['list']) && $args['list'] == 'website' && $product['primary_image_id'] > 0 ) {
                $rc = ciniki_images_hooks_loadThumbnail($ciniki, $args['tnid'], 
                    array('image_id'=>$product['primary_image_id'], 'maxlength'=>75, 'last_updated'=>$product['last_updated']));
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $products[$iid]['image'] = 'data:image/jpg;base64,' . base64_encode($rc['image']);
            }
            $products[$iid]['list_price_display'] = '$' . number_format($product['list_price'], 2);
            $products[$iid]['list_discount_percent_display'] = (float)$product['list_discount_percent'] . '%';
            $products[$iid]['cost_display'] = '$' . number_format($product['cost'], 2);

            if( $product['ptype'] == 10 ) {
                $products[$iid]['kit_price_display'] = '$' . number_format($product['kit_unit_amount'], 2);
                $products[$iid]['processing_price_display'] = '$' . number_format($product['processing_unit_amount'], 2);
            } else {
                $products[$iid]['kit_price_display'] = '';
                $products[$iid]['processing_price_display'] = '';
            }
            $products[$iid]['unit_amount_display'] = '$' . number_format($product['unit_amount'], 2);
            $products[$iid]['unit_discount_amount_display'] = '$' . number_format($product['unit_discount_amount'], 2);
            $products[$iid]['unit_discount_percentage_display'] = (float)$product['unit_discount_percentage'] . '%';
            $total = $product['unit_amount'] - $product['unit_discount_amount'];
            if( $product['unit_discount_percentage'] > 0 ) {
                $total = $total - ($total * ($product['unit_discount_percentage']/100));
            }
            $products[$iid]['tax_amount'] = 0;
            if( isset($tax_types[$product['taxtype_id']]['rates']) ) {
                foreach($tax_types[$product['taxtype_id']]['rates'] as $rate) {
                    if( $rate['item_percentage'] > 0 ) {
                        if( $product['ptype'] == 10 ) {
                            $products[$iid]['tax_amount'] += ($product['processing_unit_amount'] * ($rate['item_percentage']/100));
                        } else {
                            $products[$iid]['tax_amount'] += ($product['unit_amount'] * ($rate['item_percentage']/100));
                        }
                    }
                }
            }
            $total += $products[$iid]['tax_amount'];
            $products[$iid]['total_display'] = '$' . number_format($total, 2);
            $products[$iid]['tax_amount_display'] = '$' . number_format($products[$iid]['tax_amount'], 2);
            $product_ids[] = $product['id'];
        }
    } else {
        $products = array();
        $product_ids = array();
    }

    //
    // Get the list of open purchase orders if supplier is requested
    //
    $strsql = "SELECT orders.id, "
        . "suppliers.name, "
        . "orders.po_number, "
        . "orders.date_ordered "
        . "FROM ciniki_wineproduction_purchaseorders AS orders "
        . "LEFT JOIN ciniki_wineproduction_suppliers AS suppliers ON ("
            . "orders.supplier_id = suppliers.id "
            . "AND suppliers.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") "
        . "WHERE orders.status < 90 "
        . "AND orders.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'orders', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'po_number', 'date_ordered')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.167', 'msg'=>'Unable to load orders', 'err'=>$rc['err']));
    }
    $purchaseorders = isset($rc['orders']) ? $rc['orders'] : array();

    //
    // Get the list of suppliers
    //
    $strsql = "SELECT suppliers.id, suppliers.name, COUNT(products.id) AS num_items "
        . "FROM ciniki_wineproduction_suppliers AS suppliers "
        . "LEFT JOIN ciniki_wineproduction_products AS products ON ("
            . "suppliers.id = products.supplier_id "
            . "AND products.status < 60 "
            . "AND products.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") "
        . "WHERE suppliers.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "GROUP BY suppliers.id "
        . "ORDER BY suppliers.name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'suppliers', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'num_items')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.113', 'msg'=>'Unable to load suppliers', 'err'=>$rc['err']));
    }
    $suppliers = isset($rc['suppliers']) ? $rc['suppliers'] : array();

    //
    // Get number with missing suppliers
    //
    $strsql = "SELECT products.id, suppliers.id "
        . "FROM ciniki_wineproduction_products AS products "
        . "LEFT JOIN ciniki_wineproduction_suppliers AS suppliers ON ("
            . "products.supplier_id = suppliers.id "
            . "AND suppliers.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") "
        . "WHERE products.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND products.status < 60 "
        . "HAVING ISNULL(suppliers.id) "
        . "ORDER BY suppliers.name "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'item');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.114', 'msg'=>'Unable to load item', 'err'=>$rc['err']));
    }
    if( isset($rc['rows']) && count($rc['rows']) > 0 ) {
        array_push($suppliers, array('id'=>0, 'name'=>'No Supplier', 'num_items'=>count($rc['rows'])));
    }

    $rsp = array('stat'=>'ok', 'products'=>$products, 
        'tags10'=>$tags10, 
        'tags11'=>$tags11, 
        'tags12'=>$tags12, 
        'tags13'=>$tags13, 
        'tags14'=>$tags14, 
        'tags15'=>$tags15, 
        'tags15'=>$tags15, 
        'suppliers'=>$suppliers, 
        'purchaseorders'=>$purchaseorders, 
        'nplist'=>$product_ids,
        );

    return $rsp;
}
?>
