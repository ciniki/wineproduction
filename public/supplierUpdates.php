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
        . "products.synopsis, "
        . "products.description, "
        . "products.last_updated, "
        . "tags.tag_type, "
        . "tags.tag_name, "
        . "tags.permalink AS tag_permalink "
        . "FROM ciniki_wineproduction_products AS products "
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
                'supplier_item_number', 'list_price', 'synopsis', 'description',
                )),
        array('container'=>'tags', 'fname'=>'id', 
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
        . "products.synopsis, "
        . "products.description, "
        . "products.last_updated, "
        . "tags.id AS tag_id, "
        . "tags.tag_type, "
        . "tags.tag_name, "
        . "tags.permalink AS tag_permalink "
        . "FROM ciniki_wineproduction_products AS products "
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
                'supplier_item_number', 'list_price', 'synopsis', 'description',
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
        array('field'=>'synopsis', 'name'=>'Synopsis', 'num_diffs'=>0),
        array('field'=>'description', 'name'=>'Description', 'num_diffs'=>0),
        );

    //
    // Products array to return with differences
    //
    $products = array();

    //
    // Compare products
    //
    foreach($tenant_products as $tproduct) {
        if( isset($supplier_products[$tproduct['supplier_item_number']]) ) {
            $sproduct = $supplier_products[$tproduct['supplier_item_number']];
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
                            $update_args = array();
                            $update_args[$field['field']] = $sproduct[$field['field']];
                            ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
                            $rc = ciniki_core_objectUpdate($ciniki, $args['tnid'], 'ciniki.wineproduction.product', 
                                $args['update_product_id'], 
                                $update_args, 
                                0x04);
                            if( $rc['stat'] != 'ok' ) {
                                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.216', 'msg'=>'Unable to update the product', 'err'=>$rc['err']));
                            }
                            
                            // Skip adding to products, same now.
                            continue;
                        }
                        if( $args['field'] == 'list_price' ) {
                            $diff_product['tenant_value'] = '$' . number_format($diff_product['tenant_value'], 2);
                            $diff_product['supplier_value'] = '$' . number_format($diff_product['supplier_value'], 2);
                        }
                        $products[] = $diff_product;
                    }
                    $fields[$fid]['num_diffs']++;
                }
            }
        }
    }

    return array('stat'=>'ok', 'suppliers'=>$suppliers, 'fields'=>$fields, 'products'=>$products);
}
?>
