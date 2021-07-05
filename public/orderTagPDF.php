<?php
//
// Description
// ===========
// This method will return the file in it's binary form.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:     The ID of the tenant the requested file belongs to.
// file_id:         The ID of the file to be downloaded.
//
// Returns
// -------
// Binary file.
//
function ciniki_wineproduction_orderTagPDF($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'order_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Order'), 
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.orderTagPDF', 0); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Load the settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_wineproduction_settings', 'tnid', $args['tnid'], 'ciniki.wineproduction', 'settings', '');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.276', 'msg'=>'Unable to load settings', 'err'=>$rc['err']));
    }
    $settings = isset($rc['settings']) ? $rc['settings'] : array();
   

    $tags_template = isset($settings['tags.template']) && $settings['tags.template'] != '' ? $settings['tags.template'] : 'halfpage';

    $rc = ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'templates', $tags_template . 'Tags');
    if( $rc['stat'] == 'ok' ) {
        $fn = $rc['function_call'];
        $rc = $fn($ciniki, $args['tnid'], array('order_id'=>$args['order_id']));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.275', 'msg'=>'Unable to generate Order Tag', 'err'=>$rc['err']));
        }

        if( isset($rc['pdf']) ) {
            $rc['pdf']->Output($rc['filename'], 'I');
        }
        return array('stat'=>'binary');
    }
    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.277', 'msg'=>'No template selected'));
}
?>
