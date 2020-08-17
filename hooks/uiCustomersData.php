<?php
//
// Description
// -----------
// Return the information to be displayed on the customer panel in the UI.
//
// Arguments
// ---------
// ciniki:
// tnid:     The ID of the tenant to get wineproduction for.
//
// Returns
// -------
//
function ciniki_wineproduction_hooks_uiCustomersData($ciniki, $tnid, $args) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuoteIDs');

    //
    // Load the status maps for the text description of each status
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'maps');
    $rc = ciniki_wineproduction_maps($ciniki);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $maps = $rc['maps'];
    
    //
    // Default response
    //
    $rsp = array('stat'=>'ok', 'tabs'=>array());

    //
    // Get the list of bottling appointments
    //
    $sections['ciniki.wineproduction.appointments'] = array(
        'label' => 'Bottling Appointments',
        'type' => 'simplegrid', 
        'num_cols' => 2,
        'class' => 'dayschedule',
        'headerValues' => null,
        'cellClasses' => array('multiline slice_0', 'schedule_appointment'),
        'noData' => 'No upcoming appointments',
//        'editApp' => array('app'=>'ciniki.wineproduction.main', 'args'=>array('appointment_id'=>'d.id;')),
        'cellValues' => array(
            '0' => "M.multiline(d.start_date_display, d.start_time_display)",
            '1' => "M.appointment(d.secondary_colour, d.subject, d.secondary_text)",
            ),
        'cellApps' => array(
            '1' => array('app' => 'ciniki.wineproduction.main', 'args'=>array('appointment_id'=>'d.id;')),
            ),
        'cellColours' => array(
            '0' => '',
            '1' => "d.colour"
            ),
        'data' => array(),
        );
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'hooks', 'appointments');
    $args['status'] = 'unbottled';
    $rc = ciniki_wineproduction_hooks_appointments($ciniki, $tnid, $args);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.41', 'msg'=>'Failed to load appointments', 'err'=>$rc['err']));
    }
    $sections['ciniki.wineproduction.appointments']['data'] = isset($rc['appointments']) ? $rc['appointments'] : array();


    //
    // Get the unbottled wineproduction orders
    //
    $sections['ciniki.wineproduction.currentwineproduction'] = array(
        'label' => 'Current Orders',
        'type' => 'simplegrid', 
        'num_cols' => 7,
        'headerValues' => array('INV#', 'Wine', 'OD', 'SD', 'RD', 'FD', 'BD'),
        'headerClasses' => array(
            'multiline', 
            'multiline', 
            'multiline aligncenter', 
            'multiline aligncenter', 
            'multiline aligncenter', 
            'multiline aligncenter', 
            'multiline aligncenter',
            ),
        'cellClasses' => array(
            'multiline', 
            'multiline', 
            'multiline aligncenter', 
            'multiline aligncenter', 
            'multiline aligncenter', 
            'multiline aligncenter', 
            'multiline aligncenter',
            ),
        'noData' => 'No current orders',
        'editApp' => array('app'=>'ciniki.wineproduction.main', 'args'=>array('order_id'=>'d.id;')),
        'cellValues' => array(
            '0' => "M.multiline(d.invoice_number, d.status_text);",
            '1' => "d.wine_name",
            '2' => "d.order_date",
            '3' => "d.start_date",
            '4' => "d.racking_date",
            '5' => "d.filtering_date",
            '6' => "d.bottling_date",
            ),
        'data' => array(),
        );
    $strsql = "SELECT ciniki_wineproductions.id, "
        . "ciniki_wineproductions.invoice_number, "
        . "ciniki_products.name AS wine_name, "
        . "ciniki_wineproductions.status, "
        . "ciniki_wineproductions.status AS status_text, "
        . "DATE_FORMAT(ciniki_wineproductions.order_date, '<span class=\"maintext\">%b</span><span class=\"subtext\">%e</span>') AS order_date, "
        . "DATE_FORMAT(ciniki_wineproductions.start_date, '<span class=\"maintext\">%b</span><span class=\"subtext\">%e</span>') AS start_date, "
        . "DATE_FORMAT(ciniki_wineproductions.racking_date, '<span class=\"maintext\">%b</span><span class=\"subtext\">%e</span>') AS racking_date, "
        . "DATE_FORMAT(ciniki_wineproductions.filtering_date, '<span class=\"maintext\">%b</span><span class=\"subtext\">%e</span>') AS filtering_date, "
        . "DATE_FORMAT(ciniki_wineproductions.bottling_date, '<span class=\"maintext\">%b</span><span class=\"subtext\">%e</span>') AS bottling_date "
        . "FROM ciniki_wineproductions "
        . "LEFT JOIN ciniki_products ON (ciniki_wineproductions.product_id = ciniki_products.id "
            . "AND ciniki_products.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "WHERE ciniki_wineproductions.customer_id = '" . ciniki_core_dbQuote($ciniki, $args['customer_id']) . "' "
        . "AND ciniki_wineproductions.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND ciniki_wineproductions.status < 60 "
        . "ORDER BY ciniki_wineproductions.order_date DESC "
        . "";
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproductions', array(
        array('container'=>'orders', 'fname'=>'id',
            'fields'=>array('id', 'invoice_number', 'wine_name', 'status', 'status_text',
                'order_date', 'start_date', 'racking_date', 'filtering_date', 'bottling_date'),
            'maps'=>array('status_text'=>$maps['wineproduction']['status']),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['orders']) ) {
        $sections['ciniki.wineproduction.currentwineproduction']['data'] = $rc['orders'];
    }

    //
    // Get the bottled wineproduction orders
    //
    $sections['ciniki.wineproduction.pastwineproduction'] = array(
        'label' => 'Past Orders',
        'type' => 'simplegrid', 
        'num_cols' => 4,
        'headerValues' => array('INV#', 'Wine', 'OD', 'BD'),
        'headerClasses' => array(
            'multiline', 
            'multiline', 
            'multiline aligncenter', 
            'multiline aligncenter', 
            ),
        'cellClasses' => array(
            'multiline', 
            'multiline', 
            'multiline aligncenter', 
            'multiline aligncenter', 
            ),
        'limit' => 15,
        'moreTxt' => 'More',
        'moreApp' => array('app'=>'ciniki.wineproduction.customer', 'args'=>array('customer_id'=>$args['customer_id'])),
        'noData' => 'No current orders',
        'editApp' => array('app'=>'ciniki.wineproduction.main', 'args'=>array('order_id'=>'d.id;')),
        'cellValues' => array(
            '0' => "M.multiline(d.invoice_number, d.status_text);",
            '1' => "d.wine_name",
            '2' => "d.order_date",
            '6' => "d.bottle_date",
            ),
        'data' => array(),
        );
    $strsql = "SELECT ciniki_wineproductions.id, "
        . "ciniki_wineproductions.customer_id, "
        . "ciniki_wineproductions.invoice_number, "
        . "ciniki_products.name AS wine_name, "
        . "ciniki_wineproductions.status, "
        . "ciniki_wineproductions.status AS status_text, "
        . "DATE_FORMAT(ciniki_wineproductions.order_date, '<span class=\"maintext\">%b %e</span><span class=\"subtext\">%Y</span>') AS order_date, "
        . "DATE_FORMAT(ciniki_wineproductions.bottle_date, '<span class=\"maintext\">%b %e</span><span class=\"subtext\">%Y</span>') AS bottle_date "
        . "FROM ciniki_wineproductions "
        . "LEFT JOIN ciniki_products ON (ciniki_wineproductions.product_id = ciniki_products.id "
            . "AND ciniki_products.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "WHERE ciniki_wineproductions.customer_id = '" . ciniki_core_dbQuote($ciniki, $args['customer_id']) . "' "
        . "AND ciniki_wineproductions.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND ciniki_wineproductions.status = 60 "
        . "ORDER BY ciniki_wineproductions.order_date DESC "
        . "LIMIT 17 "
        . "";
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproductions', array(
        array('container'=>'orders', 'fname'=>'id',
            'fields'=>array('id', 'customer_id', 'invoice_number', 'wine_name', 'status', 'status_text',
                'order_date', 'bottle_date'),
            'maps'=>array('status_text'=>$maps['wineproduction']['status']),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['orders']) ) {
        $sections['ciniki.wineproduction.pastwineproduction']['data'] = $rc['orders'];
    }

    $rsp['tabs'][] = array(
        'id' => 'ciniki.wineproduction.details',
        'label' => 'Wine',
        'priority' => 10000,
        'sections' => $sections,
        );

    //
    // Get the notifications for the customer
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'customerNotifications');
    $rc = ciniki_wineproduction_customerNotifications($ciniki, $tnid, $args['customer_id']);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.64', 'msg'=>'Unable to load notifications', 'err'=>$rc['err']));
    }
    $notifications = isset($rc['notifications']) ? $rc['notifications'] : array();

    //
    // Get the queue for the customer
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'customerNotificationQueue');
    $rc = ciniki_wineproduction_customerNotificationQueue($ciniki, $tnid, $args['customer_id']);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.72', 'msg'=>'', 'err'=>$rc['err']));
    }
    $queue = isset($rc['queue']) ? $rc['queue'] : array();

    $rsp['tabs'][] = array(
        'id' => 'ciniki.wineproduction.notifications',
        'label' => 'Notifications',
        'sections' => array(
            'notifications' => array(
                'label' => 'Notifications',
                'type' => 'simplegrid', 
                'num_cols' => 2,
                'headerValues' => array('Name', 'Status'),
                'cellClasses' => array('', ''),
                'noData' => 'No notifications',
                'changeTxt' => 'Edit Notifications',
                'changeApp' => array('app'=>'ciniki.wineproduction.notifications', 'args'=>array('customer_id'=>$args['customer_id'], 'source'=>'\'\'')),
                'cellValues' => array(
                    '0' => "d.label",
                    '1' => "d.status_text",
                    ),
                'rowClass' => "((d.flags&0x10) == 0x10 ? 'statusred' : ((d.flags&0x01) == 0x01 ? 'statusgreen' : ''))",
                'data' => $notifications,
                ),
            'queue' => array(
                'label' => 'Upcoming Notifications',
                'type' => 'simplegrid', 
                'num_cols' => 2,
                'headerValues' => array('Date', 'Notification'),
                'cellClasses' => array('multiline', 'multiline', 'multiline'),
                'noData' => 'No queued notifications',
                'cellValues' => array(
                    '0' => "M.multiline(d.scheduled_date, d.scheduled_time);",
                    '1' => "M.multiline(d.name, d.subject);",
                    '2' => "M.multiline(d.product_name, d.order_date);",
                    ),
                'data' => $queue,
                ),
            ),
        );
    return $rsp;
}
?>
