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
       
    ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'hooks', 'loadThumbnail');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'productSupplierImport');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');

    //
    // Get the list of suppliers that have supplier_tnid
    //
    $strsql = "SELECT id, name, supplier_tnid "
        . "FROM ciniki_wineproduction_suppliers "
        . "WHERE supplier_tnid > 0 "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "ORDER BY name ";
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
        . "products.package_qty, "
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
        . "ORDER BY products.name, products.supplier_item_number, tags.tag_type, tags.tag_name "
        . "";
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'products', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'status', 'wine_type', 'kit_length', 
                'supplier_item_number', 'package_qty', 'list_price', 'primary_image_id', 'primary_image_checksum', 
                'synopsis', 'description',
                )),
        array('container'=>'tags', 'fname'=>'tag_id', 
            'fields'=>array('tag_type', 'tag_name', 'tag_permalink'),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.121', 'msg'=>'Unable to load products', 'err'=>$rc['err']));
    }
    $tenant_products = isset($rc['products']) ? $rc['products'] : array();

    //
    // Load all the tenant product images
    //
    $strsql = "SELECT products.id AS product_id, "
        . "pimages.id AS pimage_id, "
        . "images.id AS image_id, "
        . "images.title, "
        . "images.caption, "
        . "images.original_filename, "
        . "images.checksum "
        . "FROM ciniki_wineproduction_products AS products "
        . "INNER JOIN ciniki_wineproduction_product_images AS pimages ON ("
            . "products.id = pimages.product_id "
            . "AND pimages.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . ") "
        . "LEFT JOIN ciniki_images AS images ON ("
            . "pimages.image_id = images.id "
            . "AND images.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") "
        . "WHERE products.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND supplier_id = '" . ciniki_core_dbQuote($ciniki, $args['supplier_id']) . "' "
        . "AND products.status = 10 "   // Active products
        . "ORDER BY products.id, images.checksum "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'products', 'fname'=>'product_id', 'fields'=>array('id'=>'product_id')),
        array('container'=>'images', 'fname'=>'image_id', 'fields'=>array('image_id', 'title', 'caption', 'original_filename', 'checksum')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.230', 'msg'=>'Unable to load images', 'err'=>$rc['err']));
    }
    $tenant_images = isset($rc['products']) ? $rc['products'] : array();
    
    //
    // Load all the tenant product files
    //
    $strsql = "SELECT products.id AS product_id, "
        . "files.id AS file_id, "
        . "files.uuid, "
        . "files.extension, "
        . "files.name, "
        . "files.permalink, "
        . "files.webflags, "
        . "files.description, "
        . "files.org_filename, "
        . "files.publish_date "
        . "FROM ciniki_wineproduction_products AS products "
        . "INNER JOIN ciniki_wineproduction_product_files AS files ON ("
            . "products.id = files.product_id "
            . "AND files.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . ") "
        . "WHERE products.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND supplier_id = '" . ciniki_core_dbQuote($ciniki, $args['supplier_id']) . "' "
        . "AND products.status = 10 "   // Active products
        . "ORDER BY products.id, files.name "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'products', 'fname'=>'product_id', 'fields'=>array('id'=>'product_id')),
        array('container'=>'files', 'fname'=>'file_id', 'fields'=>array('id'=>'file_id', 'uuid', 'extension', 'name', 'permalink', 'webflags', 'description', 'org_filename', 'publish_date')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.235', 'msg'=>'Unable to load files', 'err'=>$rc['err']));
    }
    $tenant_files = isset($rc['products']) ? $rc['products'] : array();

    //
    // Load all the products and tags from the supplier
    //
    $strsql = "SELECT products.id, "
        . "products.ptype, "
        . "products.name, "
        . "products.permalink, "
        . "products.status, "
        . "products.wine_type, "
        . "products.kit_length, "
        . "products.supplier_item_number, "
        . "products.package_qty, "
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
        . "ORDER BY products.name, products.supplier_item_number, tags.tag_type, tags.tag_name "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'products', 'fname'=>'supplier_item_number', 
            'fields'=>array('id', 'ptype', 'name', 'permalink', 'status', 'wine_type', 'kit_length', 
                'supplier_item_number', 'package_qty', 'list_price', 'primary_image_id', 'primary_image_checksum', 
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
    // Load supplier images
    //
    $strsql = "SELECT products.id AS product_id, "
        . "pimages.name, "
        . "pimages.permalink, "
        . "pimages.id AS pimage_id, "
        . "images.id AS image_id, "
        . "images.title, "
        . "images.caption, "
        . "images.original_filename, "
        . "images.checksum "
        . "FROM ciniki_wineproduction_products AS products "
        . "INNER JOIN ciniki_wineproduction_product_images AS pimages ON ("
            . "products.id = pimages.product_id "
            . "AND pimages.tnid = '" . ciniki_core_dbQuote($ciniki, $supplier_tnid) . "' "
        . ") "
        . "LEFT JOIN ciniki_images AS images ON ("
            . "pimages.image_id = images.id "
            . "AND images.tnid = '" . ciniki_core_dbQuote($ciniki, $supplier_tnid) . "' "
            . ") "
        . "WHERE products.tnid = '" . ciniki_core_dbQuote($ciniki, $supplier_tnid) . "' "
        . "AND products.status = 10 "   // Active products
        . "ORDER BY products.id, images.checksum "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'products', 'fname'=>'product_id', 'fields'=>array('id'=>'product_id')),
        array('container'=>'images', 'fname'=>'image_id', 'fields'=>array('image_id', 'name', 'permalink', 'title', 'caption', 'original_filename', 'checksum')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.241', 'msg'=>'Unable to load images', 'err'=>$rc['err']));
    }
    $supplier_images = isset($rc['products']) ? $rc['products'] : array();

    //
    // Load supplier files
    //
    $strsql = "SELECT products.id AS product_id, "
        . "files.id AS file_id, "
        . "files.uuid, "
        . "files.extension, "
        . "files.name, "
        . "files.permalink, "
        . "files.webflags, "
        . "files.description, "
        . "files.org_filename, "
        . "files.publish_date "
        . "FROM ciniki_wineproduction_products AS products "
        . "INNER JOIN ciniki_wineproduction_product_files AS files ON ("
            . "products.id = files.product_id "
            . "AND files.tnid = '" . ciniki_core_dbQuote($ciniki, $supplier_tnid) . "' "
        . ") "
        . "WHERE products.tnid = '" . ciniki_core_dbQuote($ciniki, $supplier_tnid) . "' "
        . "AND products.status = 10 "   // Active products
        . "ORDER BY products.id, files.name "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'products', 'fname'=>'product_id', 'fields'=>array('id'=>'product_id')),
        array('container'=>'files', 'fname'=>'file_id', 'fields'=>array('id'=>'file_id', 'uuid', 'extension', 'name', 'permalink', 'webflags', 'description', 'org_filename', 'publish_date')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.240', 'msg'=>'Unable to load files', 'err'=>$rc['err']));
    }
    $supplier_files = isset($rc['products']) ? $rc['products'] : array();

    //
    // Compare fields
    //
    $fields = array(
        array('field'=>'wine_type', 'name'=>'Wine Type', 'num_diffs'=>0),
        array('field'=>'kit_length', 'name'=>'Kit Length', 'num_diffs'=>0),
        array('field'=>'package_qty', 'name'=>'Package Quantity', 'num_diffs'=>0),
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
        array('field'=>'additional_images', 'name'=>'Images', 'num_diffs'=>0),
        array('field'=>'file_names', 'name'=>'Files', 'num_diffs'=>0),
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
        $tenant_products[$pid]['images'] = array();
        $tenant_products[$pid]['additional_images'] = '';    // List of checksums
        $tenant_products[$pid]['additional_image_checksums'] = array();
        $tenant_products[$pid]['file_names'] = '';    // List of file 'name' fields
        $tenant_products[$pid]['files_org_filenames'] = array();
        if( isset($product['tags']) ) {
            foreach($product['tags'] as $tag) {
                $tenant_products[$pid]['tags' . $tag['tag_type']] .= ($tenant_products[$pid]['tags' . $tag['tag_type']] != '' ? ', ' : '') . $tag['tag_name'];
            }
        }
        // Build a list of the checksums
        if( isset($tenant_images[$product['id']]['images']) ) {
            $tenant_products[$pid]['images'] = $tenant_images[$product['id']]['images'];
            foreach($tenant_images[$product['id']]['images'] as $image) {
                $tenant_products[$pid]['additional_images'] .= ($tenant_products[$pid]['additional_images'] != '' ? ', ' : '') . $image['checksum'];
                $tenant_products[$pid]['additional_image_checksums'][] = $image['checksum'];
            }
        }
        // Build list of files
        if( isset($tenant_files[$product['id']]['files']) ) {
            $tenant_products[$pid]['files'] = $tenant_files[$product['id']]['files'];
            foreach($tenant_files[$product['id']]['files'] as $file) {
                $tenant_products[$pid]['file_names'] .= ($tenant_products[$pid]['file_names'] != '' ? "\n" : '') . $file['org_filename'];
                $tenant_products[$pid]['files_org_filenames'][] = $file['org_filename'];
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
        $supplier_products[$pid]['images'] = array();
        $supplier_products[$pid]['additional_images'] = '';  // List of checksums
        $supplier_products[$pid]['additional_image_checksums'] = array();
        $supplier_products[$pid]['file_names'] = '';    // List of file 'name' fields
        $supplier_products[$pid]['files_org_filenames'] = array();
        if( isset($product['tags']) ) {
            foreach($product['tags'] as $tag) {
                $supplier_products[$pid]['tags' . $tag['tag_type']] .= ($supplier_products[$pid]['tags' . $tag['tag_type']] != '' ? ', ' : '') . $tag['tag_name'];
            }
        }
        // Build a list of the checksums
        if( isset($supplier_images[$product['id']]['images']) ) {
            $supplier_products[$pid]['images'] = $supplier_images[$product['id']]['images'];
            foreach($supplier_images[$product['id']]['images'] as $image) {
                $supplier_products[$pid]['additional_images'] .= ($supplier_products[$pid]['additional_images'] != '' ? ', ' : '') . $image['checksum'];
            }
        }
        // Build list of files
        if( isset($supplier_files[$product['id']]['files']) ) {
            $supplier_products[$pid]['files'] = $supplier_files[$product['id']]['files'];
            foreach($supplier_files[$product['id']]['files'] as $file) {
                $supplier_products[$pid]['file_names'] .= ($supplier_products[$pid]['file_names'] != '' ? "\n" : '') . $file['org_filename'];
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
                            'supplier_item_number' => $tproduct['supplier_item_number'],
                            'tenant_name' => $tproduct['name'],
                            'tenant_value' => $tproduct[$field['field']],
                            'tenant_images' => array(),
                            'supplier_value' => $sproduct[$field['field']],
                            'supplier_images' => array(),
                            );
                        if( isset($args['update_product_id']) && $args['update_product_id'] == $tproduct['id'] ) {
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
                        elseif( $args['field'] == 'additional_images' ) {
                            // Add the tenant images
                            if( isset($tenant_images[$tproduct['id']]['images']) ) {
                                foreach($tenant_images[$tproduct['id']]['images'] as $image) {
                                    $rc = ciniki_images_hooks_loadThumbnail($ciniki, $args['tnid'], 
                                        array('image_id'=>$image['image_id'], 'maxlength'=>75));
                                    if( $rc['stat'] != 'ok' ) {
                                        return $rc;
                                    }
                                    $diff_product['tenant_images'][] = 'data:image/jpg;base64,' . base64_encode($rc['image']);
                                }
                            }
                            $available_images = array();
                            if( isset($supplier_images[$sproduct['id']]['images']) ) {
                                foreach($supplier_images[$sproduct['id']]['images'] as $image) {
                                    // Only add the images that don't exist in tenant
                                    if( !in_array($image['checksum'], $tproduct['additional_image_checksums']) ) {
                                        $rc = ciniki_images_hooks_loadThumbnail($ciniki, $supplier_tnid, 
                                            array('image_id'=>$image['image_id'], 'maxlength'=>75));
                                        if( $rc['stat'] != 'ok' ) {
                                            return $rc;
                                        }
                                        $diff_product['supplier_images'][] = 'data:image/jpg;base64,' . base64_encode($rc['image']);
                                    }
                                }
                            }
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
    foreach($supplier_products as $sproduct) {
        if( !isset($sproduct['tid']) ) {
            if( isset($args['import_sku']) && $args['import_sku'] == $sproduct['supplier_item_number'] ) {
                //
                // Check product name does not already exist
                //
                $strsql = "SELECT products.id, "
                    . "products.name, "
                    . "products.supplier_id, "
                    . "products.supplier_item_number "
                    . "FROM ciniki_wineproduction_products AS products "
                    . "WHERE ("
                        . "products.name = '" . ciniki_core_dbQuote($ciniki, $sproduct['name']) . "' "
                        . "OR products.supplier_item_number = '" . ciniki_core_dbQuote($ciniki, $sproduct['supplier_item_number']) . "' "
                        . ") "
                    . "AND products.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                    . "";
                $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'product');
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.227', 'msg'=>'Unable to load product', 'err'=>$rc['err']));
                }
                if( isset($rc['rows']) && count($rc['rows']) > 0 ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.228', 'msg'=>'Product name or sku already exists'));
                }

                ciniki_core_loadMethod($ciniki, 'ciniki', 'taxes', 'hooks', 'taxTypes');
                $rc = ciniki_taxes_hooks_taxTypes($ciniki, $args['tnid'], array());
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.229', 'msg'=>'Unable to load taxes', 'err'=>$rc['err']));
                }
                $taxtype_id = 0;
                if( isset($rc['types'][1]['id']) ) {
                    $taxtype_id = $rc['types'][1]['id'];
                }

                //
                // Setup the main product details to import
                //
                $tproduct = $sproduct;
                $tproduct['supplier_id'] = $args['supplier_id'];
                $tproduct['list_discount_percent'] = 0;
                $tproduct['cost'] = $tproduct['list_price'];
                $tproduct['taxtype_id'] = $taxtype_id;
                $tproduct['inventory_current_num'] = 0;
                $tproduct['primary_image_id'] = 0;
                
                //
                // Add new product
                //
                ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
                $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.wineproduction.product', $tproduct, 0x04);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.226', 'msg'=>'Unable to add the product', 'err'=>$rc['err']));
                }
                $tproduct['id'] = $rc['id'];

                //
                // Import the primary image
                //
                if( $sproduct['primary_image_id'] > 0 ) {
                    $rc = ciniki_wineproduction_productSupplierImport($ciniki, $args['tnid'], array(
                        'supplier_tnid' => $supplier_tnid,
                        'field' => 'primary_image_checksum',
                        'update_product_id' => $tproduct['id'],
                        'tproduct' => $tproduct,
                        'sproduct' => $sproduct,
                        ));
                }

                //
                // Import the tags
                //
                for($i = 10; $i <= 15; $i++ ) {
                    $tproduct['tags10'] = '';
                    $tproduct['tags11'] = '';
                    $tproduct['tags12'] = '';
                    $tproduct['tags13'] = '';
                    $tproduct['tags14'] = '';
                    $tproduct['tags15'] = '';
                    $rc = ciniki_wineproduction_productSupplierImport($ciniki, $args['tnid'], array(
                        'supplier_tnid' => $supplier_tnid,
                        'field' => 'tags' . $i,
                        'update_product_id' => $tproduct['id'],
                        'tproduct' => $tproduct,
                        'sproduct' => $sproduct,
                        ));
                }

                //
                // Import images
                //
                if( isset($supplier_images[$sproduct['id']]['images']) ) {
                    foreach($supplier_images[$sproduct['id']]['images'] as $image) {
                        $rc = ciniki_wineproduction_productSupplierImport($ciniki, $args['tnid'], array(
                            'supplier_tnid' => $supplier_tnid,
                            'field' => 'additional_images',
                            'update_product_id' => $tproduct['id'],
                            'tproduct' => $tproduct,
                            'sproduct' => $sproduct,
                            'image' => $image,
                            ));
                    }
                }

                //
                // Import files
                //
                if( isset($supplier_files[$sproduct['id']]['files']) ) {
                    foreach($supplier_files[$sproduct['id']]['files'] as $file) {
                        $rc = ciniki_wineproduction_productSupplierImport($ciniki, $args['tnid'], array(
                            'supplier_tnid' => $supplier_tnid,
                            'field' => 'file_names',
                            'update_product_id' => $tproduct['id'],
                            'tproduct' => $tproduct,
                            'sproduct' => $sproduct,
                            ));
                    }
                }
            } else {
                $new_products[] = $sproduct;
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
