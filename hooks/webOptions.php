<?php
//
// Description
// -----------
// This function will return the list of options for the module that can be set for the website.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure.
// tnid:     The ID of the tenant to get options for.
//
// args:            The possible arguments for profiles
//
//
// Returns
// -------
//
function ciniki_wineproduction_hooks_webOptions(&$ciniki, $tnid, $args) {

    //
    // Check to make sure the module is enabled
    //
    if( !isset($ciniki['tenant']['modules']['ciniki.wineproduction']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.108', 'msg'=>"I'm sorry, the page you requested does not exist."));
    }

    //
    // Get the settings from the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_settings', 'tnid', $tnid, 'ciniki.web', 'settings', 'page');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['settings']) ) {
        $settings = array();
    } else {
        $settings = $rc['settings'];
    }

    $pages = array();

    $pages['ciniki.wineproduction'] = array('name'=>'Wine Products', 'options'=>array(
        array('label'=>'Category Format',
            'setting'=>'page-wineproducts-categories-format', 
            'type'=>'toggle',
            'value'=>(isset($settings['page-wineproducts-categories-format'])?$settings['page-wineproducts-categories-format']:'thumbnails'),
            'toggles'=>array(
                array('value'=>'thumbnails', 'label'=>'Thumbnails'),
                array('value'=>'list', 'label'=>'List'),
                array('value'=>'tradingcards', 'label'=>'Trading Cards'),
                ),
            ),
        array('label'=>'List Format',
            'setting'=>'page-wineproducts-list-format',
            'type'=>'toggle',
            'value'=>(isset($settings['page-wineproducts-list-format'])?$settings['page-wineproducts-list-format']:'imagelist'),
            'toggles'=>array(
                array('value'=>'imagelist', 'label'=>'Image List'),
                array('value'=>'tradingcards', 'label'=>'Trading Cards'),
                ),
            ),
        array('label'=>'Thumbnail Format',
            'setting'=>'page-wineproducts-thumbnail-format', 
            'type'=>'toggle',
            'value'=>(isset($settings['page-wineproducts-thumbnail-format'])?$settings['page-wineproducts-thumbnail-format']:'square-cropped'),
            'toggles'=>array(
                array('value'=>'square-cropped', 'label'=>'Cropped'),
                array('value'=>'square-padded', 'label'=>'Padded'),
                ),
            ),
        array('label'=>'Thumbnail Padding Color',
            'setting'=>'page-wineproducts-thumbnail-padding-color', 
            'type'=>'colour',
            'value'=>(isset($settings['page-wineproducts-thumbnail-padding-color'])?$settings['page-wineproducts-thumbnail-padding-color']:'#ffffff'),
            ),
        ));

    return array('stat'=>'ok', 'pages'=>$pages);
}
?>
