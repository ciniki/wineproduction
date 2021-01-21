<?php
//
// Description
// -----------
// This method will return the list of Product Files for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Product File for.
//
// Returns
// -------
//
function ciniki_wineproduction_productFileList($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.productFileList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of files
    //
    $strsql = "SELECT ciniki_wineproduction_product_files.id, "
        . "ciniki_wineproduction_product_files.product_id, "
        . "ciniki_wineproduction_product_files.extension, "
        . "ciniki_wineproduction_product_files.name, "
        . "ciniki_wineproduction_product_files.permalink, "
        . "ciniki_wineproduction_product_files.webflags, "
        . "ciniki_wineproduction_product_files.org_filename, "
        . "ciniki_wineproduction_product_files.publish_date "
        . "FROM ciniki_wineproduction_product_files "
        . "WHERE ciniki_wineproduction_product_files.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'files', 'fname'=>'id', 
            'fields'=>array('id', 'product_id', 'extension', 'name', 'permalink', 'webflags', 'org_filename', 'publish_date')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['files']) ) {
        $files = $rc['files'];
        $file_ids = array();
        foreach($files as $iid => $file) {
            $file_ids[] = $file['id'];
        }
    } else {
        $files = array();
        $file_ids = array();
    }

    return array('stat'=>'ok', 'files'=>$files, 'nplist'=>$file_ids);
}
?>
