<?php
//
// Description
// -----------
// This function will return the list of available blocks to the ciniki.reporting module.
//
// Arguments
// ---------
// ciniki:
// tnid:     
// args:            The possible arguments for.
//
//
// Returns
// -------
//
function ciniki_wineproduction_reporting_blocks(&$ciniki, $tnid, $args) {

    //
    // Check to make sure the module is enabled
    //
    if( !isset($ciniki['tenant']['modules']['ciniki.wineproduction']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.47', 'msg'=>"I'm sorry, the block you requested does not exist."));
    }

    $blocks = array();

    //
    // Return the list of blocks for the tenant
    //
    $blocks['ciniki.wineproduction.winesprocessed'] = array(
        'name'=>'Work Completed',
        'module' => 'Wine Production',
        'options'=>array(
            'status'=>array('label'=>'Status', 'type'=>'toggle', 'default'=>'10', 'toggles'=>array(
                '10' => 'Entered', 
                '20' => 'Started', 
                '30' => 'Racked',
                '40' => 'Filtered',
                '60' => 'Bottled',
                )),
            ),
        );
    $blocks['ciniki.wineproduction.winesprocessedsummary'] = array(
        'name'=>'Daily Summary',
        'module' => 'Wine Production',
        'options'=>array(),
        );
    $blocks['ciniki.wineproduction.productsmissing'] = array(
        'name'=>'Products Missing Information',
        'module' => 'Products',
        'options'=>array(),
        );

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
