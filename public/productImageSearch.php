<?php
//
// Description
// -----------
// This method searchs for a Product Images for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Product Image for.
// start_needle:       The search string to search for.
// limit:              The maximum number of entries to return.
//
// Returns
// -------
//
function ciniki_wineproduction_productImageSearch($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'start_needle'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Search String'),
        'limit'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Limit'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.productImageSearch');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of images
    //
    $strsql = "SELECT ciniki_wineproduction_product_images.id, "
        . "ciniki_wineproduction_product_images.product_id, "
        . "ciniki_wineproduction_product_images.name, "
        . "ciniki_wineproduction_product_images.permalink, "
        . "ciniki_wineproduction_product_images.webflags, "
        . "ciniki_wineproduction_product_images.sequence "
        . "FROM ciniki_wineproduction_product_images "
        . "WHERE ciniki_wineproduction_product_images.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ("
            . "name LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR name LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
        . ") "
        . "";
    if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
        $strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";
    } else {
        $strsql .= "LIMIT 25 ";
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'images', 'fname'=>'id', 
            'fields'=>array('id', 'product_id', 'name', 'permalink', 'webflags', 'sequence')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['images']) ) {
        $images = $rc['images'];
        $image_ids = array();
        foreach($images as $iid => $image) {
            $image_ids[] = $image['id'];
        }
    } else {
        $images = array();
        $image_ids = array();
    }

    return array('stat'=>'ok', 'images'=>$images, 'nplist'=>$image_ids);
}
?>
