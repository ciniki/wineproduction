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
function ciniki_wineproduction_productSupplierImport(&$ciniki, $tnid, $args) {

    if( !isset($args['supplier_tnid']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.218', 'msg'=>'No supplier specified'));
    }
    if( !isset($args['field']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.219', 'msg'=>'No field specified'));
    }
    if( !isset($args['update_product_id']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.220', 'msg'=>'No product specified'));
    }
    if( !isset($args['tproduct']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.221', 'msg'=>'No product specified'));
    }
    if( !isset($args['sproduct']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.222', 'msg'=>'No product specified'));
    }

    $update_args = array();

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'storageFileLoad');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'storageFileAdd');

    //
    // If primary image, then copy image into tenant
    //
    if( $args['field'] == 'primary_image_checksum' ) {
        //
        // Load image from supplier
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'hooks', 'loadOriginal');
        $rc = ciniki_images_hooks_loadOriginal($ciniki, $args['supplier_tnid'], array(
            'image_id' => $args['sproduct']['primary_image_id'],
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.247', 'msg'=>'Unable to load image', 'err'=>$rc['err']));
        }
        $supplier_image = $rc;

        //
        // Insert image
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'hooks', 'insertFromImagick');
        $rc = ciniki_images_hooks_insertFromImagick($ciniki, $tnid, array(
            'image' => $supplier_image['image'],
            'original_filename' => $supplier_image['original_filename'],
            'checksum' => $supplier_image['checksum'],
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.251', 'msg'=>'Unable to add image', 'err'=>$rc['err']));
        }
        $update_args['primary_image_id'] = $rc['id'];
    } 

    //
    // Check for an additional image
    //
    elseif( $args['field'] == 'additional_images' ) {
        foreach($args['sproduct']['images'] as $image) {
            if( !in_array($image['checksum'], $args['tproduct']['additional_image_checksums']) ) {
                //
                // Load image from supplier
                //
                ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'hooks', 'loadOriginal');
                $rc = ciniki_images_hooks_loadOriginal($ciniki, $args['supplier_tnid'], array(
                    'image_id' => $image['image_id']),
                    );
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.223', 'msg'=>'Unable to load image', 'err'=>$rc['err']));
                }
                $supplier_image = $rc;

                //
                // Insert image
                //
                ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'hooks', 'insertFromImagick');
                $rc = ciniki_images_hooks_insertFromImagick($ciniki, $tnid, array(
                    'image' => $supplier_image['image'],
                    'original_filename' => $supplier_image['original_filename'],
                    'checksum' => $supplier_image['checksum'],
                    ));
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.224', 'msg'=>'Unable to add image', 'err'=>$rc['err']));
                } 
                $image_id = $rc['id'];

                $permalink = $image['permalink'];
                $uuid = '';
                if( $image['name'] == '' ) {
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUUID');
                    $rc = ciniki_core_dbUUID($ciniki, 'ciniki.wineproduction');
                    if( $rc['stat'] != 'ok' ) {
                        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.232', 'msg'=>'Unable to generate ID', 'err'=>$rc['err']));
                    }
                    $permalink = $rc['uuid'];
                    $uuid = $rc['uuid'];
                }

                //
                // Add to the product
                //
                ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
                $rc = ciniki_core_objectAdd($ciniki, $tnid, 'ciniki.wineproduction.productimage', array(
                    'product_id' => $args['update_product_id'],
                    'uuid' => $uuid,
                    'name' => $image['name'],
                    'permalink' => $permalink,
                    'webflags' => 1,
                    'sequence' => 1,
                    'image_id' => $image_id,
                    'description' => '',
                    ), 0x04);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.231', 'msg'=>'Unable to add the productimage', 'err'=>$rc['err']));
                }
                    
            }
        }
    }

    //
    // Check for files 
    //
    elseif( $args['field'] == 'file_names' ) {
        foreach($args['sproduct']['files'] as $file) {
            if( !in_array($file['org_filename'], $args['tproduct']['files_org_filenames']) ) {
                //
                // Load supplier file
                //
                $rc = ciniki_core_storageFileLoad($ciniki, $args['supplier_tnid'], 'ciniki.wineproduction.productfile', array(
                    'subdir' => 'files',
                    'uuid' => $file['uuid'],
                    ));
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.237', 'msg'=>'Unable to load supplier file', 'err'=>$rc['err']));
                }
                $binary_content = $rc['binary_content'];

                //
                // Generate new UUID also used to store file
                //
                ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUUID');
                $rc = ciniki_core_dbUUID($ciniki, 'ciniki.wineproduction');
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.236', 'msg'=>'Unable to generate ID', 'err'=>$rc['err']));
                }
                $uuid = $rc['uuid'];

                $rc = ciniki_core_storageFileAdd($ciniki, $tnid, 'ciniki.wineproduction.productfile', array(
                    'subdir' => 'files',
                    'uuid' => $uuid,
                    'binary_content' => $binary_content,
                    ));
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.238', 'msg'=>'Unable to load supplier file', 'err'=>$rc['err']));
                }

                //
                // Add to the product
                //
                $file['product_id'] = $args['update_product_id'];
                $file['uuid'] = $uuid;
                $file['webflags'] = 0x01;   // Visible 
                $file['binary_content'] = '';
                ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
                $rc = ciniki_core_objectAdd($ciniki, $tnid, 'ciniki.wineproduction.productfile', $file, 0x04);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.239', 'msg'=>'Unable to add the product file', 'err'=>$rc['err']));
                }
            }
        }
    }

    //
    // Check for categories
    //
    elseif( $args['field'] == 'tags10' || $args['field'] == 'tags11' || $args['field'] == 'tags12' 
        || $args['field'] == 'tags13' || $args['field'] == 'tags14' || $args['field'] == 'tags15'
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
        $tenant_tags = explode(',', $args['tproduct'][$args['field']]);
        foreach($tenant_tags as $tid => $tag) {
            $tenant_tags[$tid] = trim($tag);
        }
        $supplier_tags = explode(',', $args['sproduct'][$args['field']]);
        $tag_type = 10;
        switch($args['field']) {
            case 'tags10': $tag_type = 10; break;
            case 'tags11': $tag_type = 11; break;
            case 'tags12': $tag_type = 12; break;
            case 'tags13': $tag_type = 13; break;
            case 'tags14': $tag_type = 14; break;
            case 'tags15': $tag_type = 15; break;
        }
        foreach($supplier_tags as $tag) {
            $tag = trim($tag);
            //
            // Check if it already exists
            //
            if( !in_array($tag, $tenant_tags) ) {
                $tenant_tags[] = $tag;
                ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
                $rc = ciniki_core_objectAdd($ciniki, $tnid, 'ciniki.wineproduction.producttag', array(
                    'product_id' => $args['tproduct']['id'],
                    'tag_type' => $tag_type,
                    'tag_name' => $tag,
                    'permalink' => ciniki_core_makePermalink($ciniki, $tag),
                    ), 0x04);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.225', 'msg'=>'Unable to add the category', 'err'=>$rc['err']));
                }
            }
        }
        sort($tenant_tags);
        return array('stat'=>'ok', 'tenant_tags'=>implode(', ', $tenant_tags));
    }

    //
    // Simple field, update
    //
    else {
        $update_args[$args['field']] = $args['sproduct'][$args['field']];
    }

    //
    // Update the product
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    $rc = ciniki_core_objectUpdate($ciniki, $tnid, 'ciniki.wineproduction.product', 
        $args['update_product_id'], 
        $update_args, 
        0x04);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.216', 'msg'=>'Unable to update the product', 'err'=>$rc['err']));
    }

    return array('stat'=>'ok');
}
?>
