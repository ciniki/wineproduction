<?php
//
// Description
// -----------
// This function handles a trigger from an order event to determine if a notification(s) should be sent.
//
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// trigger:         Valid triggers
//
//                      entered - Order entered, check for new customer
//                      started - Order started, check for ntypes 20, 25
//                      racked - Order racked, check for ntypes 50, 55
//                      filtered - Order filtered, check for ntypes 60, 65, 70
//                      bottlingdate - Bottling date set, check for ntypes 80
//                      bottled - Wine was bottled, setup ntypes 100, 120, 130, 150
//
// 
// Returns
// ---------
// 
function ciniki_wineproduction_notificationTrigger(&$ciniki, $tnid, $trigger, $order_id) {

    //
    // Load the tenant settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $tnid);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];

    //
    // Setup todays date to make sure we don't queue any messages before now
    //
    $today_dt = new DateTime('now', new DateTimezone('UTC'));

    //
    // Load the order
    //
    $strsql = "SELECT orders.id, "
        . "orders.customer_id, "
        . "orders.invoice_number, "
        . "orders.order_date, "
        . "products.id as product_id, "
        . "products.name AS wine_name, "
        . "orders.wine_type, "
        . "orders.kit_length, "
        . "orders.status, "
        . "orders.colour_tag, "
        . "orders.order_flags, "
        . "orders.order_date, "
        . "orders.start_date, "
        . "orders.rack_colour, "
        . "orders.rack_date, "
        . "orders.filter_colour, "
        . "orders.filter_date, "
        . "orders.bottling_date, "
        . "orders.bottle_date, "
        . "orders.bottling_flags, "
        . "orders.bottling_nocolour_flags, "
        . "orders.bottling_status, "
        . "orders.bottling_duration, "
        . "orders.notes, "
        . "orders.batch_code "
        . "FROM ciniki_wineproductions AS orders "
        . "LEFT JOIN ciniki_products AS products ON ("
            . "orders.product_id = products.id "
            . "AND products.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "WHERE orders.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND orders.id = '" . ciniki_core_dbQuote($ciniki, $order_id) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'order');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.54', 'msg'=>'Unable to load order', 'err'=>$rc['err']));
    }
    if( !isset($rc['order']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.55', 'msg'=>'Unable to find requested order'));
    }
    $order = $rc['order'];
   
    //
    // Load the notifications settings for the customer
    //
    $strsql = "SELECT ntype, flags "
        . "FROM ciniki_wineproduction_notification_customers "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND customer_id = '" . ciniki_core_dbQuote($ciniki, $order['customer_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList2');
    $rc = ciniki_core_dbQueryList2($ciniki, $strsql, 'ciniki.wineproduction', 'subscriptions');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.56', 'msg'=>'Unable to load the list of customer notification subscriptions', 'err'=>$rc['err']));
    }
    $subscriptions = isset($rc['subscriptions']) ? $rc['subscriptions'] : array();

    //
    // Load the notification queue for the order
    //
    $strsql = "SELECT queue.id, "
        . "queue.uuid, "
        . "queue.scheduled_dt, "
        . "queue.notification_id "
        . "FROM ciniki_wineproduction_notification_queue AS queue "
        . "WHERE queue.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND queue.customer_id = '" . ciniki_core_dbQuote($ciniki, $order['customer_id']) . "' "
        . "AND queue.order_id = '" . ciniki_core_dbQuote($ciniki, $order['id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'queue', 'fname'=>'notification_id', 
            'fields'=>array('id', 'uuid', 'scheduled_dt', 'notification_id')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.79', 'msg'=>'Unable to load queue', 'err'=>$rc['err']));
    }
    $order_queue = isset($rc['queue']) ? $rc['queue'] : array();

    //
    // Load the notification queue by type for other orders
    //
    $strsql = "SELECT queue.id, "
        . "queue.uuid, "
        . "queue.scheduled_dt, "
        . "queue.notification_id, "
        . "notifications.ntype "
        . "FROM ciniki_wineproduction_notification_queue AS queue "
        . "INNER JOIN ciniki_wineproduction_notifications as notifications ON ("
            . "queue.notification_id = notifications.id "
            . "AND notifications.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "WHERE queue.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND queue.customer_id = '" . ciniki_core_dbQuote($ciniki, $order['customer_id']) . "' "
        . "AND queue.order_id <> '" . ciniki_core_dbQuote($ciniki, $order['id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'ntypes', 'fname'=>'ntype', 'fields'=>array('ntype')),
        array('container'=>'queue', 'fname'=>'scheduled_dt', 'fields'=>array('ntype', 'scheduled_dt')),
        array('container'=>'notifications', 'fname'=>'notification_id', 
            'fields'=>array('id', 'uuid', 'ntype', 'scheduled_dt', 'notification_id')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.58', 'msg'=>'Unable to load queue', 'err'=>$rc['err']));
    }
    //
    // List of all queued items for customer on OTHER orders NOT this order
    // This is good to see if we've already got the same ntype queued on the same day, don't queue 2!
    // Don't want to 2 racked messages going out the same day.
    //
    $ntype_day_queue = isset($rc['queue']) ? $rc['queue'] : array();

    //
    // Load the notifications for the trigger
    //
    $strsql = "SELECT ciniki_wineproduction_notifications.id, "
        . "ciniki_wineproduction_notifications.name, "
        . "ciniki_wineproduction_notifications.ntype, "
        . "ciniki_wineproduction_notifications.ntype AS ntype_text, "
        . "ciniki_wineproduction_notifications.offset_days, "
        . "ciniki_wineproduction_notifications.status, "
        . "ciniki_wineproduction_notifications.status AS status_text, "
        . "TIME_FORMAT(ciniki_wineproduction_notifications.email_time, '%l:%i %p') AS email_time, "
        . "ciniki_wineproduction_notifications.email_subject "
        . "FROM ciniki_wineproduction_notifications "
        . "WHERE ciniki_wineproduction_notifications.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    if( $trigger == 'entered' ) {
        $strsql .= "AND ntype = 10 ";
    } elseif( $trigger == 'started' ) {
        $strsql .= "AND ntype IN (20, 25) ";
    } elseif( $trigger == 'racked' ) {
        $strsql .= "AND ntype IN (50, 55) ";
    } elseif( $trigger == 'filtered' ) {
        $strsql .= "AND ntype IN (60, 65, 70) ";
    } elseif( $trigger == 'bottlingdate' ) {
        $strsql .= "AND ntype = 80 ";
    } elseif( $trigger == 'bottled' ) {
        $strsql .= "AND ntype IN (100, 120, 130, 150) ";
    } else {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.52', 'msg'=>'Invalid Trigger'));
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'notifications', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'ntype', 'ntype_text', 'offset_days', 
                'status', 'status_text', 'email_time', 'email_subject',
                ),
//            'maps'=>array(
//                'ntype_text'=>$maps['notification']['ntype'],
//                'status_text'=>$maps['notification']['status'],
//                ),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.53', 'msg'=>'Unable to get list of notifications for trigger', 'err'=>$rc['err']));
    }
    $notifications = isset($rc['notifications']) ? $rc['notifications'] : array();

    //
    // Process the notifications, making sure that the customer is subscribed to each notification type
    //
    foreach($notifications as $notification) {
        //
        // Check if the customer is subscribed to this notification type
        //
        if( !isset($subscriptions[$notification['ntype']]) 
            || ($subscriptions[$notification['ntype']]&0x03) == 0   // Not subscribed to either email or sms
            || ($subscriptions[$notification['ntype']]&0x10) == 0x10    // Was removed
            ) {
            continue;
        }

        //
        // Setup email time and offset days from today
        // Note: Not all ntypes will use this
        //
        $future_dt = new DateTime((isset($args['trigger_date']) ? $args['trigger_date'] : 'now'), new DateTimezone($intl_timezone));
        $future_dt = new DateTime($future_dt->format('Y-m-d') . ' ' . $notification['email_time'], new DateTimezone($intl_timezone));
        if( isset($notification['offset_days']) && $notification['offset_days'] > 0 ) { 
            $future_dt->add(new DateInterval('P' . $notification['offset_days'] . 'D'));
        }
        $future_dt->setTimezone(new DateTimezone('UTC'));

        //
        // New Customer
        //
        if( $notification['ntype'] == 10 ) {
            //
            // Setup email time and offset days from today
            //
            $future_dt = new DateTime($order['order_date'] . ' ' . $notification['email_time'], new DateTimezone($intl_timezone));
            if( isset($notification['offset_days']) && $notification['offset_days'] > 0 ) { 
                $future_dt->add(new DateInterval('P' . $notification['offset_days'] . 'D'));
            }
            $future_dt->setTimezone(new DateTimezone('UTC'));

            //
            // Check for prior orders
            //
            $strsql = "SELECT COUNT(*) "
                . "FROM ciniki_wineproductions "
                . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                . "AND customer_id = '" . ciniki_core_dbQuote($ciniki, $order['customer_id']) . "' "
                . "AND id <> '" . ciniki_core_dbQuote($ciniki, $order['id']) . "' "
                . "";
            ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbSingleCount');
            $rc = ciniki_core_dbSingleCount($ciniki, $strsql, 'ciniki.wineproduction', 'num');
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.57', 'msg'=>'Unable to load get the number of items', 'err'=>$rc['err']));
            }
            if( isset($rc['num']) && $rc['num'] > 0 ) {
                // If any other orders exist for customer, do nothing with this notification
                continue;
            }

            //
            // Check if notification already exists
            //
            if( isset($order_queue[$notification['id']]) ) {
                continue;
            }

            //
            // Add notification to queue
            //
            if( $future_dt > $today_dt ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
                $rc = ciniki_core_objectAdd($ciniki, $tnid, 'ciniki.wineproduction.notification_queue', array(
                    'scheduled_dt' => $future_dt->format('Y-m-d H:i:s'),
                    'notification_id' => $notification['id'],
                    'customer_id' => $order['customer_id'],
                    'order_id' => $order['id'],
                    ), 0x04);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.80', 'msg'=>'Unable to add the '));
                }
            }
        }

        //
        // Started
        // Post started education
        // SG Reading **future** 
        // Racked 
        // Post racked education
        // Filtered
        // Post filtered education
        // After Bottled Reminders
        // After Bottled Education
        // After Bottled Recipes
        // After Bottled Deals/Marketing
        //
        elseif( $notification['ntype'] == 20 
            || $notification['ntype'] == 25
            || $notification['ntype'] == 40
            || $notification['ntype'] == 50
            || $notification['ntype'] == 55
            || $notification['ntype'] == 60
            || $notification['ntype'] == 65
            || $notification['ntype'] == 100 
            || $notification['ntype'] == 110 
            || $notification['ntype'] == 130 
            ) {
            //
            // Setup email time and offset days from today
            //
            if( $notification['ntype'] == 20 || $notification['ntype'] == 25 ) {
                $future_dt = new DateTime($order['start_date'] . ' ' . $notification['email_time'], new DateTimezone($intl_timezone));
            } elseif( $notification['ntype'] == 50 || $notification['ntype'] == 55 ) {
                $future_dt = new DateTime($order['rack_date'] . ' ' . $notification['email_time'], new DateTimezone($intl_timezone));
            } elseif( $notification['ntype'] == 60 || $notification['ntype'] == 65 ) {
                $future_dt = new DateTime($order['filter_date'] . ' ' . $notification['email_time'], new DateTimezone($intl_timezone));
            } elseif( $notification['ntype'] >= 100 ) {
                $future_dt = new DateTime($order['bottle_date'] . ' ' . $notification['email_time'], new DateTimezone($intl_timezone));
            }
            if( isset($notification['offset_days']) && $notification['offset_days'] > 0 ) { 
                $future_dt->add(new DateInterval('P' . $notification['offset_days'] . 'D'));
            }
            $future_dt->setTimezone(new DateTimezone('UTC'));
            //
            // Check if it already exists
            //
            if( isset($order_queue[$notification['id']]) ) {
                if( $order_queue[$notification['id']]['scheduled_dt'] != $future_dt->format('Y-m-d H:i:s') ) {
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
                    $rc = ciniki_core_objectDelete($ciniki, $tnid, 'ciniki.wineproduction.notification_queue', 
                        $order_queue[$notification['id']]['id'], 
                        $order_queue[$notification['id']]['uuid'], 
                        0x04);
                    if( $rc['stat'] != 'ok' ) {
                        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.87', 'msg'=>'Unable to delete existing notification', 'err'=>$rc['err']));
                    }
                    unset($order_queue[$notification['id']]);
                }
                else {
                    // Already scheduled at correct time, skip
                    continue;
                }
            }
            
            //
            // Do we already have one of ntype scheduled for another order on this same day.
            //
            if( isset($ntype_day_queue[$notification['ntype']]['queue'][$future_dt->format('Y-m-d H:i:s')]) ) {
                continue;
            }

            //
            // Add notification to queue
            //
            if( $future_dt > $today_dt ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
                $rc = ciniki_core_objectAdd($ciniki, $tnid, 'ciniki.wineproduction.notification_queue', array(
                    'scheduled_dt' => $future_dt->format('Y-m-d H:i:s'),
                    'notification_id' => $notification['id'],
                    'customer_id' => $order['customer_id'],
                    'order_id' => $order['id'],
                    ), 0x04);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.81', 'msg'=>'Unable to add the '));
                }
            }
        }

        //
        // Filtered and no bottling appointment
        //
        elseif( $notification['ntype'] == 70 ) {
            //
            // Check for no bottling_date
            //
            $action = 'none';
            if( $notification['bottling_date'] == '0000-00-00 00:00:00' ) {
                $action = 'ctb';
            } else {
                $dt = new DateTime($notification['bottling_date'], new DateTimezone($intl_timezone));
                // Check if marked as midnight, then all day event
                if( $dt->format('H:i:s') == '00:00:00' ) {
                    $action = 'ctb';
                }
            }

            //
            // Setup queue
            //
            if( $action == 'ctb' ) {
                $future_dt = new DateTime($order['filter_date'] . ' ' . $notification['email_time'], new DateTimezone($intl_timezone));
                //
                // Check if it already exists
                //
                if( isset($order_queue[$notification['id']]) ) {
                    if( $order_queue[$notification['id']]['scheduled_dt'] != $future_dt->format('Y-m-d H:i:s') ) {
                        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
                        $rc = ciniki_core_objectDelete($ciniki, $tnid, 'ciniki.wineproduction.notification_queue', 
                            $order_queue[$notification['id']]['id'], 
                            $order_queue[$notification['id']]['uuid'], 
                            0x04);
                        if( $rc['stat'] != 'ok' ) {
                            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.97', 'msg'=>'Unable to delete existing notification', 'err'=>$rc['err']));
                        }
                        unset($order_queue[$notification['id']]);
                    }
                    else {
                        // Already scheduled at correct time, skip
                        continue;
                    }
                }
                
                //
                // Do we already have one of ntype scheduled for another order on this same day.
                //
                if( isset($ntype_day_queue[$notification['ntype']]['queue'][$future_dt->format('Y-m-d H:i:s')]) ) {
                    continue;
                }

                //
                // Add notification to queue
                //
                if( $future_dt > $today_dt ) {
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
                    $rc = ciniki_core_objectAdd($ciniki, $tnid, 'ciniki.wineproduction.notification_queue', array(
                        'scheduled_dt' => $future_dt->format('Y-m-d H:i:s'),
                        'notification_id' => $notification['id'],
                        'customer_id' => $order['customer_id'],
                        'order_id' => $order['id'],
                        ), 0x04);
                    if( $rc['stat'] != 'ok' ) {
                        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.98', 'msg'=>'Unable to add the notification queue item'));
                    }
                }
            }
        }

        //
        // Bottling Reminder
        //
        elseif( $notification['ntype'] == 80 ) {
            //
            // Setup email time and offset days previous to today
            //
            $email_dt = new DateTime($order['bottling_date'], new DateTimezone($intl_timezone));
            $email_dt = new DateTime($email_dt->format('Y-m-d') . ' ' . $notification['email_time'], new DateTimezone($intl_timezone));
            if( isset($notification['offset_days']) && $notification['offset_days'] > 0 ) { 
                $email_dt->sub(new DateInterval('P' . $notification['offset_days'] . 'D'));
            }
            $email_dt->setTimezone(new DateTimezone('UTC'));

            //
            // Check if it already exist for this notification for this order
            //
            if( isset($order_queue[$notification['id']]) ) {
                if( $order_queue[$notification['id']]['scheduled_dt'] != $email_dt->format('Y-m-d H:i:s') ) {
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
                    $rc = ciniki_core_objectDelete($ciniki, $tnid, 'ciniki.wineproduction.notification_queue', 
                        $order_queue[$notification['id']]['id'], 
                        $order_queue[$notification['id']]['uuid'], 
                        0x04);
                    if( $rc['stat'] != 'ok' ) {
                        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.88', 'msg'=>'Unable to delete existing notification', 'err'=>$rc['err']));
                    }
                    unset($order_queue[$notification['id']]);
                }
                else {
                    // Already scheduled at correct time, skip
                    continue;
                }
            }

            //
            // Check if any of this notification_id on this day for this customer
            //
            $strsql = "SELECT COUNT(queue.id) "
                . "FROM ciniki_wineproduction_notification_queue AS queue "
                . "WHERE queue.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                . "AND queue.customer_id = '" . ciniki_core_dbQuote($ciniki, $order['customer_id']) . "' "
                . "AND queue.notification_id = '" . ciniki_core_dbQuote($ciniki, $notification['id']) . "' "
                . "AND scheduled_dt = '" . ciniki_core_dbQuote($ciniki, $email_dt->format("Y-m-d H:i:s")) . "' "
                . "";
            ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbSingleCount');
            $rc = ciniki_core_dbSingleCount($ciniki, $strsql, 'ciniki.wineproduction', 'num');
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.89', 'msg'=>'Unable to load get the number of items', 'err'=>$rc['err']));
            }
            if( isset($rc['num']) && $rc['num'] > 0 ) {
                continue;
            }

            //
            // Add notification to queue
            //
            if( $email_dt > $future_dt ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
                $rc = ciniki_core_objectAdd($ciniki, $tnid, 'ciniki.wineproduction.notification_queue', array(
                    'scheduled_dt' => $email_dt->format('Y-m-d H:i:s'),
                    'notification_id' => $notification['id'],
                    'customer_id' => $order['customer_id'],
                    'order_id' => $order['id'],
                    ), 0x04);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.82', 'msg'=>'Unable to add the '));
                }
            }
        }

        elseif( $notification['ntype'] == 150 ) {
            //
            // Check if it already exists
            //
            if( isset($order_queue[$notification['id']]) ) {
                if( $order_queue[$notification['id']]['scheduled_dt'] != $future_dt->format('Y-m-d H:i:s') ) {
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
                    $rc = ciniki_core_objectDelete($ciniki, $tnid, 'ciniki.wineproduction.notification_queue', 
                        $order_queue[$notification['id']]['id'], 
                        $order_queue[$notification['id']]['uuid'], 
                        0x04);
                    if( $rc['stat'] != 'ok' ) {
                        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.60', 'msg'=>'Unable to delete existing notification', 'err'=>$rc['err']));
                    }
                    unset($order_queue[$notification['id']]);
                }
                else {
                    // Already scheduled at correct time, skip
                    continue;
                }
            }
            
            //
            // Do we already have one of ntype scheduled, doesn't matter when we
            // only want 1 of this notification type ever in the queue at a time for a customer.
            //
            if( isset($ntype_day_queue[$notification['ntype']]) ) {
                continue;
            }

            //
            // Check if there are other open orders in the system, that were ordered after this one
            //
            $strsql = "SELECT COUNT(*) "
                . "FROM ciniki_wineproductions "
                . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                . "AND customer_id = '" . ciniki_core_dbQuote($ciniki, $order['customer_id']) . "' "
                . "AND order_date >= '" . ciniki_core_dbQuote($ciniki, $order['order_date']) . "' "
                . "";
            ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbSingleCount');
            $rc = ciniki_core_dbSingleCount($ciniki, $strsql, 'ciniki.wineproduction', 'num');
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.62', 'msg'=>'Unable to load get the number of items', 'err'=>$rc['err']));
            }
            if( isset($rc['num']) && $rc['num'] > 0 ) {
                continue;
            }

            //
            // Add notification to queue
            //
            if( $future_dt > $today_dt ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
                $rc = ciniki_core_objectAdd($ciniki, $tnid, 'ciniki.wineproduction.notification_queue', array(
                    'scheduled_dt' => $future_dt->format('Y-m-d H:i:s'),
                    'notification_id' => $notification['id'],
                    'customer_id' => $order['customer_id'],
                    'order_id' => $order['id'],
                    ), 0x04);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.59', 'msg'=>'Unable to add the '));
                }
            }
        }
    }

    return array('stat'=>'ok');
}
?>
