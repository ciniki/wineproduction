<?php
//
// Description
// -----------
// 
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wineproduction_supplierUpdates(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'supplier_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Supplier'),
        'field'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Field'),
        'update_product_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Update Product'),
        'import_sku'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Import Sku'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.supplierUpdates');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
       
    //
    // Get the list of suppliers that have supplier_tnid
    //
    $strsql = "SELECT id, name, supplier_tnid "
        . "FROM ciniki_wineproduction_suppliers "
        . "WHERE supplier_tnid > 0 "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "ORDER BY name ";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'suppliers', 'fname'=>'id', 'fields'=>array('id', 'name', 'supplier_tnid')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.209', 'msg'=>'Unable to load ', 'err'=>$rc['err']));
    }
    $suppliers = isset($rc['suppliers']) ? $rc['suppliers'] : array();
    if( count($suppliers) == 0 ) {
        $suppliers[] = array('id'=>0, 'name'=>'No Suppliers Configured');
        return array('stat'=>'ok', 'suppliers'=>$suppliers, 'fields'=>array(), 'products'=>array());
    }

    //
    // If no supplier, then return with empty fields
    //
    if( !isset($args['supplier_id']) || $args['supplier_id'] == '' || $args['supplier_id'] <= 0 ) {
        $suppliers[] = array('id'=>0, 'name'=>'Choose a Supplier');
        return array('stat'=>'ok', 'suppliers'=>$suppliers, 'fields'=>array(), 'products'=>array());
    }

    if( !isset($suppliers[$args['supplier_id']]['supplier_tnid']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.210', 'msg'=>'No supplier found'));
    }
    if( $suppliers[$args['supplier_id']]['supplier_tnid'] <= 0 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.211', 'msg'=>'No supplier found'));
    }

    $supplier_tnid = $suppliers[$args['supplier_id']]['supplier_tnid'];

    //
    // Check supplier ID is sharring
    //
    $strsql = "SELECT flags "
        . "FROM ciniki_tenant_modules "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $supplier_tnid) . "' "
        . "AND package = 'ciniki' "
        . "AND module = 'wineproduction' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'module');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.213', 'msg'=>'Invalid supplier', 'err'=>$rc['err']));
    }
    if( !isset($rc['module']['flags']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.214', 'msg'=>'Invalid supplier'));
    }
    if( ($rc['module']['flags']&0x02) != 0x02 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.215', 'msg'=>'Supplier disabled'));
    }

    //
    // Load all the products and tags for the tenant
    //
    $strsql = "SELECT products.id, "
        . "products.name, "
        . "products.status, "
        . "products.wine_type, "
        . "products.kit_length, "
        . "products.supplier_item_number, "
        . "products.list_price, "
        . "products.primary_image_id, "
        . "IFNULL(images.checksum, '') AS primary_image_checksum, "
        . "products.synopsis, "
        . "products.description, "
        . "products.last_updated, "
        . "tags.id AS tag_id, "
        . "tags.tag_type, "
        . "tags.tag_name, "
        . "tags.permalink AS tag_permalink "
        . "FROM ciniki_wineproduction_products AS products "
        . "LEFT JOIN ciniki_images AS images ON ("
            . "products.primary_image_id = images.id "
            . "AND images.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") "
        . "LEFT JOIN ciniki_wineproduction_product_tags AS tags ON ("
            . "products.id = tags.product_id "
            . "AND tags.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . ") "
        . "WHERE products.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND supplier_id = '" . ciniki_core_dbQuote($ciniki, $args['supplier_id']) . "' "
        . "AND products.status = 10 "   // Active products
        . "ORDER BY products.name, tags.tag_type, tags.tag_name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'products', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'status', 'wine_type', 'kit_length', 
                'supplier_item_number', 'list_price', 'primary_image_id', 'primary_image_checksum', 
                'synopsis', 'description',
                )),
        array('container'=>'tags', 'fname'=>'tag_id', 
            'fields'=>array('tag_type', 'tag_name', 'tag_permalink'),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.212', 'msg'=>'Unable to load products', 'err'=>$rc['err']));
    }
    $tenant_products = isset($rc['products']) ? $rc['products'] : array();

    //
    // Load all the products and tags from the supplier
    //
    $strsql = "SELECT products.id, "
        . "products.name, "
        . "products.status, "
        . "products.wine_type, "
        . "products.kit_length, "
        . "products.supplier_item_number, "
        . "products.list_price, "
        . "products.primary_image_id, "
        . "IFNULL(images.checksum, '') AS primary_image_checksum, "
        . "products.synopsis, "
        . "products.description, "
        . "products.last_updated, "
        . "tags.id AS tag_id, "
        . "tags.tag_type, "
        . "tags.tag_name, "
        . "tags.permalink AS tag_permalink "
        . "FROM ciniki_wineproduction_products AS products "
        . "LEFT JOIN ciniki_images AS images ON ("
            . "products.primary_image_id = images.id "
            . "AND images.tnid = '" . ciniki_core_dbQuote($ciniki, $supplier_tnid) . "' "
            . ") "
        . "LEFT JOIN ciniki_wineproduction_product_tags AS tags ON ("
            . "products.id = tags.product_id "
            . "AND tags.tnid = '" . ciniki_core_dbQuote($ciniki, $supplier_tnid) . "' "
        . ") "
        . "WHERE products.tnid = '" . ciniki_core_dbQuote($ciniki, $supplier_tnid) . "' "
        . "AND products.status = 10 "   // Active products
        . "ORDER BY products.name, tags.tag_type, tags.tag_name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'products', 'fname'=>'supplier_item_number', 
            'fields'=>array('id', 'name', 'status', 'wine_type', 'kit_length', 
                'supplier_item_number', 'list_price', 'primary_image_id', 'primary_image_checksum', 
                'synopsis', 'description',
                )),
        array('container'=>'tags', 'fname'=>'tag_id', 
            'fields'=>array('tag_type', 'tag_name', 'permalink'=>'tag_permalink'),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.212', 'msg'=>'Unable to load products', 'err'=>$rc['err']));
    }
    $supplier_products = isset($rc['products']) ? $rc['products'] : array();

    //
    // Compare fields
    //
    $fields = array(
        array('field'=>'wine_type', 'name'=>'Wine Type', 'num_diffs'=>0),
        array('field'=>'kit_length', 'name'=>'Kit Length', 'num_diffs'=>0),
        array('field'=>'list_price', 'name'=>'List Price', 'num_diffs'=>0),
        array('field'=>'primary_image_checksum', 'name'=>'Primary Image', 'num_diffs'=>0),
        array('field'=>'tags10', 'name'=>'Categories', 'num_diffs'=>0),
        array('field'=>'tags11', 'name'=>'Sub Categories', 'num_diffs'=>0),
        array('field'=>'tags12', 'name'=>'Varietals', 'num_diffs'=>0),
        array('field'=>'tags13', 'name'=>'Oak', 'num_diffs'=>0),
        array('field'=>'tags14', 'name'=>'Body', 'num_diffs'=>0),
        array('field'=>'tags15', 'name'=>'Dryness', 'num_diffs'=>0),
        array('field'=>'synopsis', 'name'=>'Synopsis', 'num_diffs'=>0),
        array('field'=>'description', 'name'=>'Description', 'num_diffs'=>0),
        );

    //
    // Rollup tags
    //
    foreach($tenant_products as $pid => $product) {
        $tenant_products[$pid]['tags10'] = '';
        $tenant_products[$pid]['tags11'] = '';
        $tenant_products[$pid]['tags12'] = '';
        $tenant_products[$pid]['tags13'] = '';
        $tenant_products[$pid]['tags14'] = '';
        $tenant_products[$pid]['tags15'] = '';
        if( isset($product['tags']) ) {
            foreach($product['tags'] as $tag) {
                $tenant_products[$pid]['tags' . $tag['tag_type']] .= ($tenant_products[$pid]['tags' . $tag['tag_type']] != '' ? ', ' : '') . $tag['tag_name'];
            }
        }
    }
    foreach($supplier_products as $pid => $product) {
        $supplier_products[$pid]['tags10'] = '';
        $supplier_products[$pid]['tags11'] = '';
        $supplier_products[$pid]['tags12'] = '';
        $supplier_products[$pid]['tags13'] = '';
        $supplier_products[$pid]['tags14'] = '';
        $supplier_products[$pid]['tags15'] = '';
        if( isset($product['tags']) ) {
            foreach($product['tags'] as $tag) {
                $supplier_products[$pid]['tags' . $tag['tag_type']] .= ($supplier_products[$pid]['tags' . $tag['tag_type']] != '' ? ', ' : '') . $tag['tag_name'];
            }
        }
    }

    //
    // Products array to return with differences
    //
    $products = array();

    //
    // Compare products
    //
    foreach($tenant_products as $tid => $tproduct) {
        if( isset($supplier_products[$tproduct['supplier_item_number']]) ) {
            $sproduct = $supplier_products[$tproduct['supplier_item_number']];
            $supplier_products[$tproduct['supplier_item_number']]['tid'] = $tid;
            foreach($fields as $fid => $field) {
                if( $tproduct[$field['field']] != $sproduct[$field['field']] && $sproduct[$field['field']] != '' ) {
                    if( isset($args['field']) && $field['field'] == $args['field'] ) {
                        $diff_product = array(
                            'id' => $tproduct['id'],
                            'tenant_name' => $tproduct['name'],
                            'tenant_value' => $tproduct[$field['field']],
                            'supplier_value' => $sproduct[$field['field']],
                            );
                        if( isset($args['update_product_id']) && $args['update_product_id'] == $tproduct['id'] ) {
                            ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'productSupplierImport');
                            $rc = ciniki_wineproduction_productSupplierImport($ciniki, $args['tnid'], array(
                                'supplier_tnid' => $supplier_tnid,
                                'field' => $args['field'],
                                'update_product_id' => $args['update_product_id'],
                                'tproduct' => $tproduct,
                                'sproduct' => $sproduct,
                                ));
                            if( $rc['stat'] != 'ok' ) {
                                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.217', 'msg'=>'Unable to update the product', 'err'=>$rc['err']));
                            }
                            
                            // Skip adding to products, field is the same now.
                            if( preg_match("/^tags/", $args['field']) && $rc['tenant_tags'] != $sproduct[$args['field']]) {
                                $diff_product['tenant_value'] = $rc['tenant_tags'];
                            } else {
                                continue;
                            } 
                        }
                        if( $args['field'] == 'list_price' ) {
                            $diff_product['tenant_value'] = '$' . number_format($diff_product['tenant_value'], 2);
                            $diff_product['supplier_value'] = '$' . number_format($diff_product['supplier_value'], 2);
                        }
                        elseif( $args['field'] == 'primary_image_checksum' ) {
                            //
                            // Load image
                            //
                            ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'hooks', 'loadThumbnail');
                            if( $tproduct['primary_image_id'] > 0 ) {
                                $rc = ciniki_images_hooks_loadThumbnail($ciniki, $args['tnid'], 
                                    array('image_id'=>$tproduct['primary_image_id'], 'maxlength'=>75));
                                if( $rc['stat'] != 'ok' ) {
                                    return $rc;
                                }
                                $diff_product['tenant_image'] = 'data:image/jpg;base64,' . base64_encode($rc['image']);
                            }
                            // Load supplier image
                            $rc = ciniki_images_hooks_loadThumbnail($ciniki, $supplier_tnid, 
                                array('image_id'=>$sproduct['primary_image_id'], 'maxlength'=>75));
                            if( $rc['stat'] != 'ok' ) {
                                return $rc;
                            }
                            $diff_product['supplier_image'] = 'data:image/jpg;base64,' . base64_encode($rc['image']);
                        }
                        $products[] = $diff_product;
                    }
                    $fields[$fid]['num_diffs']++;
                }
            }
        }
    }

    //
    // Check for new products
    //
    $new_products = array();
    foreach($supplier_products as $product) {
        if( !isset($product['tid']) ) {
            if( isset($args['import_sku']) && $args['import_sku'] == $product['supplier_item_number'] ) {

                //
                // Import product
                //
                $tproduct = $sproduct;
                $primary_image_id = $sproduct['primary_image_id'];
                $sproduct['primary_image_id'] = 0;
                $product['supplier_id'] = $args['supplier_id'];
                ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
                $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.wineproduction.product', $product, 0x04);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.226', 'msg'=>'Unable to add the product', 'err'=>$rc['err']));
                }

                $tproduct['id'] = $rc['id'];
                //
                // Import the primary image
                //
                if( $product['primary_image_id'] > 0 ) {
/*                    $rc = ciniki_wineproduction_productSupplierImport($ciniki, $args['tnid'], array(
                        'supplier_tnid' => $supplier_tnid,
                        'field' => 'primary_image_id',
                        'update_product_id' => $args['update_product_id'],
                        'tproduct' => $tproduct,
                        'sproduct' => $sproduct,
                        ));
*/        
                }

                

            } else {
                $new_products[] = $product;
            }
        }
    }
    if( count($new_products) > 0 ) {
        array_unshift($fields, array(
            'field' => 'new', 
            'name' => 'New Products',
            'num_diffs' => count($new_products),
            ));
    }
    if( $args['field'] == 'new' ) {
        $products = $new_products;
    }

    return array('stat'=>'ok', 'suppliers'=>$suppliers, 'fields'=>$fields, 'products'=>$products, 'new_products'=>$new_products);
}
?>
