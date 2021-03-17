<?php
//
// Description
// -----------
// This method will return the list of Wine Production Orders for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Wine Production Order for.
//
// Returns
// -------
//
function ciniki_wineproduction_production($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'view'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'menu', 'name'=>'View'),
        'action'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Action'),
        'order_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Order'),
        'batch_code'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Batch Code'),
        'tsg_reading'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Transfer SG Reading'),
        'sg_reading'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Rack SG Reading'),
        'schedulestatus'=>array('required'=>'no', 'default'=>'', 'name'=>'Schedule Status'),
        'scheduledate'=>array('required'=>'no', 'default'=>'', 'name'=>'Schedule Date'),
        'workdate'=>array('required'=>'no', 'default'=>'', 'name'=>'Rack Date'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.production');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load the tenant settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];

    //
    // Load maps
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'maps');
    $rc = ciniki_wineproduction_maps($ciniki);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $maps = $rc['maps'];

    //
    // Load the tenant settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_wineproduction_settings', 'tnid', $args['tnid'], 'ciniki.wineproduction', 'settings', '');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.267', 'msg'=>'Unable to load settings', 'err'=>$rc['err']));
    }
    $settings = isset($rc['settings']) ? $rc['settings'] : array();

    $order_flags = array();
    $order_backgrounds = array();
    $bottling_backgrounds = array();
    for($i = 1; $i <= 16;$i++) {
        if( isset($settings["order.flags.$i.name"]) && $settings["order.flags.$i.name"] != '' ) {
            $order_flags[pow(2, $i-1)] = $settings["order.flags.$i.name"];
            $order_backgrounds[pow(2, $i-1)] = $settings["order.flags.$i.colour"];
        }
        if( isset($settings["bottling.status.$i.name"]) && $settings["bottling.status.$i.name"] != '' ) {
            $bottling_backgrounds[pow(2, $i-1)] = $settings["bottling.status.$i.colour"];
        }
    }

    //
    // Load the date format strings for the user
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'mysql');
    
    //
    // Setup todays date for finding orders
    //
    $dt = new DateTime('now', new DateTimezone($intl_timezone));
    $todays_date = $dt->format('Y-m-d');
    $dt->add(new DateInterval('P4D'));
    $today_plus_four = $dt->format('Y-m-d');
    $dt->add(new DateInterval('P10D'));
    $today_plus_fourteen = $dt->format('Y-m-d');

    if( isset($args['scheduledate']) && $args['scheduledate'] == 'today' ) {
        $args['scheduledate'] = $todays_date;
    }

    //
    // Check if an action is to be taken
    //
    if( isset($args['action']) && $args['action'] != '' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'orderAction');
        $rc = ciniki_wineproduction_orderAction($ciniki, $args['tnid'], $args);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
    }

    //
    // Setup the basic menu items
    //
    $rsp = array('stat'=>'ok',
        'today' => array(
            'starting' => array('id' => 'starting', 'label' => 'Starting', 'count'=>0),
            'tsgreadings' => array('id' => 'tsgreadings', 'label' => 'Transfer SG Readings', 'count'=>0),
            'transferring' => array('id' => 'transferring', 'label' => 'Transfers', 'count'=>0),
            'sgreadings' => array('id' => 'sgreadings', 'label' => 'Racking SG Readings', 'count'=>0),
            'racking' => array('id' => 'racking', 'label' => 'Racking', 'count'=>0),
            'filtering' => array('id' => 'filtering', 'label' => 'Filtering', 'count'=>0),
            ),
        'statuses' => array(
            'ordered' => array('id' => 'ordered', 'status'=>10, 'label' => 'Ordered', 'count'=>0),
            'started' => array('id' => 'started', 'status'=>20, 'label' => 'Started', 'count'=>0),
            'tsgread' => array('id' => 'tsgread', 'status'=>22, 'label' => 'Transfer SG Ready', 'count'=>0),
            'transferred' => array('id' => 'transferred', 'status'=>23, 'label' => 'Transferred', 'count'=>0),
            'sgread' => array('id' => 'sgread', 'status'=>25, 'label' => 'SG Ready', 'count'=>0),
            'racked' => array('id' => 'racked', 'status'=>30, 'label' => 'Racked', 'count'=>0),
            'filtered' => array('id' => 'filtered', 'status'=>40, 'label' => 'Filtered', 'count'=>0),
            ),
        'reports' => array(
            'schedule' => array('id' => 'schedule', 'label' => 'Production Schedule'),
            'completed' => array('id' => 'completed', 'label' => 'Work Completed', 'count'=>0),
            'late' => array('id' => 'late', 'label' => 'Late Wines', 'count'=>0),
            'ctb' => array('id' => 'ctb', 'label' => 'Call to Book', 'count'=>0),
            'cellarnights' => array('id' => 'cellarnights', 'label' => 'Cellar Nights'),
            'shared' => array('id' => 'shared', 'label' => 'Shared'),
            'export' => array('id' => 'export', 'label' => 'Export Orders'),
            ),
        );

    //
    // Get the number of orders for action today
    //
    $strsql = "SELECT (orders.status|(products.flags&0x80)) AS status, COUNT(orders.status) AS num_orders "
        . "FROM ciniki_wineproductions AS orders "
        . "INNER JOIN ciniki_wineproduction_products AS products ON ("
            . "orders.product_id = products.id "
            . "AND products.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") "
        . "WHERE orders.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ("
            // Transfer SG Readings (status becomes 148)
            . "(orders.status = 20 AND (products.flags&0x80) = 0x80 AND orders.transferring_date <= '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
            // Transfer Ready (status is 22)
            . "OR (orders.status = 22) "
            // Transferred (status is 23)
            . "OR (orders.status = 23 AND orders.racking_date <= '" . ciniki_core_dbQuote($ciniki, $today_plus_four) . "') "
            // SG Readings (status is 20)
            . "OR (orders.status = 20 AND (products.flags&0x80) = 0 AND orders.racking_date <= '" . ciniki_core_dbQuote($ciniki, $today_plus_four) . "') "
            // Racking
            . "OR (orders.status = 25) "
            // Filtering
            . "OR (orders.status = 30 AND orders.filtering_date <= '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
            . ") "
        . "GROUP BY status "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbCount');
    $rc = ciniki_core_dbCount($ciniki, $strsql, 'ciniki.wineproduction', 'statuses');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.11', 'msg'=>'Unable to load get the number of items', 'err'=>$rc['err']));
    }
    $today = isset($rc['statuses']) ? $rc['statuses'] : array();

    if( isset($today['148']) ) {
        $rsp['today']['tsgreadings']['count'] += $today['148'];
    }
    if( isset($today['20']) ) {
        $rsp['today']['sgreadings']['count'] += $today['20'];
    }
    if( isset($today['22']) ) {
        $rsp['today']['transferring']['count'] += $today['22'];
    }
    if( isset($today['150']) ) {
        $rsp['today']['transferring']['count'] += $today['150'];
    }
    if( isset($today['23']) ) {
        $rsp['today']['sgreadings']['count'] += $today['23'];
    }
    if( isset($today['151']) ) {
        $rsp['today']['sgreadings']['count'] += $today['151'];
    }
    if( isset($today['153']) ) {
        $rsp['today']['racking']['count'] += $today['153'];
    }
    if( isset($today['25']) ) {
        $rsp['today']['racking']['count'] += $today['25'];
    }
    if( isset($today['30']) ) {
        $rsp['today']['filtering']['count'] += $today['30'];
    }
    if( isset($today['158']) ) {
        $rsp['today']['filtering']['count'] += $today['158'];
    }

    //
    // Get the number of orders in each status
    //
    $strsql = "SELECT status, COUNT(*) AS num_orders "
        . "FROM ciniki_wineproductions "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND status < 60 "
        . "GROUP BY status "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbCount');
    $rc = ciniki_core_dbCount($ciniki, $strsql, 'ciniki.wineproduction', 'statuses');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.11', 'msg'=>'Unable to load get the number of items', 'err'=>$rc['err']));
    }
    $statuses = isset($rc['statuses']) ? $rc['statuses'] : array();
    foreach($rsp['statuses'] as $id => $item) {
        if( isset($statuses[$item['status']]) ) {
            $rsp['statuses'][$id]['count'] = $statuses[$item['status']];
        }
    }
    if( isset($statuses['10']) ) {
        $rsp['today']['starting']['count'] = $statuses['10'];
    }

    //
    // Get the work done for the current day
    //
    $strsql = "SELECT COUNT(status) AS count "
        . "FROM ciniki_wineproductions "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ("
            . "(status = 10 && order_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
            . "OR (status = 20 && start_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
            . "OR (status = 25 && start_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
            . "OR (status = 30 && rack_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') " 
            . "OR (status = 40 && filter_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
            . "OR (status = 60 && bottle_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
            . ") "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbSingleCount');
    $rc = ciniki_core_dbSingleCount($ciniki, $strsql, 'ciniki.wineproduction', 'num');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.78', 'msg'=>'Unable to load get the number of items', 'err'=>$rc['err']));
    }
    if( isset($rc['num']) ) {
        $rsp['reports']['completed']['count'] = $rc['num'];
    }

    //
    // Get Late Wines stats
    //
    $strsql = "SELECT COUNT(status) AS count "
        . "FROM ciniki_wineproductions "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND status < 40 "
        . "AND bottling_date > 0 AND TIME(bottling_date) <> '00:00:00' AND (bottling_date < filtering_date "
            . "OR (filtering_date = 0 AND bottling_date < DATE_ADD(racking_date, INTERVAL (kit_length-2) WEEK)) "
            . "OR (racking_date = 0 AND bottling_date < DATE_ADD(start_date, INTERVAL kit_length WEEK)) "
            . "OR bottling_date < start_date) "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbSingleCount');
    $rc = ciniki_core_dbSingleCount($ciniki, $strsql, 'ciniki.wineproduction', 'num');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.78', 'msg'=>'Unable to load get the number of items', 'err'=>$rc['err']));
    }
    if( isset($rc['num']) ) {
        $rsp['reports']['late']['count'] = $rc['num'];
    }

    //
    // Get Call to Book Stats
    //
    $strsql = "SELECT COUNT(status) AS count "
        . "FROM ciniki_wineproductions "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND status < 60 "
        . "AND (TIME(bottling_date) = '00:00:00' OR bottling_date = '0000-00-00 00:00:00') "
        . "AND (filtering_date > 0 AND filtering_date < NOW()) "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbSingleCount');
    $rc = ciniki_core_dbSingleCount($ciniki, $strsql, 'ciniki.wineproduction', 'num');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.78', 'msg'=>'Unable to load get the number of items', 'err'=>$rc['err']));
    }
    if( isset($rc['num']) ) {
        $rsp['reports']['ctb']['count'] = $rc['num'];
    }
    
    //
    // Get Shared stats
    //
    $strsql = "SELECT COUNT(status) AS count "
        . "FROM ciniki_wineproductions "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND status < 60 "
        . "AND (flags&0x01) = 0x01 "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbSingleCount');
    $rc = ciniki_core_dbSingleCount($ciniki, $strsql, 'ciniki.wineproduction', 'num');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.78', 'msg'=>'Unable to load get the number of items', 'err'=>$rc['err']));
    }
    if( isset($rc['num']) ) {
        $rsp['reports']['shared']['count'] = $rc['num'];
    }

    //
    // Remove any transfers when not enabled
    //
    if( !ciniki_core_checkModuleFlags($ciniki, 'ciniki.wineproduction', 0x0800) ) {
        unset($rsp['today']['tsgreadings']);
        unset($rsp['today']['transferring']);
        unset($rsp['statuses']['transferred']);
    }
    //
    // Remove any shared if not enabled
    //
    if( !ciniki_core_checkModuleFlags($ciniki, 'ciniki.wineproduction', 0x1000) ) {
        unset($rsp['reports']['cellarnights']);
        unset($rsp['reports']['shared']);
    }

    //
    // If just menu needed, no list of wine required, return now
    //
    if( $args['view'] == 'menu' ) {
        return $rsp;
    }

    //
    // Check if we should get the schedule
    //
    if( $args['view'] == 'schedule' ) {
        //
        // Get the transfer stats
        //
        $strsql = "SELECT IF(transferring_date < '$todays_date', 'past', "
            . "IF(transferring_date >= '$today_plus_fourteen', 'future', orders.transferring_date)) AS tdate, "
            . "COUNT(orders.id) AS num "
            . "FROM ciniki_wineproductions AS orders "
            . "INNER JOIN ciniki_wineproduction_products AS products ON ("
                . "orders.product_id = products.id "
                . "AND products.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                . ") "
            // transfer step required, or transfer sg ready
            . "WHERE ((orders.status = 20 AND (products.flags&0x80) = 0x80) OR orders.status = 22) "
            . "AND orders.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "GROUP BY tdate "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbCount');
        $rc = ciniki_core_dbCount($ciniki, $strsql, 'ciniki.wineproduction', 'transfers');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.272', 'msg'=>'Unable to load get the number of items', 'err'=>$rc['err']));
        }
        $transfers = isset($rc['transfers']) ? $rc['transfers'] : array();

        //
        // Get the racking stats
        //
        $strsql = "SELECT IF(racking_date < '$todays_date', 'past', "
            . "IF(racking_date >= '$today_plus_fourteen', 'future', orders.racking_date)) AS rdate, "
            . "COUNT(orders.id) AS num "
            . "FROM ciniki_wineproductions AS orders "
            . "INNER JOIN ciniki_wineproduction_products AS products ON ("
                . "orders.product_id = products.id "
                . "AND products.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                . ") "
            // No transfer, or transferred, or sgread
            . "WHERE ((orders.status = 20 AND (products.flags&0x80) = 0) OR orders.status = 23 OR orders.status = 25) "
            . "AND orders.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "GROUP BY rdate "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbCount');
        $rc = ciniki_core_dbCount($ciniki, $strsql, 'ciniki.wineproduction', 'racking');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.272', 'msg'=>'Unable to load get the number of items', 'err'=>$rc['err']));
        }
        $racking = isset($rc['racking']) ? $rc['racking'] : array();

        //
        // Get the filtering stats
        //
        $strsql = "SELECT IF(filtering_date < '$todays_date', 'past', "
            . "IF(filtering_date >= '$today_plus_fourteen', 'future', orders.filtering_date)) AS fdate, "
            . "COUNT(orders.id) AS num "
            . "FROM ciniki_wineproductions AS orders "
            . "WHERE orders.status = 30 "
            . "AND orders.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "GROUP BY fdate "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbCount');
        $rc = ciniki_core_dbCount($ciniki, $strsql, 'ciniki.wineproduction', 'filtering');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.272', 'msg'=>'Unable to load get the number of items', 'err'=>$rc['err']));
        }
        $filtering = isset($rc['filtering']) ? $rc['filtering'] : array();

        //
        // Get the bottling stats
        //
        $strsql = "SELECT IF(bottling_date < '$todays_date', 'past', "
            . "IF(bottling_date >= '$today_plus_fourteen', 'future', DATE(orders.bottling_date))) AS bdate, "
            . "COUNT(orders.id) AS num "
            . "FROM ciniki_wineproductions AS orders "
            . "WHERE orders.status < 60 "
            . "AND orders.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "GROUP BY bdate "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbCount');
        $rc = ciniki_core_dbCount($ciniki, $strsql, 'ciniki.wineproduction', 'bottling');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.272', 'msg'=>'Unable to load get the number of items', 'err'=>$rc['err']));
        }
        $bottling = isset($rc['bottling']) ? $rc['bottling'] : array();

        //
        // Setup the schedule
        //
        $rsp['schedule'] = array(
            'transferring' => array(
                array('label'=>'Transfers'),
                ),
            'racking' => array(
                array('label'=>'Racking'),
                ),
            'filtering' => array(
                array('label'=>'Filtering'),
                ),
            'bottling' => array(
                array('label'=>'Bottling'),
                ),
            'dates' => array(
                array('label'=>''),
                ),
            );
        $dt = new DateTime('now', new DateTimezone($intl_timezone));
        $rsp['schedule']['dates'][0]['label'] = $dt->format('M');
        for($i=1;$i<=16;$i++) {
            $cur_date = $dt->format('Y-m-d');
            if( $i == 1 ) {
                $rsp['schedule']['transferring'][$i] = array('label' => (isset($transfers['past']) ? $transfers['past'] : ''),
                    'date' => 'past',
                    );
                $rsp['schedule']['racking'][$i] = array('label' => (isset($racking['past']) ? $racking['past'] : ''),
                    'date' => 'past',
                    );
                $rsp['schedule']['filtering'][$i] = array('label' => (isset($filtering['past']) ? $filtering['past'] : ''),
                    'date' => 'past',
                    );
                $rsp['schedule']['bottling'][$i] = array('label' => (isset($bottling['past']) ? $bottling['past'] : ''),
                    'date' => 'past',
                    );
                $rsp['schedule']['dates'][$i] = array('label' => '...', 'date'=>'past');
            } elseif( $i == 16 ) {
                $rsp['schedule']['transferring'][$i] = array('label' => (isset($transfers['future']) ? $transfers['future'] : ''),
                    'date' => 'future',
                    );
                $rsp['schedule']['racking'][$i] = array('label' => (isset($racking['future']) ? $racking['future'] : ''),
                    'date' => 'future',
                    );
                $rsp['schedule']['filtering'][$i] = array('label' => (isset($filtering['future']) ? $filtering['future'] : ''),
                    'date' => 'future',
                    );
                $rsp['schedule']['bottling'][$i] = array('label' => (isset($bottling['future']) ? $bottling['future'] : ''),
                    'date' => 'future',
                    );
                $rsp['schedule']['dates'][$i] = array('label' => '...', 'date'=>'future');
            } else {
                $rsp['schedule']['transferring'][$i] = array('label' => (isset($transfers[$cur_date]) ? $transfers[$cur_date] : ''),
                    'date' => ($i == 2 ? 'today' : $dt->format('Y-m-d')),
                    );
                $rsp['schedule']['racking'][$i] = array('label' => (isset($racking[$cur_date]) ? $racking[$cur_date] : ''),
                    'date' => ($i == 2 ? 'today' : $dt->format('Y-m-d')),
                    );
                $rsp['schedule']['filtering'][$i] = array('label' => (isset($filtering[$cur_date]) ? $filtering[$cur_date] : ''),
                    'date' => ($i == 2 ? 'today' : $dt->format('Y-m-d')),
                    );
                $rsp['schedule']['bottling'][$i] = array('label' => (isset($bottling[$cur_date]) ? $bottling[$cur_date] : ''),
                    'date' => ($i == 2 ? 'today' : $dt->format('Y-m-d')),
                    );
                $rsp['schedule']['dates'][$i] = array('label'=>'<span class="subtext">' . $dt->format('D') . '</span>'
                    . '<span class="maintext">' . $dt->format('j') . '</span>', 'date' => ($i == 2 ? 'today' : $dt->format('Y-m-d')));
                $dt->add(new DateInterval('P1D'));
            }
        }

        //
        // If nothing clicked in the schedule, then return with no listing
        //
        if( !isset($args['schedulestatus']) || $args['schedulestatus'] == '' ) {
            return $rsp;
        }
    }

    //
    // Pull the requested wines
    //
    $strsql = "SELECT orders.id, "
        . "orders.parent_id, "
        . "orders.customer_id, "
        . "orders.invoice_id, "
        . "orders.invoice_number, "
        . "orders.batch_letter, "
        . "orders.product_id, "
        . "orders.wine_type, "
        . "orders.kit_length, "
        . "orders.status, "
        . "orders.status AS status_text, "
        . "orders.rack_colour, "
        . "orders.filter_colour, "
        . "orders.location, "
        . "orders.flags, "
        . "orders.order_flags, "
        . "orders.order_flags AS order_options, "
        . "orders.order_flags AS bgcolour, "
        . "DATE_FORMAT(orders.order_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS order_date, "
        . "DATE_FORMAT(orders.start_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS start_date, "
        . "orders.tsg_reading, "
        . "DATE_FORMAT(orders.transferring_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS transferring_date, "
        . "DATE_FORMAT(orders.transfer_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS transfer_date, "
        . "orders.sg_reading, "
        . "DATE_FORMAT(orders.racking_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS racking_date, "
        . "DATE_FORMAT(orders.rack_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS rack_date, "
        . "DATE_FORMAT(orders.filtering_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS filtering_date, "
        . "DATE_FORMAT(orders.filter_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS filter_date, "
        . "orders.bottling_flags, "
        . "orders.bottling_nocolour_flags, "
        . "orders.bottling_duration, "
        . "DATE_FORMAT(orders.bottling_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS bottling_date, "
        . "orders.bottling_date AS bottling_date_sort, "
        . "orders.bottling_status, "
        . "orders.bottling_status AS bottling_bgcolour, "
        . "orders.bottling_status AS bottling_status_text, "
        . "DATE_FORMAT(orders.bottle_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS bottle_date, "
        . "orders.batch_code, "
        . "orders.notes, "
        . "CONCAT_WS('-', UNIX_TIMESTAMP(orders.bottling_date), orders.customer_id) AS appointment_id, "
        . "IFNULL(products.name, 'Unknown?') AS wine_name, "
        . "IFNULL(customers.display_name, 'Unknown?') AS customer_name "
        . "FROM ciniki_wineproductions AS orders "
        . "LEFT JOIN ciniki_wineproduction_products AS products ON ("
            . "orders.product_id = products.id "
            . "AND products.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") "
        . "LEFT JOIN ciniki_customers AS customers ON ("
            . "orders.customer_id = customers.id "
            . "AND customers.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") "
        . "WHERE orders.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    if( $args['view'] == 'ordered' ) {
        $strsql .= "AND orders.status = 10 ";
    }
    elseif( $args['view'] == 'starting' ) {
        $strsql .= "AND ("
            . "orders.status = 10 "
            . "OR (orders.status = 20 AND orders.start_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
            . ") ";
    }
    elseif( $args['view'] == 'started' ) {
        $strsql .= "AND orders.status = 20 ";
    }
    elseif( $args['view'] == 'tsgreadings' ) {
        $strsql .= "AND ("
            . "(orders.status = 20 AND (products.flags&0x80) = 0x80 AND orders.transferring_date <= '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') OR orders.status = 22 "
            . ")";
    }
    elseif( $args['view'] == 'tsgread' ) {
        $strsql .= "AND orders.status = 22 ";
    }
    elseif( $args['view'] == 'transferring' ) {
        $strsql .= "AND orders.status = 22 ";
    }
    elseif( $args['view'] == 'transferred' ) {
        $strsql .= "AND orders.status = 23 ";
    }
    elseif( $args['view'] == 'sgreadings' ) {
        $strsql .= "AND ("
            . "((orders.status = 20 AND (products.flags&0x80) = 0) OR orders.status = 23 OR orders.status = 25) "
            . "AND orders.racking_date <= '" . ciniki_core_dbQuote($ciniki, $today_plus_four) . "' "
            . ")";
    }
    elseif( $args['view'] == 'sgread' ) {
        $strsql .= "AND orders.status = 25 ";
    }
    elseif( $args['view'] == 'racking' ) {
        $strsql .= "AND ("
            . "orders.status = 25 "
            . "OR (orders.status = 30 AND orders.rack_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
            . ") ";
    }
    elseif( $args['view'] == 'racked' ) {
        $strsql .= "AND orders.status = 30 ";
    }
    elseif( $args['view'] == 'filtering' ) {
        $strsql .= "AND ("
            . "(orders.status = 30 AND orders.filtering_date <= '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
            . "OR (orders.status = 40 AND orders.filter_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
            . ") ";
    }
    elseif( $args['view'] == 'filtered' ) {
        $strsql .= "AND orders.status = 40 ";
    }
    elseif( $args['view'] == 'schedule' ) {
        if( isset($args['schedulestatus']) && $args['schedulestatus'] != '' 
            && isset($args['scheduledate']) && $args['scheduledate'] != '' 
            ) {
            $strsql_date = "";
            if( $args['scheduledate'] == 'past' ) {
                $strsql_date = " < '" . ciniki_core_dbQuote($ciniki, $todays_date) . "' ";
            } elseif( $args['scheduledate'] == 'future' ) {
                $strsql_date = " >= '" . ciniki_core_dbQuote($ciniki, $today_plus_fourteen) . "' ";
            } else {
                $strsql_date = " = '" . ciniki_core_dbQuote($ciniki, $args['scheduledate']) . "' ";
            }
            if( $args['schedulestatus'] == 'transferring' ) {
                $strsql .= "AND ((orders.status = 20 AND (products.flags&0x80) = 0x80) OR orders.status = 22 OR orders.status = 23 ) "
                    . "AND orders.transferring_date" . $strsql_date;
            } elseif( $args['schedulestatus'] == 'racking' ) {
                $strsql .= "AND (((orders.status = 20 AND (products.flags&0x80) = 0) OR orders.status = 23 OR orders.status = 25) "
                    . "AND orders.racking_date" . $strsql_date
                    . ") OR (orders.status = 30 and rack_date" . $strsql_date . ")";
            } elseif( $args['schedulestatus'] == 'filtering' ) {
                $strsql .= "AND (orders.status = 30 OR orders.status = 40) ";
                $strsql .= "AND DATE(orders.filtering_date)" . $strsql_date;
            } elseif( $args['schedulestatus'] == 'bottling' ) {
                $strsql .= "AND orders.status < 60 "
                    . "AND DATE(orders.bottling_date)" . $strsql_date;
            }
        } else {
            // No list requested, shouldn't reach this spot in code but return just in case
            return $rsp;
        }
    }
    elseif( $args['view'] == 'completed' ) {
        if( isset($args['workdate']) && $args['workdate'] == 'today' ) {
            $strsql .= "AND ("
                . "(orders.order_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
                . "OR (orders.start_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
                . "OR (orders.transfer_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') " 
                . "OR (orders.rack_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') " 
                . "OR (orders.filter_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
                . "OR (orders.bottle_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') "
                . ") ";
        } else if( isset($args['workdate']) && $args['workdate'] != '' ) {
            $strsql .= "AND ("
                . "(orders.order_date = '" . ciniki_core_dbQuote($ciniki, $args['workdate']) . "') "
                . "OR (orders.start_date = '" . ciniki_core_dbQuote($ciniki, $args['workdate']) . "') "
                . "OR (orders.transfer_date = '" . ciniki_core_dbQuote($ciniki, $args['workdate']) . "') " 
                . "OR (orders.rack_date = '" . ciniki_core_dbQuote($ciniki, $args['workdate']) . "') " 
                . "OR (orders.filter_date = '" . ciniki_core_dbQuote($ciniki, $args['workdate']) . "') "
                . "OR (orders.bottle_date = '" . ciniki_core_dbQuote($ciniki, $args['workdate']) . "') "
                . ") ";
        }
    }
    elseif( $args['view'] == 'late' ) {
        $strsql .= "AND orders.status < 40 "
            . "AND orders.bottling_date > 0 AND TIME(orders.bottling_date) <> '00:00:00' "
                . "AND ("
                    . "orders.bottling_date < orders.filtering_date "
                    . "OR (orders.filtering_date = 0 AND orders.bottling_date < DATE_ADD(orders.racking_date, INTERVAL (orders.kit_length-2) WEEK)) "
                    . "OR (orders.racking_date = 0 AND orders.bottling_date < DATE_ADD(orders.start_date, INTERVAL orders.kit_length WEEK)) "
                    . "OR orders.bottling_date < orders.start_date "
                . ") "
            . "";
    }
    elseif( $args['view'] == 'ctb' ) {
        $strsql .= "AND orders.status < 60 "
            . "AND (TIME(orders.bottling_date) = '00:00:00' OR orders.bottling_date = '0000-00-00 00:00:00') "
            . "AND (orders.filtering_date > 0 AND orders.filtering_date < NOW()) "
            . "";

    }
    $strsql .= "ORDER BY status, invoice_number ";

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'status', 'fname'=>'status', 'fields'=>array('status')),
        array('container'=>'orders', 'fname'=>'id', 
            'fields'=>array('id', 'parent_id', 'customer_id', 'customer_name', 
                'invoice_id', 'invoice_number', 'batch_letter', 
                'product_id', 'wine_name', 'wine_type', 'kit_length', 'status', 'status_text',
                'rack_colour', 'filter_colour', 'location', 
                'flags', 'order_flags', 'order_options', 'bgcolour', 'order_date', 'start_date', 
                'tsg_reading', 'transferring_date', 'transfer_date', 'sg_reading', 
                'racking_date', 'rack_date', 'filtering_date', 'filter_date', 
                'bottling_flags', 'bottling_bgcolour', 'bottling_nocolour_flags', 
                'bottling_duration', 'bottling_date', 'bottling_date_sort',
                'bottling_status', 'bottling_status_text', 'bottle_date', 'appointment_id', 'batch_code', 'notes',
                ),
            'maps'=>array(  
                'status_text'=>$maps['wineproduction']['status'],
                'bottling_status_text'=>$maps['wineproduction']['status'],
                ),
            'flags'=>array(
                'order_options' => $order_flags,
                'bgcolour' => $order_backgrounds,
                'bottling_bgcolour' => $bottling_backgrounds,
                ),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.76', 'msg'=>'Unable to get the list of orders', 'err'=>$rc['err']));
    }
    $statuses = isset($rc['status']) ? $rc['status'] : array();

    //
    // Setup empty arrays so the sections show in the UI
    //
    if( $args['view'] == 'completed' ) {    
        $rsp['ordered'] = array();
        $rsp['started'] = array();
        if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.wineproduction', 0x0800) ) {
            $rsp['transferred'] = array();
        }
        $rsp['racked'] = array();
        $rsp['filtered'] = array();
        $rsp['bottled'] = array();
    }
    elseif( $args['view'] == 'schedule' && isset($args['schedulestatus']) ) {
        if( $args['schedulestatus'] == 'transferring' ) {
            $rsp['tsgreadings'] = array();
            $rsp['transferring'] = array();
            $rsp['transferred'] = array();
        } elseif( $args['schedulestatus'] == 'racking' ) {
            $rsp['sgreadings'] = array();
            $rsp['racking'] = array();
            $rsp['racked'] = array();
        } elseif( $args['schedulestatus'] == 'filtering' ) {
            $rsp['filtering'] = array();
            $rsp['filtered'] = array();
        } elseif( $args['schedulestatus'] == 'bottling' ) {
            $rsp['bottling'] = array();
        }
    }
    elseif( in_array($args['view'], ['ordered', 'completed']) ) {  
        $rsp['ordered'] = array();
    } 
    elseif( $args['view'] == 'starting' ) {
        $rsp['starting'] = array();
    }
    elseif( in_array($args['view'], ['starting', 'started', 'completed']) ) {  
        $rsp['started'] = array();
    }
    elseif( $args['view'] == 'tsgread' ) {
        $rsp['transferring'] = array();
    }
    elseif( $args['view'] == 'tsgreadings' ) {
        $rsp['tsgreadings'] = array();
        $rsp['transferring'] = array();
    }
    elseif( $args['view'] == 'sgreadings' ) {
        $rsp['sgreadings'] = array();
        $rsp['racking'] = array();
    }
    elseif( $args['view'] == 'sgread' ) {
        $rsp['racking'] = array();
    }
    elseif( $args['view'] == 'transferring' ) {
        $rsp['transferring'] = array();
    } 
    elseif( $args['view'] == 'transferred' ) {
        $rsp['transferred'] = array();
    } 
    elseif( $args['view'] == 'racking' ) {
        $rsp['racking'] = array();
    }
    elseif( in_array($args['view'], ['racking', 'racked', 'completed']) ) {  
        $rsp['racked'] = array();
    } 
    elseif( $args['view'] == 'filtering' ) {
        $rsp['filtering'] = array();
        $rsp['filtered'] = array();
    }
    elseif( in_array($args['view'], ['filtering', 'filtered', 'completed']) ) {  
        $rsp['filtered'] = array();
    }
    elseif( $args['view'] == 'filtered' ) {
        $rsp['filtered'] = array();
    }
    

    foreach($statuses as $s) {
        if( in_array($args['view'], ['ordered', 'completed']) && $s['status'] == '10' ) {  
            $rsp['ordered'] = $s['orders'];
        } 
        elseif( $args['view'] == 'starting' && $s['status'] == '10' ) {
            $rsp['starting'] = $s['orders'];
        }
        elseif( in_array($args['view'], ['starting', 'started', 'completed']) && $s['status'] == '20' ) {  
            $rsp['started'] = $s['orders'];
        }
        elseif( $args['view'] == 'tsgreadings' && $s['status'] == '20' ) {
            $rsp['tsgreadings'] = $s['orders'];
        }
        elseif( $args['view'] == 'sgreadings' && ($s['status'] == '20' || $s['status'] == '23') ) {
            $rsp['sgreadings'] = array_merge((isset($rsp['sgreadings']) ? $rsp['sgreadings'] : array()), $s['orders']);
        }
        elseif( in_array($args['view'], ['tsgreadings', 'tsgread', 'transferring']) && $s['status'] == '22' ) {  
            $rsp['transferring'] = $s['orders'];
        } 
        elseif( $args['view'] == 'transferred' && $s['status'] == '23' ) {
            $rsp['transferred'] = $s['orders'];
        } 
        elseif( in_array($args['view'], ['sgreadings', 'sgread', 'racking']) && $s['status'] == '25' ) {  
            $rsp['racking'] = $s['orders'];
        }
        elseif( in_array($args['view'], ['racking', 'racked', 'completed']) && $s['status'] == '30' ) {  
            $rsp['racked'] = $s['orders'];
        } 
        elseif( $args['view'] == 'filtering' && $s['status'] == '30' ) {
            $rsp['filtering'] = $s['orders'];
        }
        elseif( in_array($args['view'], ['filtering', 'filtered', 'completed']) && $s['status'] == '40' ) {  
            $rsp['filtered'] = $s['orders'];
        }
        elseif( $args['view'] == 'filtered' ) {
            $rsp['filtered'] = $s['orders'];
        }
        elseif( $args['view'] == 'completed' && $s['status'] == '60' ) {    
            $rsp['bottled'] = $s['orders'];
        }
        elseif( $args['view'] == 'schedule' && isset($args['schedulestatus']) ) {
            if( $args['schedulestatus'] == 'transferring' && $s['status'] == '20' ) {
                $rsp['tsgreadings'] = array_merge((isset($rsp['tsgreadings']) ? $rsp['tsgreadings'] : array()), $s['orders']);
            } 
            elseif( $args['schedulestatus'] == 'transferring' && $s['status'] == '22' ) {
                $rsp['transferring'] = array_merge((isset($rsp['transferring']) ? $rsp['transferring'] : array()), $s['orders']);
            } 
            elseif( $args['schedulestatus'] == 'transferring' && $s['status'] == '23' ) {
                $rsp['transferred'] = array_merge((isset($rsp['transferred']) ? $rsp['transferred'] : array()), $s['orders']);
            } 
            elseif( $args['schedulestatus'] == 'racking' && $s['status'] == '20' ) {
                $rsp['sgreadings'] = array_merge((isset($rsp['sgreadings']) ? $rsp['sgreadings'] : array()), $s['orders']);
            } 
            elseif( $args['schedulestatus'] == 'racking' && $s['status'] == '23' ) {
                $rsp['sgreadings'] = array_merge((isset($rsp['sgreadings']) ? $rsp['sgreadings'] : array()), $s['orders']);
            } 
            elseif( $args['schedulestatus'] == 'racking' && $s['status'] == '25' ) {
                $rsp['racking'] = $s['orders'];
            } 
            elseif( $args['schedulestatus'] == 'racking' && $s['status'] == '30' ) {
                $rsp['racked'] = $s['orders'];
            } 
            elseif( $args['schedulestatus'] == 'filtering' && $s['status'] == '30' ) {
                $rsp['filtering'] = $s['orders'];
            } 
            elseif( $args['schedulestatus'] == 'filtering' && $s['status'] == '40' ) {
                $rsp['filtered'] = $s['orders'];
            } 
            elseif( $args['schedulestatus'] == 'bottling' ) {
                $rsp['bottling'] = array_merge((isset($rsp['bottling']) ? $rsp['bottling'] : array()), $s['orders']);
            }
        }
        elseif( $args['view'] == 'late' ) {
            if( !isset($rsp['late']) ) {
                $rsp['late'] = array();
            }
            foreach($s['orders'] as $order) {
                $rsp['late'][] = $order;
            }
            usort($rsp['late'], function($a, $b) {
                if( $a['bottling_date_sort'] == $b['bottling_date_sort'] ) {
                    return 0;
                }
                return $a['bottling_date_sort'] < $b['bottling_date_sort'] ? -1 : 1;
            });
        }
        elseif( $args['view'] == 'ctb' ) {
            if( !isset($rsp['ctb']) ) {
                $rsp['ctb'] = array();
            }
            foreach($s['orders'] as $order) {
                $rsp['ctb'][] = $order;
            }
        }
    }

    return $rsp;
}
?>
