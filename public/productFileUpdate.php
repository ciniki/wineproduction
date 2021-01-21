<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant to the file is attached to.
// name:                The name of the file.  
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_wineproduction_productFileUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'file_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'File'), 
        'name'=>array('required'=>'no', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Name'),
        'description'=>array('required'=>'no', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Description'),
        'webflags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Web Flags'),
        'publish_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'Publish Date'),
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

    if( isset($args['name']) && (!isset($args['permalink']) || $args['permalink'] == '') ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
        $args['permalink'] = ciniki_core_makePermalink($ciniki, $name);
    }

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.productFileUpdate', 0); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

    //
    // Get the current information about the file
    //
    $strsql = "SELECT id, product_id, name, permalink "
        . "FROM ciniki_wineproduction_product_files "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['file_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'file');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['file']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.171', 'msg'=>'File does not exist.'));
    }
    $file = $rc['file'];

    //
    // Check the permalink doesn't already exist
    //
    if( isset($args['permalink']) ) {
        $strsql = "SELECT id, name, permalink "
            . "FROM ciniki_wineproduction_product_files "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND product_id = '" . ciniki_core_dbQuote($ciniki, $file['product_id']) . "' "
            . "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
            . "AND id <> '" . ciniki_core_dbQuote($ciniki, $args['file_id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'files');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( $rc['num_rows'] > 0 ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.172', 'msg'=>'You already have a file with this name, please choose another name.'));
        }
    }

    //
    // Update the file in the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    return ciniki_core_objectUpdate($ciniki, $args['tnid'], 'ciniki.wineproduction.productfile', $args['file_id'], $args, 0x07);
}
?>
