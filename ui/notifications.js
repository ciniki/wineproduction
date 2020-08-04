//
// The UI to handle customer notifications from orders
//
function ciniki_wineproduction_notifications() {
    //
    // The panel to list the notification
    //
    this.menu = new M.panel('Notifications', 'ciniki_wineproduction_notifications', 'menu', 'mc', 'xlarge', 'sectioned', 'ciniki.wineproduction.notifications.menu');
    this.menu.data = {};
    this.menu.nplist = [];
    this.menu.sections = {
        'notifications':{'label':'Notification', 'type':'simplegrid', 'num_cols':6,
            'headerValues':['Type', 'Name', 'Delay', 'Status', 'Time', 'Subject'],
            'noData':'No notifications',
            'addTxt':'Add Notification',
            'addFn':'M.ciniki_wineproduction_notifications.notification.open(\'M.ciniki_wineproduction_notifications.menu.open();\',0,null);'
            },
    }
    this.menu.cellValue = function(s, i, j, d) {
        if( s == 'notifications' ) {
            switch(j) {
                case 0: return d.ntype_text;
                case 1: return d.name;
                case 2: return (d.offset_days > 0 ? d.offset_days + ' days' : 'Same Day');
                case 3: return d.status_text;
                case 4: return d.email_time;
                case 5: return d.email_subject;
            }
        }
    }
    this.menu.rowFn = function(s, i, d) {
        if( s == 'notifications' ) {
            return 'M.ciniki_wineproduction_notifications.notification.open(\'M.ciniki_wineproduction_notifications.menu.open();\',\'' + d.id + '\',M.ciniki_wineproduction_notifications.menu.nplist);';
        }
    }
    this.menu.open = function(cb) {
        M.api.getJSONCb('ciniki.wineproduction.notificationList', {'tnid':M.curTenantID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_wineproduction_notifications.menu;
            p.data = rsp;
            p.nplist = (rsp.nplist != null ? rsp.nplist : null);
            p.refresh();
            p.show(cb);
        });
    }
    this.menu.addClose('Back');

    //
    // The panel to edit Notification
    //
    this.notification = new M.panel('Notification', 'ciniki_wineproduction_notifications', 'notification', 'mc', 'medium', 'sectioned', 'ciniki.wineproduction.main.notification');
    this.notification.data = null;
    this.notification.notification_id = 0;
    this.notification.nplist = [];
    this.notification.sections = {
        'general':{'label':'', 'fields':{
            'ntype':{'label':'Type', 'required':'yes', 'type':'select', 'options':{
                '10':'New Customer',
                '20':'Started',
                '25':'Post Started Education',
//                '40':'SG Reading', **future**
                '50':'Racked',
                '55':'Post Racked Education',
                '60':'Filtered',
                '65':'Post Filtered Education',
                '80':'Upcoming Bottling Reminder',
                '100':'Post Bottling Reminder',
                '120':'Post Bottling Education',
//                '130':'Post Bottling Recipes', **future**
                '150':'Post Bottling No Order Deals',
                }},
            'name':{'label':'Name', 'required':'yes', 'type':'text'},
            'offset_days':{'label':'Days After/Before', 'type':'text', 'size':'small'},
            'status':{'label':'Status', 'type':'toggle', 'toggles':{'0':'Inactive', '10':'Require Approval', '20':'Auto Send'}},
            'email_time':{'label':'Email Time', 'type':'text', 'size':'small'},
            'email_subject':{'label':'Email Subject', 'type':'text'},
            }},
        '_email_content':{'label':'Email Message', 'fields':{
            'email_content':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'large'},
            }},
//        '_sms_content':{'label':'SMS Message', 'fields':{
//            'sms_content':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'medium'},
//            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_wineproduction_notifications.notification.save();'},
            'delete':{'label':'Delete', 
                'visible':function() {return M.ciniki_wineproduction_notifications.notification.notification_id > 0 ? 'yes' : 'no'; },
                'fn':'M.ciniki_wineproduction_notifications.notification.remove();'},
            }},
        };
    this.notification.fieldValue = function(s, i, d) { return this.data[i]; }
    this.notification.fieldHistoryArgs = function(s, i) {
        return {'method':'ciniki.wineproduction.notificationHistory', 'args':{'tnid':M.curTenantID, 'notification_id':this.notification_id, 'field':i}};
    }
    this.notification.open = function(cb, nid, list) {
        if( nid != null ) { this.notification_id = nid; }
        if( list != null ) { this.nplist = list; }
        M.api.getJSONCb('ciniki.wineproduction.notificationGet', {'tnid':M.curTenantID, 'notification_id':this.notification_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_wineproduction_notifications.notification;
            p.data = rsp.notification;
            p.refresh();
            p.show(cb);
        });
    }
    this.notification.save = function(cb) {
        if( cb == null ) { cb = 'M.ciniki_wineproduction_notifications.notification.close();'; }
        if( !this.checkForm() ) { return false; }
        if( this.notification_id > 0 ) {
            var c = this.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('ciniki.wineproduction.notificationUpdate', {'tnid':M.curTenantID, 'notification_id':this.notification_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    eval(cb);
                });
            } else {
                eval(cb);
            }
        } else {
            var c = this.serializeForm('yes');
            M.api.postJSONCb('ciniki.wineproduction.notificationAdd', {'tnid':M.curTenantID}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_wineproduction_notifications.notification.notification_id = rsp.id;
                eval(cb);
            });
        }
    }
    this.notification.remove = function() {
        if( confirm('Are you sure you want to remove notification?') ) {
            M.api.getJSONCb('ciniki.wineproduction.notificationDelete', {'tnid':M.curTenantID, 'notification_id':this.notification_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_wineproduction_notifications.notification.close();
            });
        }
    }
    this.notification.nextButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.notification_id) < (this.nplist.length - 1) ) {
            return 'M.ciniki_wineproduction_notifications.notification.save(\'M.ciniki_wineproduction_notifications.notification.open(null,' + this.nplist[this.nplist.indexOf('' + this.notification_id) + 1] + ');\');';
        }
        return null;
    }
    this.notification.prevButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.notification_id) > 0 ) {
            return 'M.ciniki_wineproduction_notifications.notification.save(\'M.ciniki_wineproduction_notifications.notification.open(null,' + this.nplist[this.nplist.indexOf('' + this.notification_id) - 1] + ');\');';
        }
        return null;
    }
    this.notification.addButton('save', 'Save', 'M.ciniki_wineproduction_notifications.notification.save();');
    this.notification.addClose('Cancel');
    this.notification.addButton('next', 'Next');
    this.notification.addLeftButton('prev', 'Prev');

    //
    // The panel to display the notifications for a customer
    //
    this.customer = new M.panel('Customer Notifications',
        'ciniki_wineproduction_notifications', 'customer',
        'mc', 'medium mediumaside', 'sectioned', 'ciniki.wineproduction.notifications.customer');
    this.customer.data = {};
    this.customer.notification_id = 0;
    this.customer.sections = {
        'customer_details':{'label':'Customer', 'aside':'yes', 'type':'simplegrid', 'num_cols':2, 
            'cellClasses':['label', ''],
//            'changeTxt':'View Customer',
//            'changeFn':'M.startApp(\'ciniki.customers.main\',null,\'M.ciniki_customers_reminders.reminder.open();\',\'mc\',{\'customer_id\':M.ciniki_customers_reminders.reminder.data.customer_id});',
            },
        'notifications':{'label':'Notifications', 'fields':{
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save Notifications', 'fn':'M.ciniki_wineproduction_notifications.customer.save();'},
            }},
        };
    this.customer.cellValue = function(s, i, j, d) {
        if( s == 'customer_details' ) {
            switch(j) {
                case 0: return d.label;
                case 1: return (d.label == 'Email' ? M.linkEmail(d.value):d.value);
            }
        }
    }
    this.customer.open = function(cb, cid) {
        if( cid != null ) { this.customer_id = cid; }
        M.api.getJSONCb('ciniki.wineproduction.customerNotificationsGet', 
            {'tnid':M.curTenantID, 'customer_id':this.customer_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_wineproduction_notifications.customer;
                p.data = rsp;
                p.sections.notifications.fields = {};
                for(var i in rsp.notifications) {
                    p.sections.notifications.fields['n_' + rsp.notifications[i].ntype] = {'label':rsp.notifications[i].label,
                        'type':'toggle',
                        'toggles':{'10':'Subscribed', '60':'Removed'},
                        };
                    if( (rsp.notifications[i].flags&0x10) == 0x10 ) {
                        p.data['n_' + rsp.notifications[i].ntype] = 60;
                    } else if( (rsp.notifications[i].flags&0x01) == 0x01 ) {
                        p.data['n_' + rsp.notifications[i].ntype] = 10;
                    } else {
                        p.data['n_' + rsp.notifications[i].ntype] = 0;
                    }
                }
                p.refresh();
                p.show(cb);
            });
    };
    this.customer.save = function() {
        var subs = '';
        var unsubs = '';
        if( this.data.notifications != null ) {
            for(i in this.data.notifications) {
                var fname = 'n_' + this.data.notifications[i].ntype;
                var o = this.fieldValue('notifications', fname, this.sections.notifications.fields[fname]);
                var n = this.formValue(fname);
                if( o != n && n > 0 ) {
                    if( n == 10 ) {
                        subs += (subs != '' ? ',' : '') + this.data.notifications[i].ntype;
                    } else if( n == 60 ) {
                        unsubs += (unsubs != '' ? ',' : '') + this.data.notifications[i].ntype;
                    }
                }   
            }
        }
        if( subs != '' || unsubs != '' ) {
            M.api.getJSONCb('ciniki.wineproduction.customerNotificationsUpdate', 
                {'tnid':M.curTenantID, 'customer_id':this.customer_id, 'subs':subs, 'unsubs':unsubs}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_wineproduction_notifications.customer.close();
                });
        } else {
            this.close();
        }
    }
    this.customer.addClose('Back');

    //
    // Arguments:
    // aG - The arguments to be parsed into args
    //
    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) {
            args = eval(aG);
        }

        //
        // Create the app container if it doesn't exist, and clear it out
        // if it does exist.
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_wineproduction_notifications', 'yes');
        if( appContainer == null ) {
            M.alert('App Error');
            return false;
        } 

        if( args.customer_id != null && args.customer_id != '' ) {
            this.customer.open(cb, args.customer_id);
        } else {
            this.menu.open(cb);
        }
    };
}
