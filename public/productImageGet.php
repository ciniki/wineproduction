<?php
//
// Description
// ===========
// This method will return all the information about an product image.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant the product image is attached to.
// productimage_id:          The ID of the product image to get the details for.
//
// Returns
// -------
//
function ciniki_wineproduction_productImageGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'productimage_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Product Image'),
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.productImageGet');
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

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'php');

    //
    // Return default for new Product Image
    //
    if( $args['productimage_id'] == 0 ) {
        $image = array('id'=>0,
            'product_id'=>'',
            'name'=>'',
            'permalink'=>'',
            'webflags'=>0x01,
            'sequence'=>'',
            'image_id'=>'',
            'description'=>'',
        );
    }

    //
    // Get the details for an existing Product Image
    //
    else {
        $strsql = "SELECT ciniki_wineproduction_product_images.id, "
            . "ciniki_wineproduction_product_images.product_id, "
            . "ciniki_wineproduction_product_images.name, "
            . "ciniki_wineproduction_product_images.permalink, "
            . "ciniki_wineproduction_product_images.webflags, "
            . "ciniki_wineproduction_product_images.sequence, "
            . "ciniki_wineproduction_product_images.image_id, "
            . "ciniki_wineproduction_product_images.description "
            . "FROM ciniki_wineproduction_product_images "
            . "WHERE ciniki_wineproduction_product_images.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_wineproduction_product_images.id = '" . ciniki_core_dbQuote($ciniki, $args['productimage_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
            array('container'=>'images', 'fname'=>'id', 
                'fields'=>array('product_id', 'name', 'permalink', 'webflags', 'sequence', 'image_id', 'description'),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.156', 'msg'=>'Product Image not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['images'][0]) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.157', 'msg'=>'Unable to find Product Image'));
        }
        $image = $rc['images'][0];
    }

    return array('stat'=>'ok', 'image'=>$image);
}
?>
