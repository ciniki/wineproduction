<?php
//
// Description
// -----------
// This function will return the report details for a requested report block.
//
// Arguments
// ---------
// ciniki:
// tnid:     The ID of the tenant.
// args:            The possible arguments for.
//
//
// Returns
// -------
//
function ciniki_wineproduction_reporting_block(&$ciniki, $tnid, $args) {

    //
    // Check to make sure the module is enabled
    //
    if( !isset($ciniki['tenant']['modules']['ciniki.wineproduction']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.48', 'msg'=>"That report is not available."));
    }

    //
    // Check to make sure the report is specified
    //
    if( !isset($args['block_ref']) || !isset($args['options']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.49', 'msg'=>"No block specified."));
    }

    //
    // The array to store the report data
    //

    //
    // Return the list of reports for the tenant
    //
    if( $args['block_ref'] == 'ciniki.wineproduction.winesprocessed' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'reporting', 'blockWinesProcessed');
        return ciniki_wineproduction_reporting_blockWinesProcessed($ciniki, $tnid, $args['options']);
    }

    return array('stat'=>'ok');
}
?>
