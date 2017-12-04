<?php
//
// Description
// -----------
// This function will return a list of user interface settings for the module.
//
// Arguments
// ---------
// ciniki:
// tnid:     The ID of the tenant to get events for.
//
// Returns
// -------
//
function ciniki_wineproduction_hooks_uiSettings($ciniki, $tnid, $args) {

    //
    // Setup the default response
    //
    $rsp = array('stat'=>'ok', 'menu_items'=>array(), 'settings_menu_items'=>array());

    //
    // Check if wineproduction flag is set, and if the user has permissions
    //
    if( isset($ciniki['tenant']['modules']['ciniki.wineproduction'])
        && (isset($args['permissions']['owners'])
            || isset($args['permissions']['employees'])
            || isset($args['permissions']['resellers'])
            || ($ciniki['session']['user']['perms']&0x01) == 0x01
            )
        ) {
        $menu_item = array(
            'priority'=>6300,
            'label'=>'Wine Production', 
            'edit'=>array('app'=>'ciniki.wineproduction.main'),
            'add'=>array('app'=>'ciniki.wineproduction.main', 'args'=>array('add'=>'\'"yes"\'')),
            'search'=>array(
                'method'=>'ciniki.wineproduction.searchQuick',
                'args'=>array(),
                'container'=>'orders',
                'cols'=>8,
                'headerValues'=>array('INV#', 'Wine', 'Type', 'BD', 'OD', 'SD', 'RD', 'FD'),
                'cellClasses'=>array('multiline', 'multiline', 'multiline', 'multiline aligncenter', 'multiline aligncenter', 'multiline aligncenter', 'multiline aligncenter', 'multiline aligncenter'),
                'cellValues'=>array(
                    '0'=>'\'<span class="maintext">\' + d.order.invoice_number + \'</span><span class="subtext">\' + M.ciniki_tenants_main.statusOptions[d.order.status] + \'</span>\'',
                    '1'=>'\'<span class="maintext">\' + d.order.wine_name + \'</span><span class="subtext">\' + d.order.customer_name + \'</span>\'',
                    '2'=>'\'<span class="maintext">\' + d.order.wine_type + \'</span><span class="subtext">\' + d.order.kit_length + \'&nbsp;weeks</span>\'',
                    '3'=>'if( d.order.bottling_date != null && d.order.bottling_date != \'\' ) { d.order.bottling_date.replace(/(...)\s([0-9]+),\s([0-9][0-9][0-9][0-9])/, \'<span class="maintext">$1</span><span class="subtext">$2</span>\') } else { \'\'; }',
                    '4'=>'if( d.order.order_date != null && d.order.order_date != \'\' ) { d.order.order_date.replace(/(...)\s([0-9]+),\s([0-9][0-9][0-9][0-9])/, \'<span class="maintext">$1</span><span class="subtext">$2</span>\') } else { \'\'; }',
                    '5'=>'if( d.order.start_date != null && d.order.start_date != \'\' ) { d.order.start_date.replace(/(...)\s([0-9]+),\s([0-9][0-9][0-9][0-9])/, \'<span class="maintext">$1</span><span class="subtext">$2</span>\') } else { \'\'; }',
                    '6'=>'if( d.order.racking_date != null && d.order.racking_date != \'\' ) { d.order.racking_date.replace(/(...)\s([0-9]+),\s([0-9][0-9][0-9][0-9])/, \'<span class="maintext">$1</span><span class="subtext">$2</span>\') } else { \'\'; }',
                    '7'=>'if( d.order.filtering_date != null && d.order.filtering_date != \'\' ) { d.order.filtering_date.replace(/(...)\s([0-9]+),\s([0-9][0-9][0-9][0-9])/, \'<span class="maintext">$1</span><span class="subtext">$2</span>\') } else { \'\'; }',
                    ),
                'noData'=>'No wineproduction found',
                'edit'=>array('method'=>'ciniki.wineproduction.main', 'args'=>array('order_id'=>'d.order.id;')),
                'submit'=>array('method'=>'ciniki.wineproduction.main', 'args'=>array('search'=>'search_str')),
                ),
            );
        $rsp['menu_items'][] = $menu_item;
    } 

    //
    // Check permissions for what menu items should be available
    //
    if( isset($ciniki['tenant']['modules']['ciniki.wineproduction'])
        && (isset($args['permissions']['owners'])
            || isset($args['permissions']['employees'])
            || isset($args['permissions']['resellers'])
            || ($ciniki['session']['user']['perms']&0x01) == 0x01
            )
        ) {
        $menu_item = array(
            'priority'=>3900,
            'label'=>'Production Schedule', 
            'edit'=>array('app'=>'ciniki.wineproduction.main', 'args'=>array('schedule'=>'"\'today\'"')),
            );
        $rsp['menu_items'][] = $menu_item;
    } 

    if( isset($ciniki['tenant']['modules']['ciniki.wineproduction'])
        && (isset($args['permissions']['owners'])
            || isset($args['permissions']['resellers'])
            || ($ciniki['session']['user']['perms']&0x01) == 0x01
            )
        ) {
        $rsp['settings_menu_items'][] = array('priority'=>3900, 'label'=>'Wine Production', 'edit'=>array('app'=>'ciniki.wineproduction.settings'));
    }

    return $rsp;
}
?>
