//
// This is the main app for the wineproduction module
//
function ciniki_wineproduction_main() {
    
    //
    // The panel to list the order
    //
    this.menu = new M.panel('Production', 'ciniki_wineproduction_main', 'menu', 'mc', 'medium', 'sectioned', 'ciniki.wineproduction.main.menu');
    this.menu.data = {};
    this.menu.nplist = [];
    this.menu.view = 'menu';
    this.menu.view_aside = 'yes';
    this.menu.view_content = 'no';
    this.menu.offset = 'none';   // Used when colour and location columns needed
    this.menu.workdate = 'today';
    this.menu.schedulestatus = '';
    this.menu.scheduledate = '';
    this.menu.sections = {
        'search':{'label':'Search All Orders', 'type':'livesearchgrid', 'livesearchcols':8,
            'visible':function() { return M.ciniki_wineproduction_main.menu.size == 'medium' && M.ciniki_wineproduction_main.menu.view == 'menu' ? 'yes' : 'no'; },
            'headerValues':['Inv#', 'Wine', 'Type', 'BD', 'OD', 'SD', 'RD', 'FD'],
            'cellClasses':['multiline', 'multiline', 'multiline', 'multiline', 'multiline', 'multiline', 'multiline', 'multiline'],
            'hint':'inv#, customer or product',
            'noData':'No order found',
            },
        'today':{'label':'Todays Production', 'type':'simplegrid', 'num_cols':1, 'aside':'yes',
            'visible':function() { return M.ciniki_wineproduction_main.menu.view_aside; },
            'noData':'No production today',
            },
        'statuses':{'label':'Orders', 'type':'simplegrid', 'num_cols':1, 'aside':'yes',
            'visible':function() { return M.ciniki_wineproduction_main.menu.view_aside; },
            'noData':'No orders',
            },
        'reports':{'label':'Reports', 'type':'simplegrid', 'num_cols':1, 'aside':'yes',
            'visible':function() { return M.ciniki_wineproduction_main.menu.view_aside; },
            'noData':'No reports',
            },
        'search2':{'label':'Search', 'type':'livesearchgrid', 'livesearchcols':8,
            'visible':function() { return M.ciniki_wineproduction_main.menu.view_content; },
            'headerValues':['Inv#', 'Wine', 'Type', 'BD', 'OD', 'SD', 'RD', 'FD'],
            'cellClasses':['multiline', 'multiline', 'multiline', 'multiline', 'multiline', 'multiline', 'multiline', 'multiline'],
            'hint':'inv#, customer or product',
            'noData':'No order found',
            },
        'datepicker':{'label':'', 'type':'datepicker', 'fn':'M.ciniki_wineproduction_main.menu.changeDate',
            'visible':function() { return M.ciniki_wineproduction_main.menu.view == 'completed' ? 'yes' : 'no'; },
            },
        'schedule':{'label':'Schedule', 'type':'simplegrid', 'num_cols':17,
            'visible':function() { return M.ciniki_wineproduction_main.menu.view == 'schedule' ? 'yes' : 'no'; },
            },
        'ordered':{'label':'Ordered', 'type':'simplegrid', 'num_cols':6,
//            'visible':function() { return ['ordered','completed'].includes(M.ciniki_wineproduction_main.menu.view) ? 'yes' : 'no'; },
            'visible':function() { return M.ciniki_wineproduction_main.menu.data.ordered != null ? 'yes' : 'no'; },
            'headerValues':['Inv#', 'Wine', 'Type', 'Ordered', 'Bottling', ''],
            'sortable':'yes',
            'sortTypes':['number', 'text', 'text', 'date', 'date', ''],
            'values':['invoice_number', 'wine', 'type', 'order_date', 'bottling_date', 'notes'],
            'noData':'No orders',
            },
        'starting':{'label':'Starting', 'type':'simplegrid', 'num_cols':7, 
//            'visible':function() { return M.ciniki_wineproduction_main.menu.view == 'starting' ? 'yes' : 'no'; },
            'visible':function() { return M.ciniki_wineproduction_main.menu.data.starting != null ? 'yes' : 'no'; },
            'sortable':'yes',
            'headerValues':['Inv#', 'Wine', 'Type', 'Ordered', 'Bottling', '', ''],
            'sortTypes':['number', 'text', 'text', 'date', 'date', 'text', 'text'],
            'values':['invoice_number', 'wine', 'type', 'order_date', 'bottling_date', 'startbtn', 'notes'],
            'noData':'No orders',
            },
        'started':{'label':'Started', 'type':'simplegrid', 'num_cols':9, 'offset':'yes',
//            'visible':function() { return ['starting', 'started', 'completed'].includes(M.ciniki_wineproduction_main.menu.view) ? 'yes' : 'no'; },
            'visible':function() { return M.ciniki_wineproduction_main.menu.data.started != null ? 'yes' : 'no'; },
            'sortable':'yes',
            'headerValues':['', '', 'Inv#', 'Wine', 'Type', 'Started', 'Bottling', 'Racking', ''],
            'sortTypes':['', '', 'number', 'text', 'text', 'date', 'date', 'date', ''],
            'values':['location', 'rack_colour', 'invoice_number', 'wine', 'type', 'start_date', 'bottling_date', 'racking_date', 'notes'],
            'noData':'No orders',
            },
        'tsgreadings':{'label':'Transfer SG Readings', 'type':'simplegrid', 'num_cols':8, 'offset':'yes',
//            'visible':function() { return M.ciniki_wineproduction_main.menu.view == 'tsgreadings' ? 'yes' : 'no'; },
            'visible':function() { return M.ciniki_wineproduction_main.menu.data.tsgreadings != null ? 'yes' : 'no'; },
            'headerValues':['', '', 'Inv#', 'Wine', 'Type', 'SG', '', ''],
            'sortable':'yes',
            'sortTypes':['', '', 'number', 'text', 'text', 'number', '', ''],
            'values':['location', 'rack_colour', 'invoice_number', 'wine', 'type', 'tsg_reading', 'tsgbtn', 'notes'],
            'noData':'No orders',
            },
        'tsgread':{'label':'Transfer SG Read', 'type':'simplegrid', 'num_cols':8, 'offset':'yes',
//            'visible':function() { return M.ciniki_wineproduction_main.menu.view == 'tsgreadings' ? 'yes' : 'no'; },
            'visible':function() { return M.ciniki_wineproduction_main.menu.data.tsgread != null ? 'yes' : 'no'; },
            'headerValues':['', '', 'Inv#', 'Wine', 'Type', 'SG', '', ''],
            'sortable':'yes',
            'sortTypes':['', '', 'number', 'text', 'text', 'number', '', ''],
            'values':['location', 'rack_colour', 'invoice_number', 'wine', 'type', 'tsg_reading', 'tsgbtn', 'notes'],
            'noData':'No orders',
            },
        'transferring':{'label':'Transferring', 'type':'simplegrid', 'num_cols':11, 'offset':'yes',
//            'visible':function() { return M.ciniki_wineproduction_main.menu.view == 'transferring' ? 'yes' : 'no'; },
            'visible':function() { return M.ciniki_wineproduction_main.menu.data.transferring != null ? 'yes' : 'no'; },
            'headerValues':['', '', 'Inv#', 'Wine', 'Type', 'SG', 'Started', 'Bottling', 'Transferring', '', ''],
            'sortable':'yes',
            'sortTypes':['', '', 'number', 'text', 'text', 'number', 'date', 'date', 'date', '', ''],
            'values':['location', 'rack_colour', 'invoice_number', 'wine', 'type', 'tsg_reading', 'start_date', 'bottling_date', 'transferring_date', 'transferbtn', 'notes'],
            'noData':'No orders',
            },
        'transferred':{'label':'Transferred', 'type':'simplegrid', 'num_cols':10, 'offset':'yes',
//            'visible':function() { return ['transferring', 'transferred', 'completed'].includes(M.ciniki_wineproduction_main.menu.view) ? 'yes' : 'no'; },
            'visible':function() { return M.ciniki_wineproduction_main.menu.data.transferred != null ? 'yes' : 'no'; },
            'headerValues':['', '', 'Inv#', 'Wine', 'Type', 'SG', 'Started', 'Bottling', 'Racking', ''],
            'sortable':'yes',
            'sortTypes':['', '', 'number', 'text', 'text', 'number', 'date', 'date', 'date', ''],
            'values':['location', 'rack_colour', 'invoice_number', 'wine', 'type', 'tsg_reading', 'start_date', 'bottling_date', 'racking_date', 'notes'],
            'noData':'No orders',
            },
        'sgreadings':{'label':'SG Readings', 'type':'simplegrid', 'num_cols':8, 'offset':'yes',
//            'visible':function() { return M.ciniki_wineproduction_main.menu.view == 'sgreadings' ? 'yes' : 'no'; },
            'visible':function() { return M.ciniki_wineproduction_main.menu.data.sgreadings != null ? 'yes' : 'no'; },
            'headerValues':['', '', 'Inv#', 'Wine', 'Type', 'SG', '', ''],
            'sortable':'yes',
            'sortTypes':['', '', 'number', 'text', 'text', 'number', '', ''],
            'values':['location', 'rack_colour', 'invoice_number', 'wine', 'type', 'sg_reading', 'sgbtn', 'notes'],
            'noData':'No orders',
            },
        'sgread':{'label':'SG Read', 'type':'simplegrid', 'num_cols':8, 'offset':'yes',
//            'visible':function() { return M.ciniki_wineproduction_main.menu.view == 'sgreadings' ? 'yes' : 'no'; },
            'visible':function() { return M.ciniki_wineproduction_main.menu.data.sgread != null ? 'yes' : 'no'; },
            'headerValues':['', '', 'Inv#', 'Wine', 'Type', 'SG', '', ''],
            'sortable':'yes',
            'sortTypes':['', '', 'number', 'text', 'text', 'number', '', ''],
            'values':['location', 'rack_colour', 'invoice_number', 'wine', 'type', 'sg_reading', 'sgbtn', 'notes'],
            'noData':'No orders',
            },
        'racking':{'label':'Racking', 'type':'simplegrid', 'num_cols':11, 'offset':'yes',
//            'visible':function() { return M.ciniki_wineproduction_main.menu.view == 'racking' ? 'yes' : 'no'; },
            'visible':function() { return M.ciniki_wineproduction_main.menu.data.racking != null ? 'yes' : 'no'; },
            'headerValues':['', '', 'Inv#', 'Wine', 'Type', 'SG', 'Started', 'Bottling', 'Racking','', ''],
            'sortable':'yes',
            'sortTypes':['', '', 'number', 'text', 'text', 'number', 'date', 'date', 'date', '', ''],
            'values':['location', 'rack_colour', 'invoice_number', 'wine', 'type', 'sg_reading', 'start_date', 'bottling_date', 'racking_date', 'rackbtn', 'notes'],
            'noData':'No orders',
            },
        'racked':{'label':'Racked', 'type':'simplegrid', 'num_cols':10, 'offset':'yes',
//            'visible':function() { return ['racking', 'racked', 'completed'].includes(M.ciniki_wineproduction_main.menu.view) ? 'yes' : 'no'; },
            'visible':function() { return M.ciniki_wineproduction_main.menu.data.racked != null ? 'yes' : 'no'; },
            'headerValues':['', '', 'Inv#', 'Wine', 'Type', 'SG', 'Racked', 'Bottling', 'Filtering',''],
            'sortable':'yes',
            'sortTypes':['', '', 'number', 'text', 'text', 'number', 'date', 'date', 'date', ''],
            'values':['location', 'filter_colour', 'invoice_number', 'wine', 'type', 'sg_reading', 'rack_date', 'bottling_date', 'filtering_date', 'notes'],
            'noData':'No orders',
            },
        'filtering':{'label':'Filtering', 'type':'simplegrid', 'num_cols':10, 'offset':'yes',
//            'visible':function() { return M.ciniki_wineproduction_main.menu.view == 'filtering' ? 'yes' : 'no'; },
            'visible':function() { return M.ciniki_wineproduction_main.menu.data.filtering != null ? 'yes' : 'no'; },
            'headerValues':['', '', 'Inv#', 'Wine', 'Type', 'Racked', 'Bottling', 'Filtering', '', ''],
            'sortable':'yes',
            'sortTypes':['', '', 'number', 'text', 'text', 'date', 'date', 'date', '', ''],
            'values':['location', 'filter_colour', 'invoice_number', 'wine', 'type', 'racking_date', 'bottling_date', 'filtering_date', 'filterbtn', 'notes'],
            'noData':'No orders',
            },
        'filtered':{'label':'Filtered', 'type':'simplegrid', 'num_cols':6,
//            'visible':function() { return ['filtering', 'filtered', 'completed'].includes(M.ciniki_wineproduction_main.menu.view) ? 'yes' : 'no'; },
            'visible':function() { return M.ciniki_wineproduction_main.menu.data.filtered != null ? 'yes' : 'no'; },
            'headerValues':['Inv#', 'Wine', 'Type', 'Filtered', 'Bottling', ''],
            'sortable':'yes',
            'sortTypes':['number', 'text', 'text', 'date', 'date', ''],
            'values':['invoice_number', 'wine', 'type', 'filter_date', 'bottling_date', 'notes'],
            'noData':'No orders',
            },
        'bottling':{'label':'Bottling', 'type':'simplegrid', 'num_cols':6,
//            'visible':function() { return ['bottled', 'completed'].includes(M.ciniki_wineproduction_main.menu.view) ? 'yes' : 'no'; },
            'visible':function() { return M.ciniki_wineproduction_main.menu.data.bottling != null ? 'yes' : 'no'; },
            'headerValues':['Inv#', 'Wine', 'Type', 'Racked', 'Bottling', 'Filtering'],
            'sortable':'yes',
            'sortTypes':['number', 'text', 'text', 'date', 'date', 'date'],
            'values':['invoice_numstat', 'wine', 'type', 'rack_date', 'bottling_date', 'filtering_date'],
            'noData':'No orders',
            },
        'bottled':{'label':'Bottled', 'type':'simplegrid', 'num_cols':4,
//            'visible':function() { return ['bottled', 'completed'].includes(M.ciniki_wineproduction_main.menu.view) ? 'yes' : 'no'; },
            'visible':function() { return M.ciniki_wineproduction_main.menu.data.bottled != null ? 'yes' : 'no'; },
            'headerValues':['Inv#', 'Wine', 'Type', 'Bottled'],
            'sortable':'yes',
            'sortTypes':['number', 'text', 'text', 'date'],
            'values':['invoice_number', 'wine', 'type', 'bottle_date'],
            'noData':'No orders',
            },
        'late':{'label':'Late Wines', 'type':'simplegrid', 'num_cols':9,
            'visible':function() { return M.ciniki_wineproduction_main.menu.view == 'late' ? 'yes' : 'no'; },
            'headerValues':['Inv#', 'Wine', 'Type', 'Ordered', 'Started', 'Racked', 'Filtered', 'Bottling', ''],
            'sortable':'yes',
            'sortTypes':['number', 'text', 'text', 'date', 'date', 'date', 'date', 'date', ''],
            'values':['invoice_number', 'wine', 'type', 'order_date', 'start_date', 'rack_date', 'filter_date', 'bottling_date', 'notes'],
            },
        'ctb':{'label':'Call to Book', 'type':'simplegrid', 'num_cols':10,
            'visible':function() { return M.ciniki_wineproduction_main.menu.view == 'ctb' ? 'yes' : 'no'; },
            'headerValues':['', 'Inv#', 'Wine', 'Type', 'Ordered', 'Started', 'Racked', 'Filtered', 'Bottling', ''],
            'sortable':'yes',
            'sortTypes':['', 'number', 'text', 'text', 'date', 'date', 'date', 'date', 'date', ''],
            'values':['bottling_colour', 'invoice_number', 'wine', 'type', 'order_date', 'start_date', 'rack_date', 'filter_date', 'bottling_date', 'notes'],
            },
    }
    this.menu.liveSearchCb = function(s, i, v) {
        if( s == 'search' && v != '' ) {
            M.api.getJSONBgCb('ciniki.wineproduction.orderSearch', {'tnid':M.curTenantID, 'start_needle':v, 'limit':'25'}, function(rsp) {
                M.ciniki_wineproduction_main.menu.liveSearchShow('search',null,M.gE(M.ciniki_wineproduction_main.menu.panelUID + '_' + s), rsp.orders);
                });
        }
        if( s == 'search2' && v != '' ) {
            M.api.getJSONBgCb('ciniki.wineproduction.orderSearch', {'tnid':M.curTenantID, 'start_needle':v, 'limit':'25'}, function(rsp) {
                M.ciniki_wineproduction_main.menu.liveSearchShow('search2',null,M.gE(M.ciniki_wineproduction_main.menu.panelUID + '_' + s), rsp.orders);
                });
        }
    }
    this.menu.liveSearchResultValue = function(s, f, i, j, d) {
        switch(j) {
            case 0: return M.multiline(d.invoice_number, d.status_text);
            case 1: return M.multiline(d.wine_name, d.customer_name);
            case 2: return M.multiline(d.wine_type, d.kit_length);
            case 3: return d.bottling_date.replace(/(...)\s([0-9]+),\s([0-9][0-9][0-9][0-9])/, "<span class='maintext'>$1<\/span><span class='subtext'>$2<\/span>");
            case 4: return d.order_date.replace(/(...)\s([0-9]+),\s([0-9][0-9][0-9][0-9])/, "<span class='maintext'>$1<\/span><span class='subtext'>$2<\/span>");
            case 5: return d.start_date.replace(/(...)\s([0-9]+),\s([0-9][0-9][0-9][0-9])/, "<span class='maintext'>$1<\/span><span class='subtext'>$2<\/span>");
            case 6: return d.racking_date.replace(/(...)\s([0-9]+),\s([0-9][0-9][0-9][0-9])/, "<span class='maintext'>$1<\/span><span class='subtext'>$2<\/span>");
            case 7: return d.filtering_date.replace(/(...)\s([0-9]+),\s([0-9][0-9][0-9][0-9])/, "<span class='maintext'>$1<\/span><span class='subtext'>$2<\/span>");
        }
        return '';
    }
    this.menu.liveSearchResultRowFn = function(s, f, i, j, d) {
        return 'M.ciniki_wineproduction_main.order.open(\'M.ciniki_wineproduction_main.menu.open();\',\'' + d.id + '\');';
    }
    this.menu.liveSearchSubmitFn = function(s, search_str) {
        M.ciniki_wineproduction_main.search.open('M.ciniki_wineproduction_main.menu.open();',search_str);
    }
    this.menu.headerValue = function(s, i, sc) {
        if( this.sections[s].headerValues == null ) {   
            return null;
        }
        if( this.sections[s].offset != null && this.sections[s].offset == 'yes' ) {
            if( this.offset == 'both' ) {
                return this.sections[s].headerValues[i];
            } else if( this.offset == 'colours' ) {
                return this.sections[s].headerValues[(i+1)];
            } else if( this.offset == 'location' ) {
                if( i > 0 ) {
                    return this.sections[s].headerValues[(i+1)];
                } else {
                    return this.sections[s].headerValues[i];
                }
            } else {
                return this.sections[s].headerValues[(i+2)];
            }
        } else {
            return this.sections[s].headerValues[i];
        }
        return null;
    }
    this.menu.cellClass = function(s, i, j, d) {
        if( s == 'schedule' && i != 'dates' && d[j].date == this.scheduledate && i == this.schedulestatus ) {
            return 'multiline aligncenter highlightcell';
        }
        if( s == 'schedule' && i == 'dates' ) {
            if( j > 0 ) {
                if( d[j].date == this.scheduledate ) {
                    return 'multiline aligncenter highlightcell';
                }
                return 'multiline aligncenter';
            }
            return 'multiline';
        } else if( s == 'schedule' && j > 0 ) {  
            return 'aligncenter';
        }
        var offset = 0;
        if( this.sections[s].offset != null && this.sections[s].offset == 'yes' ) {
            if( this.offset == 'none' ) {
                offset = 0;
            } else if( this.offset == 'colours' ) {
                offset = 1;
            } else if( this.offset == 'location' ) {
                offset = 1;
            } else {
                offset = 2;
            }
        }
        if( this.sections[s].headerValues != null && ['Inv#', 'Wine','Type'].includes(this.sections[s].headerValues[(j+offset)]) ) {
            return 'multiline';
        }
    }
    this.menu.cellFn = function(s, i, j, d) {
        if( s == 'schedule' && ['transferring','racking','filtering','bottling'].includes(i) ) {
            if( j < 1 || j > 16 ) {
                return '';
            }
            return 'M.ciniki_wineproduction_main.menu.switchSchedule(\'' + i + '\',\'' + d[j].date + '\');';
        }
        return '';
    }
    this.menu.rowStyle = function(s, i, d) {
        if( s == 'ctb' ) {
            if( d.bottling_bgcolour != null && d.bottling_bgcolour != '' ) {
                return 'background: ' + d.bottling_bgcolour.replace(/,.*/, '') + ';';
            }
        }
        else if( d.bgcolour != null && d.bgcolour != '' ) {
            return 'background: ' + d.bgcolour.replace(/,.*/, '') + ';';
        }
        return '';
    }
    this.menu.cellValue = function(s, i, j, d) {
        if( s == 'today' || s == 'statuses' || s == 'reports' ) {
            switch(j) {
                case 0: return M.textCount(d.label, d.count);
            }
        }
        if( s == 'schedule' ) {
            return d[j].label;
        }
        if( this.sections[s].values != null ) {
            var offset = 0;
            if( this.sections[s].offset != null && this.sections[s].offset == 'yes' ) {
                if( this.offset == 'both' ) {
                    offset = 0;
                } else if( this.offset == 'colours' ) {
                    offset = 1;
                } else if( this.offset == 'location' ) {
                    if( j > 0 ) {
                        offset = 1;
                    } else {
                        offset = 0;
                    }
                }
            }
            switch(this.sections[s].values[(j+offset)]) {
                case 'location': return d.location;
                case 'invoice_number': return M.multiline(d.invoice_number, d.order_options);
                case 'invoice_numstat': return M.multiline(d.invoice_number, d.status_text);
                case 'wine': return M.multiline(d.wine_name, d.customer_name);
                case 'type': return M.multiline(d.wine_type, d.kit_length + ' weeks');
                case 'rack_colour': return M.colourSwatch(d.rack_colour);
                case 'filter_colour': return M.colourSwatch(d.filter_colour);
                case 'order_date': return d.order_date;
                case 'start_date': return d.start_date;
                case 'transferring_date': return d.transferring_date;
                case 'transfer_date': return d.transfer_date;
                case 'tsg_reading': return d.tsg_reading;
                case 'sg_reading': return d.sg_reading;
                case 'racking_date': return d.racking_date;
                case 'rack_date': return d.rack_date;
                case 'filtering_date': return d.filtering_date;
                case 'filter_date': return d.filter_date;
                case 'bottling_date': return d.bottling_date;
                case 'bottle_date': return d.bottle_date;
                case 'startbtn': return M.btn('Started', 'M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'started\');');
                case 'transferbtn': return M.btn('Transferred', 'M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'transferred\');');
                case 'tsgbtn': 
                    return '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'tsgread\',\'992\');">92</button>'
                        + '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'tsgread\',\'993\');">93</button>'
                        + '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'tsgread\',\'994\');">94</button>'
                        + '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'tsgread\',\'995\');">95</button>'
                        + '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'tsgread\',\'996\');">96</button>'
                        + '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'tsgread\',\'997\');">97</button>'
                        + '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'tsgread\',\'998\');">98</button><br/>'
                        + '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'tsgread\',\'999\');">99</button>'
                        + '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'tsgread\',\'1000\');">00</button>'
                        + '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'tsgread\',\'1010\');">01</button>'
                        + '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'tsgread\',\'1020\');">02</button>'
                        + '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'tsgread\',\'1030\');">03</button>'
                        + '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'tsgread\',\'1040\');">04</button>'
                        + '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'tsgread\',\'1050\');">05</button>'
                        + '';
                case 'sgbtn': 
                    return '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'sgread\',\'992\');">92</button>'
                        + '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'sgread\',\'993\');">93</button>'
                        + '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'sgread\',\'994\');">94</button>'
                        + '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'sgread\',\'995\');">95</button>'
                        + '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'sgread\',\'996\');">96</button>'
                        + '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'sgread\',\'997\');">97</button>'
                        + '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'sgread\',\'998\');">98</button><br/>'
                        + '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'sgread\',\'999\');">99</button>'
                        + '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'sgread\',\'1000\');">00</button>'
                        + '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'sgread\',\'1010\');">01</button>'
                        + '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'sgread\',\'1020\');">02</button>'
                        + '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'sgread\',\'1030\');">03</button>'
                        + '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'sgread\',\'1040\');">04</button>'
                        + '<button onclick="event.stopPropagation(); M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'sgread\',\'1050\');">05</button>'
                        + '';
                case 'rackbtn': return M.btn('Racked', 'M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'racked\');');
                case 'filterbtn': 
                    if( this.view == 'schedule' && this.scheduledate != 'today' ) {
                        return M.btn('Filter Today', 'M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'filtertoday\');');
                    } 
                    return M.btn('Filtered', 'M.ciniki_wineproduction_main.menu.actionOrder(\'' + d.id + '\',\'filtered\');');
                case 'notes': return (d.notes != null && d.notes != '' ? '*' : '');
            }
        }
    }
    this.menu.rowClass = function(s, i, d) {
        if( (s == 'today' || s == 'statuses' || s == 'reports') && d.id == this.view ) {
            return 'highlight'; 
        }
        return '';
    }
    this.menu.rowFn = function(s, i, d) {
        if( s == 'reports' && i == 'cellarnights' ) {
            return 'M.startApp(\'ciniki.wineproduction.reports\',null,\'M.ciniki_wineproduction_main.menu.open();\',\'mc\',{\'report\':\'cellarnights\'});';
        }
        if( s == 'reports' && i == 'shared' ) {
            return 'M.startApp(\'ciniki.wineproduction.reports\',null,\'M.ciniki_wineproduction_main.menu.open();\',\'mc\',{\'report\':\'shared\'});';
        }
        if( s == 'today' || s == 'statuses' || s == 'reports' ) {
            return 'M.ciniki_wineproduction_main.menu.open(null,\'' + d.id + '\');';
        }
        if( this.view == 'ctb' ) {
            return 'M.ciniki_wineproduction_main.appointment.open(\'M.ciniki_wineproduction_main.menu.open();\',\'' + d.appointment_id + '\');';
        }
        if( this.sections[s].values != null ) {
            return 'M.ciniki_wineproduction_main.order.open(\'M.ciniki_wineproduction_main.menu.open();\',\'' + d.id + '\');';
        }
    }
    this.menu.datePickerValue = function(s, d) {
        return this.workdate;
    }
    this.menu.changeDate = function(f, d) {
        this.workdate = d;
        this.open();
    }
    this.menu.switchSchedule = function(i, d) {
        this.schedulestatus = i;
        this.scheduledate = d;
        this.open();
    }
    this.menu.actionOrder = function(oid, action,sg) {
        var batch_code = '';
        if( action == 'started' ) {
            batch_code = prompt("Enter batch code", "");
            if( batch_code == null ) { // User clicked cancel
                return false;
            } 
            if( batch_code == '' ) {
                M.alert("Invalid batch code");
                return false;
            }
        }
        if( action == 'tsgread' ) {
            M.api.getJSONCb('ciniki.wineproduction.production', {'tnid':M.curTenantID, 
                'view':this.view, 'action':action, 'order_id':oid, 'tsg_reading':sg, 
                'scheduledate':this.scheduledate, 'schedulestatus':this.schedulestatus}, this.openFinish);
//            M.ciniki_wineproduction_main.sg.open('M.ciniki_wineproduction_main.menu.open();', 'tsgread', oid);
        } 
        else if( action == 'sgread' ) {
            M.api.getJSONCb('ciniki.wineproduction.production', {'tnid':M.curTenantID, 
                'view':this.view, 'action':action, 'order_id':oid, 'sg_reading':sg, 
                'scheduledate':this.scheduledate, 'schedulestatus':this.schedulestatus}, this.openFinish);
//            M.ciniki_wineproduction_main.sg.open('M.ciniki_wineproduction_main.menu.open();', 'sgread', oid);
        }
        else if( this.view == 'schedule' ) {
            M.api.getJSONCb('ciniki.wineproduction.production', {'tnid':M.curTenantID, 
                'view':this.view, 'action':action, 'order_id':oid, 'scheduledate':this.scheduledate, 'schedulestatus':this.schedulestatus}, this.openFinish);
        } else {
            M.api.getJSONCb('ciniki.wineproduction.production', {'tnid':M.curTenantID, 
                'view':this.view, 'action':action, 'order_id':oid, 'batch_code':batch_code}, this.openFinish);
        }
    }
    this.menu.open = function(cb,view) {
        if( view != null ) { this.view = view; }
        if( cb != null ) { this.cb = cb; }
        M.api.getJSONCb('ciniki.wineproduction.production', {'tnid':M.curTenantID, 'view':this.view, 'workdate':this.workdate,
            'scheduledate':this.scheduledate, 'schedulestatus':this.schedulestatus,
            }, this.openFinish);
    }
    this.menu.openFinish = function(rsp) {
        if( rsp.stat != 'ok' ) {
            M.api.err(rsp);
            return false;
        }
        var p = M.ciniki_wineproduction_main.menu;
        p.data = rsp;
        p.nplist = (rsp.nplist != null ? rsp.nplist : null);
        if( M.emWidth() < 80 ) {
            p.size = 'large';
            if( p.view == 'menu' ) {
                p.view_aside = 'yes';
                p.view_content == 'no';
            } else {
                p.view_aside = 'no';
                p.view_content == 'yes';
            }
        } else {
            if( p.view == 'menu' ) {
                p.size = 'large';
                p.view_aside = 'yes';
                p.view_content = 'no';
            } else {
                p.size = 'xlarge narrowaside';
                p.view_aside = 'yes';
                p.view_content = 'yes';
            }
        }
        p.refresh();
        p.show();
    }
    this.menu.close = function() {
        if( this.size == 'medium' && this.view != 'menu' ) {
            this.view = 'menu';
            this.view_aside = 'yes';
            this.view_content = 'no';
            this.open();
        } else {
            M.panel.prototype.close.call(this);
        }
    }
    this.menu.addButton('add', 'Add', 'M.ciniki_wineproduction_main.add.open(\'M.ciniki_wineproduction_main.menu.open();\');');
    this.menu.addClose('Back');

    //
    // The panel to display the sg reading buttons
    //
    this.sg = new M.panel('SG Reading', 'ciniki_wineproduction_main', 'sg', 'mc', 'medium', 'sectioned', 'ciniki.wineproduction.main.sg');
    this.sg.sections = {
        'sg':{'label':'What is the SG Reading?', 'type':'html', 'html':''},
//        'buttons':{'label':'', 'buttons':{
//            }},
    }
    this.sg.open = function(cb, action, id) {
        this.action = action;
        this.order_id = id;
        this.sections.sg.html = '<center>';
        for(var i = 92; i < 125; i++) {
            if( i%5 == 0 ) {
                this.sections.sg.html += '<br/><br/>';
            }
            this.sections.sg.html += '<button style="min-width:7em;margin:0.5em;" '
                + 'onclick="M.ciniki_wineproduction_main.sg.setSG(' + i + ');">' + i + '</button>'
            if( action == 'sgread' && i == 102 ) {
                break;
            }
        }
        this.sections.sg.html += '</center>';
        this.refresh();
        this.show(cb);
    }
    this.sg.setSG = function(sg) {
        M.api.getJSONCb('ciniki.wineproduction.production', {'tnid':M.curTenantID, 
            'view':M.ciniki_wineproduction_main.menu.view, 'action':this.action, 'tsg_reading':sg, 'order_id':this.order_id}, M.ciniki_wineproduction_main.menu.openFinish);
    }
    this.sg.addClose('Cancel');

    //
    // The form panel to add a new production order
    //
    this.add = new M.panel('Add Wine Order',
        'ciniki_wineproduction_main', 'add',
        'mc', 'medium', 'sectioned', 'ciniki.wineproduction.main.edit');
    this.add.data = {};
    this.add.sections = {
        'info':{'label':'', 'fields':{
            'order_date':{'label':'Ordered', 'type':'date', 'caloffset':0},
            'invoice_number':{'label':'Invoice #', 'autofocus':'yes', 'type':'text', 'size':'small'},
            'customer_id':{'label':'Customer', 'type':'fkid','size':'medium', 'livesearch':'yes'},
        }},
        'wines':{'label':'Wines', 'multi':'yes', 'multiinsert':'first', 'livesearchempty':'yes', 'fields':{
            'product_id':{'label':'Wine', 'type':'fkid', 'size':'medium', 'livesearch':'yes', 'livesearchempty':'yes'},
            'wine_type':{'label':'Type', 'hint':'red, white or other', 'type':'text', 'size':'medium'},
            'kit_length':{'label':"Kit Length", 'hint':'4, 5, 6, 8', 'type':'text', 'size':'small'},
            'order_flags':{'label':'Flags', 'type':'flags', 'join':'yes', 'flags':{}},
        }},
        'bottling':{'label':'Bottling', 'fields':{
            'bottling_duration':{'label':'Duration', 'type':'multitoggle', 'toggles':{'30':'30', '45':'45', '60':'60', '90':'90'}},
            'bottling_date':{'label':'Date', 'type':'appointment', 'caloffset':0,
                'start':{},
                'end':{},
                'interval':{},
                'notimelabel':'CTB',
                },
            'bottling_nocolour_flags':{'label':'Flags', 'join':'yes', 'toggle':'no', 'type':'flags', 'flags':{}},
            }},
        '_notes':{'label':'Notes', 'fields':{
            'notes':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'small'},
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save order', 'fn':'M.ciniki_wineproduction_main.add.save();'},
            }},
        };
    this.add.fieldValue = function(s, i, d) { 
        if( i == 'bottling_duration' ) { return '60'; }
        if( i == 'order_date' ) {
            return this.data[i];
        }
        return ''; 
    };
    this.add.setDefaultFieldValue = function(s, i, j) {
        this.data[s][j] = {};
    };
    this.add.sectionCount = function(s) {
        return this.data[s].length;
    };
    this.add.listValue = function(s, i, d) { return d.label; };
    this.add.listFn = function(s, i, d) { return d.fn; };

    this.add.liveSearchCb = function(s, i, value) {
        if( i.match(/^product_id/) ) {
            var cv = this.formValue('customer_id');
            if( value != '' || cv > 0 ) {
                var rsp = M.api.getJSONBgCb('ciniki.wineproduction.searchProductNames', 
                    {'tnid':M.curTenantID, 'customer_id':cv, 'start_needle':value, 'limit':11},
                    function(rsp) { 
                        M.ciniki_wineproduction_main.add.liveSearchShow(s, i, M.gE(M.ciniki_wineproduction_main.add.panelUID + '_' + i), rsp.names); 
                    });
            }
        }
        if( i == 'customer_id' && value != '' ) {
            var rsp = M.api.getJSONBgCb('ciniki.customers.searchQuick', {'tnid':M.curTenantID, 'start_needle':value, 'limit':25},
                function(rsp) { 
                    M.ciniki_wineproduction_main.add.liveSearchShow(s, i, M.gE(M.ciniki_wineproduction_main.add.panelUID + '_' + i), rsp.customers); 
                });
        }
    };
    this.add.liveSearchResultValue = function(s, f, i, j, d) {
        if( f.match(/^product_id/) ) { return d.name.wine_name; }
        if( f == 'customer_id') {  return d.display_name; }
        return '';
    };
    this.add.liveSearchResultRowFn = function(s, f, i, j, d) { 
        if( f.match(/^product_id/) ) {
            var x = f.replace(/^product_id_/, '');
            return 'M.ciniki_wineproduction_main.add.updateProduct(\'' + s + '\',\'' + x + '\',\'' + d.name.id + '\',\'' + escape(d.name.wine_name) + '\',\'' + d.name.wine_type + '\',\'' + d.name.kit_length + '\',\'' + d.name.order_flags + '\');';
        } else if( f == 'customer_id' ) {
            return 'M.ciniki_wineproduction_main.add.updateCustomer(\'' + s + '\',\'' + escape(d.display_name) + '\',\'' + d.id + '\');';
        }
    };
    this.add.updateCustomer = function(s, customer_name, customer_id) {
        M.gE(this.panelUID + '_customer_id').value = customer_id;
        M.gE(this.panelUID + '_customer_id_fkidstr').value = unescape(customer_name);
        this.removeLiveSearch(s, 'customer_id');
    };
    this.add.updateProduct = function(s, f, product_id, wine_name, wine_type, kit_length, order_flags) {
        M.gE(this.panelUID + '_product_id_' + f).value = product_id;
        M.gE(this.panelUID + '_product_id_' + f + '_fkidstr').value = unescape(wine_name);
        M.gE(this.panelUID + '_wine_type_' + f).value = wine_type;
        M.gE(this.panelUID + '_kit_length_' + f).value = kit_length;
        this.setFieldValue('order_flags', order_flags, null,null,f);
        this.removeLiveSearch(s, 'product_id_' + f);
    };
    this.add.liveAppointmentDayEvents = function(i, day, cb) {
        // Search for events on the specified day
        if( i == 'bottling_date' ) {
            if( day == '--' ) { day = 'today'; }
            M.api.getJSONCb('ciniki.calendars.appointments', {'tnid':M.curTenantID, 'date':day}, cb);
        }
    };
    this.add.open = function(cb) {
        // Reset form
        this.reset();
        this.data = {'wines':[]};
        var dt = new Date();
        this.data.order_date = M.dateFormat(dt);
        this.data.wines[0] = {};
        for(i=1;i<=21;i++) {
            if( this.sections['wines_' + i] != null ) {
                delete this.sections['wines_' + i];
            }
        }
        this.show(cb);
    }
    this.add.save = function() {
        // Add the customer if required
        if( this.formValue('customer_id') == 0 ) {
            var customer_name = M.gE(this.panelUID + '_customer_id_fkidstr').value;
            var rsp = M.api.getJSON('ciniki.customers.add', {'tnid':M.curTenantID, 'name':encodeURIComponent(customer_name)});
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            M.gE(this.panelUID + '_customer_id').value = rsp['id'];
        }

        var bd = M.gE(this.panelUID + '_bottling_date');
        if( bd.value.match(/ctb/i) ) {
            bd.value = '';
        }

        // Serialize the basic order information
        var content = this.serializeFormSection('yes', 'info')
            + this.serializeFormSection('yes', 'bottling')
            + this.serializeFormSection('yes', '_notes');
        if( content == '' ) {
            return false;
        }
        var wines = [];

        var c = this.sectionCount('wines');
        for(var i=0;i<c;i++) {
            if( M.gE(this.panelUID + '_product_id_' + i).value == 0 ) {
                M.alert('Invalid wine, please search for a valid wine.');
                return false;
            }
            // Check if there are multiple of this wine and add A/B/C after invoice number
            var pid = M.gE(this.panelUID + '_product_id_' + i).value;
            if( wines[pid] != null ) {
                wines[pid]['count'] += 1;
            } else {
                wines[pid] = {'count':1, 'cur':1};
            }
        }

        for(var i=0;i<c;i++) {
            // The status must be set to 10, we have removed the dropdown selection from the add form.
            var sc = this.serializeFormSection('yes', 'wines', i);
            var pid = M.gE(this.panelUID + '_product_id_' + i).value;
            if( wines[pid]['count'] > 1 ) {
                sc += '&batch_count=' + wines[pid]['cur'];
                wines[pid]['cur'] += 1;
            }
            var rsp = M.api.postJSON('ciniki.wineproduction.add', {'tnid':M.curTenantID, 'status':10}, content + sc);
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            } 
        }
        
        this.close();
    }
    this.add.addButton('save', 'Save', 'M.ciniki_wineproduction_main.add.save();');
    this.add.addClose('cancel');

    //
    // Display a current order
    //
    this.order = new M.panel('Wine Order',
        'ciniki_wineproduction_main', 'order',
        'mc', 'medium mediumaside', 'sectioned', 'ciniki.wineproduction.main.edit');
    this.order.order_id = 0;
    this.order.customer_id = 0;
    this.order.data = {};
    this.order.sections = {
        'customer':{'label':'Customer', 'aside':'yes', 'type':'simplegrid', 'num_cols':2,
            'history':'no', 
            'cellClasses':['label',''],
            'addTxt':'Edit',
            'addFn':'M.startApp(\'ciniki.customers.edit\',null,\'M.ciniki_wineproduction_main.order.open();\',\'mc\',{\'next\':\'M.ciniki_wineproduction_main.order.updateCustomer\',\'customer_id\':M.ciniki_wineproduction_main.order.data.customer_id});',
            'changeTxt':'Change customer',
            'changeFn':'M.startApp(\'ciniki.customers.edit\',null,\'M.ciniki_wineproduction_main.order.open();\',\'mc\',{\'next\':\'M.ciniki_wineproduction_main.order.updateCustomer\',\'customer_id\':0});',
        },
        'info':{'label':'', 'aside':'yes', 'fields':{
            'order_date':{'label':'Ordered', 'type':'date', 'caloffset':0},
            'invoice_number':{'label':'Invoice #', 'type':'text', 'size':'small'},
            'product_id':{'label':'Wine', 'type':'fkid', 'size':'medium', 'livesearch':'yes', 'livesearchempty':'yes'},
            'wine_type':{'label':'Type', 'type':'text', 'size':'medium'},
            'kit_length':{'label':'Kit Length', 'hint':'4, 5, 6, 8', 'type':'text', 'size':'small'},
            'order_flags':{'label':'Flags', 'join':'yes', 'type':'flags', 'flags':{}},
        }},
        'bottling':{'label':'Bottling', 'aside':'yes', 'fields':{
            'bottling_duration':{'label':'Duration', 'type':'multitoggle', 'toggles':{'30':'30', '45':'45', '60':'60', '90':'90'}},
            'bottling_date':{'label':'Date', 'type':'appointment', 'caloffset':0,
                'start':'8:00',
                'end':'20:00',
                'interval':'60',
                'notimelabel':'CTB'},
            'bottling_status':{'label':'Status', 'join':'yes', 'toggle':'yes', 'type':'flags', 'flags':{}},
        }},
        'details':{'label':'Details', 'fields':{
            'status':{'label':'Status', 'type':'select', 'options':{}},
            'rack_colour':{'label':'Rack', 'type':'colourswatches', 'colours':{},
                'visible':function() { return M.modFlagSet('ciniki.wineproduction', 0x0200); },
                },
            'filter_colour':{'label':'Filter', 'type':'colourswatches', 'colours':{},
                'visible':function() { return M.modFlagSet('ciniki.wineproduction', 0x0200); },
                },
            'location':{'label':'Location', 'type':'text', 'size':'small',
                'visible':function() { return M.modFlagSet('ciniki.wineproduction', 0x0400); },
                },
            'start_date':{'label':'Started', 'type':'date', 'caloffset':0},
            }},
        'details2':{'label':'', 'fields':{
            'tsg_reading':{'label':'Transfer SG', 'type':'text', 'size':'small',
                'visible':function() { return (M.modFlagOn('ciniki.wineproduction', 0x0800) && M.ciniki_wineproduction_main.order.data.transfer == 'yes' ? 'yes' : 'no'); },
                },
            'transferring_date':{'label':'Transferring', 'type':'date', 'caloffset':0, 'colourize':'bg',
                'visible':function() { return (M.modFlagOn('ciniki.wineproduction', 0x0800) && M.ciniki_wineproduction_main.order.data.transfer == 'yes' ? 'yes' : 'no'); },
                },
            'transfer_date':{'label':'Transferred On', 'type':'date', 'caloffset':0,
                'visible':function() { return (M.modFlagOn('ciniki.wineproduction', 0x0800) && M.ciniki_wineproduction_main.order.data.transfer == 'yes' ? 'yes' : 'no'); },
                },
            'sg_reading':{'label':'Racking SG', 'type':'text', 'size':'small', 'separator':'no'},
            'racking_date':{'label':'Racking', 'type':'date', 'caloffset':0, 'colourize':'bg'},
            'rack_date':{'label':'Racked On', 'type':'date', 'caloffset':0},
            'filtering_date':{'label':'Filtering', 'type':'date', 'caloffset':0, 'colourize':'bg', 'separator':'yes'},
            'filter_date':{'label':'Filtered On', 'type':'date', 'caloffset':0},
            'bottle_date':{'label':'Bottled', 'type':'date', 'caloffset':0},
            'batch_code':{'label':'Batch Code', 'type':'text'},
            }},
        '_notes':{'label':'Production Notes', 'fields':{
            'notes':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'small'},
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save order', 'fn':'M.ciniki_wineproduction_main.order.save();'},
            }},
        };
    this.order.sectionData = function(s) {
        return this.data[s];
    };
    this.order.calBgColour = function(i, y, m, d) {
        var c = new Date(y, m, d, 12, 0, 0, 0);
        //
        // To calculate the week for colour, start from 1970 because this avoids rollover problems in dec/jan
        // Start at Jan 18, 1970 (timestamp=1468800), so the original colour selections in 2011 work
        // Formula:
        //   (current timestamp - 1468800)/604800 = number of weeks since Jan 18, 1970
        //   then remainder of division %3 will return (0-3), which week in the colour rotation
        // 
        //
        if( i == 'racking_date' ) {
            return M.curTenants.modules['ciniki.wineproduction'].settings['racking.autocolour.week' + (Math.floor(((c.getTime()/1000) - 1468800)/604800))%3 + M.dayOfWeek(c)];
//            return M.ciniki_wineproduction_main.settings['racking.autocolour.week' + (Math.floor(((c.getTime()/1000) - 1468800)/604800))%3 + M.dayOfWeek(c)];
        } else if( i == 'filtering_date' ) {
            
            return M.ciniki_wineproduction_main.settings['filtering.autocolour.week' + (Math.floor(((c.getTime()/1000) - 1468800)/604800))%7 + M.dayOfWeek(c)];
        }
    };
    this.order.listValue = function(s, i, d) { return d['label']; };
    this.order.listFn = function(s, i, d) { return d['fn']; };
    this.order.fieldValue = function(s, i, d) { 
        if( i == 'product_id_fkidstr' ) {
            return this.data['wine_name'];
        }
        if( this.data[i] == '0000-00-00' ) {
            return '';
        } else if( this.data[i] == '0000-00-00 00:00:00' ) {
            return '';
        } 
        return this.data[i];
    };
    this.order.cellValue = function(s, i, j, d) {
        if( s == 'customer' ) {
            switch(j) {
                case 0: return d.detail.label;
                case 1: return d.detail.value;
            }
        }
    };
    this.order.liveSearchCb = function(s, i, value) {
        if( i == 'product_id' ) {
            var cv = this.data.customer_id;
            var rsp = M.api.getJSONBgCb('ciniki.wineproduction.searchProductNames', 
                {'tnid':M.curTenantID, 'customer_id':cv, 'start_needle':value, 'limit':11},
                function(rsp) { 
                    M.ciniki_wineproduction_main.order.liveSearchShow(s, i, M.gE(M.ciniki_wineproduction_main.order.panelUID + '_' + i), rsp['names']); 
                });
        }
    };
    this.order.liveSearchResultValue = this.add.liveSearchResultValue;
    this.order.liveSearchResultRowFn = function(s, f, i, j, d) { 
        if( f == 'product_id' ) {
            return 'M.ciniki_wineproduction_main.order.updateProduct(\'' + s + '\',\'' + f + '\',\'' + d.name.id + '\',\'' + escape(d.name.wine_name) + '\',\'' + d.name.wine_type + '\',\'' + d.name.kit_length + '\',\'' + d.name.order_flags + '\');';
        }
    };
    this.order.updateProduct = function(s, field, product_id, wine_name, wine_type, kit_length, order_flags) {
        M.gE(this.panelUID + '_product_id').value = product_id;
        M.gE(this.panelUID + '_product_id_fkidstr').value = unescape(wine_name);
        M.gE(this.panelUID + '_wine_type').value = wine_type;
        M.gE(this.panelUID + '_kit_length').value = kit_length;
        this.setFieldValue('order_flags', order_flags);
        this.removeLiveSearch(s, 'product_id');
    };
    this.order.fieldHistoryArgs = function(s, i) {
        return {'method':'ciniki.wineproduction.getHistory', 'args':{'tnid':M.curTenantID, 
            'wineproduction_id':this.order_id, 'field':i}};
    }
    this.order.rowFn = function(s, i, d) {
        return '';
    };
    this.order.appointmentEventText = this.add.appointmentEventText;
    this.order.appointmentColour = this.add.appointmentColour;
    this.order.liveAppointmentDayEvents = this.add.liveAppointmentDayEvents;
    this.order.open = function(cb, oid) {
        if( oid != null ) { this.order_id = oid; }
        M.api.getJSONCb('ciniki.wineproduction.getOrder', 
            {'tnid':M.curTenantID, 'wineproduction_id':this.order_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_wineproduction_main.order;
                p.data = rsp.order;
                p.customer_id = rsp.order.customer_id;
                if( rsp.order.customer_id > 0 ) {
                    p.sections.customer.addTxt = 'View Customer';
                    p.sections.customer.addFn = 'M.startApp(\'ciniki.customers.main\',null,\'M.ciniki_wineproduction_main.appointment.open();\',\'mc\',{\'next\':\'M.ciniki_wineproduction_main.appointment.updateCustomer\',\'customer_id\':M.ciniki_wineproduction_main.appointment.customer_id});';
                    p.sections.customer.changeTxt = 'Change Customer';
                    p.data.customer = rsp.order.customer;
                } else {
                    p.sections.customer.addTxt = 'Add Customer';
                    p.sections.customer.addFn = 'M.startApp(\'ciniki.customers.edit\',null,\'M.ciniki_wineproduction_main.appointment.open();\',\'mc\',{\'next\':\'M.ciniki_wineproduction_main.appointment.updateCustomer\',\'customer_id\':M.ciniki_wineproduction_main.appointment.customer_id});';
                    p.sections.customer.changeTxt = '';
                }

                p.refresh();
                p.show(cb);
            });
    }
    this.order.updateCustomer = function(cid) {
        // If the customer has changed, then update the details of the invoice
        if( cid != null && this.customer_id != cid ) {
            this.customer_id = cid;
        }
        // Update the customer details
        M.api.getJSONCb('ciniki.customers.customerDetails', {'tnid':M.curTenantID, 
            'customer_id':this.customer_id, 'phones':'yes', 'emails':'yes'}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_wineproduction_main.order;
                p.data.customer = rsp.details;
                p.refreshSection('customer');
                p.show();
            });
    };
    this.order.save = function() {
        var bd = M.gE(this.panelUID + '_bottling_date');
        if( bd.value.match(/ctb/i) ) {
            bd.value = '';
        }
        var c = this.serializeForm('no');
        // Check if customer_id has changed
        if( this.customer_id != 0 && this.data.customer_id != this.customer_id ) {
            c += 'customer_id=' + this.customer_id + '&';
        }
        if( c != '' ) {
            var rsp = M.api.postJSON('ciniki.wineproduction.update', 
                {'tnid':M.curTenantID, 'wineproduction_id':M.ciniki_wineproduction_main.order.order_id}, c);
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            } 
        }
        M.ciniki_wineproduction_main.order.close();
        // this.showOrders(null, this.list.ordertype);      
    }
    this.order.addButton('save', 'Save', 'M.ciniki_wineproduction_main.order.save();');
    this.order.addLeftButton('cancel', 'Cancel', 'M.ciniki_wineproduction_main.order.close();');

    //
    // Then to display an bottling appointment
    //
    this.appointment = new M.panel('Appointment',
        'ciniki_wineproduction_main', 'appointment',
        'mc', 'large mediumaside', 'sectioned', 'ciniki.wineproduction.main.appointment');
    this.appointment.data = null;
    this.appointment.cb = null;
    this.appointment.bottlingStatus = {};
    this.appointment.sections = {
        'customer':{'label':'Customer', 'type':'simplegrid', 'num_cols':2, 'aside':'yes',
            'cellClasses':['label',''],
            'addTxt':'Edit',
            'addFn':'M.startApp(\'ciniki.customers.main\',null,\'M.ciniki_wineproduction_main.appointment.open();\',\'mc\',{\'next\':\'M.ciniki_wineproduction_main.appointment.updateCustomer\',\'customer_id\':M.ciniki_wineproduction_main.appointment.customer_id});',
            'changeTxt':'Change customer',
            'changeFn':'M.startApp(\'ciniki.customers.edit\',null,\'M.ciniki_wineproduction_main.appointment.open();\',\'mc\',{\'next\':\'M.ciniki_wineproduction_main.appointment.updateCustomer\',\'customer_id\':0});',
        },
        'info':{'label':'Bottling Appointment', 'fields':{
            'invoice_number':{'label':'Invoice #', 'type':'noedit', 'size':'small', 'history':'no'},
            'bottling_duration':{'label':'Duration', 'type':'multitoggle', 'toggles':{'30':'30', '45':'45', '60':'60', '90':'90'}},
            'bottling_date':{'label':'Date', 'type':'appointment', 'caloffset':0,
                'start':{}, 
                'end':{}, 
                'interval':{},
                'notimelabel':'CTB',
                },
            'bottling_flags':{'label':'Flags', 'join':'yes', 'toggle':'yes', 'type':'flags', 'flags':{}},
            'bottling_nocolour_flags':{'label':'', 'join':'yes', 'toggle':'no', 'type':'flags', 'flags':{}},
            }},
        '_bottled':{'label':'', 'buttons':{
            'bottled':{'label':'Bottled', 'fn':'M.ciniki_wineproduction_main.appointment.save(\'yes\');'},
        }},
        'wines':{'label':'Wines', 'type':'simplegrid', 'num_cols':'7', 'compact_split_at':6,
            'headerValues':['INV#', 'Wine', 'OD', 'SD', 'RD', 'FD', 'Status'],
            'headerClasses':['', '', 'aligncenter', 'aligncenter', 'aligncenter', 'aligncenter', 'Status'],
            'dataMaps':['invoice_number_and_status', 'wine_name', 'order_date', 'start_date', 'racking_date', 'filtering_date', 'buttons'],
            'fields':{},
            'data':{}
        },
        '_notes':{'label':'Bottling Notes', 'fields':{
            'bottling_notes':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'small'},
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_wineproduction_main.appointment.save(\'no\');'},
        }},
    };
    this.appointment.sections = this.appointment.sections;
    this.appointment.sectionData = function(s) {
        if( s == 'wines' ) { return this.data.orders; }
        return this.data[s];
    };
    this.appointment.cellClass = function(s, i, col, d) {
        if( s == 'customer' ) {
            return this.sections[s].cellClasses[col];
        } else if( s == 'wines' && col <= 1 ) {
            return 'multiline';
        } else if( s == 'wines' && col > 1 && col < 7 ) {
            return 'multiline aligncenter';
        }
        return '';
    };
    this.appointment.rowStyle = function(s, i, d) {
        if( s == 'wines' && d.order.colour != '' ) {
            return 'background: ' + d.order.colour;
        }
        return '';
    };
    this.appointment.cellValue = function(s, i, j, d) {
        if( s == 'customer' ) {
            switch(j) {
                case 0: return d.detail.label;
                case 1: return d.detail.value;
            }
        }
        else if( s == 'wines' ) {
            if( j == 0 ) {
                return M.multiline(d.order.invoice_number, d.order.status_text);
            } else if( j == 1 ) {
                return M.multiline(d.order.wine_name, d.order.bottling_status_text);
            } else if( j > 1 && j < 7 ) {
                var dt = d['order'][this.sections[s].dataMaps[j]];
                // Check for missing filter date, and try to take a guess
                if( dt == null && j == 5 ) {
                    var dt = d['order']['approx_filtering_date'];
                    if( dt != null ) {  
                        return dt.replace(/(...)\s([0-9]+),\s([0-9][0-9][0-9][0-9])/, "<span class='maintext'>$1<\/span><span class='subtext'>~$2<\/span>");
                    }
                    return '';
                }
                if( dt != null && dt != '' ) {
                    return dt.replace(/(...)\s([0-9]+),\s([0-9][0-9][0-9][0-9])/, "<span class='maintext'>$1<\/span><span class='subtext'>$2<\/span>");
                } else {
                    return '';
                }
            }

            return d['order'][this.sections[s].dataMaps[j]];
        }
        return '';
    };
    this.appointment.rowFn = function(s, i, d) {
        if( s == 'wines' ) {
            return 'M.ciniki_wineproduction_main.order.open(\'M.ciniki_wineproduction_main.appointment.open(null, null);\', \'' + d.order.order_id + '\');'; 
        }
        return '';
    };
    this.appointment.listValue = function(s, i, d) { return d.label; };
    this.appointment.listFn = function(s, i, d) { return d.fn; };
    this.appointment.fieldValue = function(s, i, d) { 
        if( i.match(/order_/) ) {
            return d.value;
        }
        if( i == 'bottling_duration' ) {
            return this.data.orders[0].order.duration;
        }
        if( this.data[i] == '0000-00-00' ) {
            return '';
        } else if( this.data[i] == '0000-00-00 00:00:00' ) {
            return '';
        } 
        return this.data[i];
    };
    this.appointment.fieldHistoryArgs = function(s, i) {
        // Grab the order_id from the first order listed for the bottling appointment.
        return {'method':'ciniki.wineproduction.getHistory', 'args':{'tnid':M.curTenantID, 
            'wineproduction_id':this.data.orders[0].order.order_id, 'field':i}};
    }
    this.appointment.appointmentEventText = this.add.appointmentEventText;
    this.appointment.appointmentColour = this.add.appointmentColour;
    this.appointment.liveAppointmentDayEvents = this.add.liveAppointmentDayEvents;
    this.appointment.open = function(cb, aid) {
        if( aid != null ) {
            this.appointment_id = aid;
        }
        M.api.getJSONCb('ciniki.wineproduction.appointment', {'tnid':M.curTenantID, 
            'appointment_id':this.appointment_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                // If no appointment was found, close the panel
                // This happens when a bottling date on an order from an appointment panel.  
                // When they return, the appointment is no long valid, because the bottling date has changed.
                var p = M.ciniki_wineproduction_main.appointment;
                if( rsp.appointments == null ) {
                    p.close();
                }
                if( p.appointment_id != null ) {
                    p.data = rsp.appointments[0].appointment;
                }
                p.sections._bottled.visible = 'yes';
                if( rsp.appointments[0].appointment.orders[0].order.status == '60' ) {
                    p.sections._bottled.visible = 'no';
                }
                p.sections.wines.fields = {};
                for(i in rsp.appointments[0].appointment.orders) {
                    p.sections.wines.fields[i] = {'6':{'id':'order_' + rsp.appointments[0].appointment.orders[i].order.order_id + '_bottling_status', 'label':'Status', 'type':'flags', 'join':'yes', 'toggle':'yes', 'flags':p.bottlingStatus}};
                    for(var j in p.bottlingStatus) {
                        if( p.bottlingStatus[j].name == rsp.appointments[0].appointment.orders[i].order.bottling_status ) {
                            p.sections.wines.fields[i]['6']['value'] = Math.pow(2,j-1);
                        }
                    }
                }

                p.customer_id = rsp.appointments[0].appointment.customer_id;
                if( p.customer_id > 0 ) {
                    p.sections.customer.addTxt = 'View Customer';
                    p.sections.customer.addFn = 'M.startApp(\'ciniki.customers.main\',null,\'M.ciniki_wineproduction_main.appointment.open();\',\'mc\',{\'next\':\'M.ciniki_wineproduction_main.appointment.updateCustomer\',\'customer_id\':M.ciniki_wineproduction_main.appointment.customer_id});';
                    p.sections.customer.changeTxt = 'Change Customer';
                    p.data.customer = rsp.appointments[0].appointment.customer;
                } else {
                    p.sections.customer.addTxt = 'Add Customer';
                    p.sections.customer.addFn = 'M.startApp(\'ciniki.customers.edit\',null,\'M.ciniki_wineproduction_main.appointment.open();\',\'mc\',{\'next\':\'M.ciniki_wineproduction_main.appointment.updateCustomer\',\'customer_id\':M.ciniki_wineproduction_main.appointment.customer_id});';
                    p.sections.customer.changeTxt = '';
                }
                p.refreshSection('customer');
                p.refreshSection('info');
                p.refreshSection('wines');
                p.show(cb);
            });
    }

    this.appointment.updateCustomer = function(cid) {
        // If the customer has changed, then update the details of the invoice
        if( cid != null && this.customer_id != cid ) {
            this.customer_id = cid;
            var wids = '';
            var cma = '';
            for(var i in this.data.orders) {
                wids += cma + this.data.orders[i].order.order_id;
                cma = ',';
            }
            M.api.getJSONCb('ciniki.wineproduction.updateAppointment', 
                {'tnid':M.curTenantID, 'wineproduction_ids':wids, 
                'customer_id':this.customer_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } 
                    M.ciniki_wineproduction_main.appointment.updateCustomerFinish();
                });
        } else {
            this.updateCustomerFinish();
        }
    };
    this.appointment.updateCustomerFinish = function() {
        // Update the customer details
        M.api.getJSONCb('ciniki.customers.customerDetails', {'tnid':M.curTenantID, 
            'customer_id':this.customer_id, 'phones':'yes', 'emails':'yes'}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_wineproduction_main.appointment;
                p.data.customer = rsp.details;
                p.refreshSection('customer');
                p.show();
            });
    };
    this.appointment.save = function(bottled) {
        var bd = M.gE(this.panelUID + '_bottling_date');
        if( bd.value.match(/ctb/i) ) {
            bd.value = '';
        }
        var c = this.serializeForm('no');
        if( c != '' || bottled == 'yes' ) {
            var wids = '';
            var cma = '';
            for(var i in this.data.orders) {
                wids += cma + this.data.orders[i].order.order_id;
                cma = ',';
            }
            M.api.postJSONCb('ciniki.wineproduction.updateAppointment', 
                {'tnid':M.curTenantID, 'wineproduction_ids':wids, 'bottled':bottled}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } 
                    M.ciniki_wineproduction_main.appointment.close();
                });
        } else {
            this.close();
        }
    }
    this.appointment.addButton('save', 'Save', 'M.ciniki_wineproduction_main.appointment.save(\'no\');');
    this.appointment.addClose('Cancel');

    //
    // The search panel will list all search results for a string.  This allows more advanced searching,
    // and will search the entire strings, not just start of the string like livesearch
    //
    this.search = new M.panel('Search Results',
        'ciniki_wineproduction_main', 'search',
        'mc', 'xlarge', 'sectioned', 'ciniki.wineproduction.main.search');
    this.search.sections = {
        'orders':{'label':'', 'type':'simplegrid', 'num_cols':9,
            'headerValues':['Status', 'Inv#', 'Wine', 'Type', 'Ordered', 'Started', 'Racked', 'Filtered', 'Bottled'],
            'sortable':'yes',
            'sortTypes':['text','number','text','text','date','date','date','date','date'],
            'cellClasses':['', '', 'multiline', 'multiline', '', '', '', '', ''],
            },
    };
    this.search.data = {};
    this.search.noData = function() { return 'No orders found'; }
    this.search.cellValue = function(s, i, j, d) {
        switch(j) {
            case 0: return d.order.status_text;
            case 1: return d.order.invoice_number;
            case 2: return M.multiline(d.order.wine_name, d.order.customer_name);
            case 3: return M.multiline(d.order.wine_type, d.order.kit_length);
            case 4: return d.order.order_date;
            case 5: return d.order.start_date;
            case 6: return d.order.rack_date;
            case 7: return d.order.filter_date;
            case 8: return d.order.bottle_date;
        }
    }
    this.search.rowFn = function(s, i, d) { 
        return 'M.ciniki_wineproduction_main.order.open(\'M.ciniki_wineproduction_main.search.open();\',\'' + d.order.id + '\');'; 
    }
    this.search.open = function(cb,search_str) {
        if( search_str != null ) { this.search_str = search_str; }
        M.api.getJSONCb('ciniki.wineproduction.searchFull', {'tnid':M.curTenantID, 'search_str':this.search_str, 'limit':100}, function(rsp) {
            if( rsp['stat'] != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_wineproduction_main.search;
            p.data = rsp;
            p.refresh();
            p.show(cb);
        });
    }
    this.search.addClose('Back');


    //
    // Start the app
    // cb - The callback to run when the user leaves the main panel in the app.
    // ap - The application prefix.
    // ag - The app arguments.
    //
    this.start = function(cb, ap, ag) {
        args = {};
        if( ag != null ) {
            args = eval(ag);
        }
        
        //
        // Create the app container
        //
        var ac = M.createContainer(ap, 'ciniki_wineproduction_main', 'yes');
        if( ac == null ) {
            alert('App Error');
            return false;
        }

        //
        // Setup the order screen
        //
        var statusOptions = {};
        var orderFlags = {};
        var bottlingFlags = {};
        var bottlingNocolourFlags = {};
        var bottlingStatus = {};
        var rackingColours = {};
        var filteringColours = {};
        var settings = M.curTenant.modules['ciniki.wineproduction'].settings;

        if( M.modFlagOn('ciniki.wineproduction', 0x0800) ) {
            statusOptions = {
                '10':'Ordered',
                '20':'Started',
                '22':'Transfer SG Ready',
                '23':'Transferred',
                '25':'Racking SG Ready',
                '30':'Racked',
                '40':'Filtered',
                '50':'Shared',
                '60':'Bottled',
                '100':'Removed',
                '':'Unknown',
                };
        } else {
            statusOptions = {
                '10':'Ordered',
                '20':'Started',
                '25':'Racking SG Ready',
                '30':'Racked',
                '40':'Filtered',
                '50':'Shared',
                '60':'Bottled',
                '100':'Removed',
                '':'Unknown',
                };
        }

        for(i in settings) {
            if( i.match(/racking.autocolour/) ) {
                rackingColours[settings[i]] = settings[i];
            } else if( i.match(/filtering.autocolour/) ) {
                filteringColours[settings[i]] = settings[i];
            }
        }

        for(i=1;i<12;i++) {
            if( settings['order.flags.' + i + '.name'] != null && settings['order.flags.' + i + '.name'] != '' ) {
                orderFlags[i] = {'name':settings['order.flags.' + i + '.name'],
                    'bgcolour':settings['order.flags.' + i + '.colour'],
                    'fontcolour':settings['order.flags.' + i + '.fontcolour']};
            }
            if( settings['bottling.flags.' + i + '.name'] != null && settings['bottling.flags.' + i + '.name'] != '' ) {
                bottlingFlags[i] = {'name':settings['bottling.flags.' + i + '.name'],
                    'bgcolour':settings['bottling.flags.' + i + '.colour'],
                    'fontcolour':settings['bottling.flags.' + i + '.fontcolour']};
            }
            if( settings['bottling.nocolour.flags.' + i + '.name'] != null && settings['bottling.nocolour.flags.' + i + '.name'] != '' ) {
                bottlingNocolourFlags[i] = {'name':settings['bottling.nocolour.flags.' + i + '.name']};
            }
            if( settings['bottling.status.' + i + '.name'] != null && settings['bottling.status.' + i + '.name'] != '' ) {
                bottlingStatus[i] = {'name':settings['bottling.status.' + i + '.name'],
                    'bgcolour':settings['bottling.status.' + i + '.colour'],
                    'fontcolour':settings['bottling.status.' + i + '.fontcolour']};
            }
        }
        this.order.sections.info.fields.order_flags.flags = orderFlags;
        this.order.sections.bottling.fields.bottling_date.start = settings['bottling.schedule.start'];
        this.order.sections.bottling.fields.bottling_date.end = settings['bottling.schedule.end'];
        this.order.sections.bottling.fields.bottling_date.interval = settings['bottling.schedule.interval'];
        this.order.sections.bottling.fields.bottling_status.flags = bottlingStatus;
        this.order.sections.details.fields.status.options = statusOptions;
        this.order.sections.details.fields.rack_colour.colours = rackingColours;
        this.order.sections.details.fields.filter_colour.colours = filteringColours;

        this.add.sections.wines.fields.order_flags.flags = orderFlags;
        this.add.sections.bottling.fields.bottling_date.start = settings['bottling.schedule.start'];
        this.add.sections.bottling.fields.bottling_date.end = settings['bottling.schedule.end'];
        this.add.sections.bottling.fields.bottling_date.interval = settings['bottling.schedule.interval'];
        this.add.sections.bottling.fields.bottling_nocolour_flags.flags = bottlingNocolourFlags;
        
        this.appointment.sections.info.fields.bottling_date.start = settings['bottling.schedule.start'];
        this.appointment.sections.info.fields.bottling_date.end = settings['bottling.schedule.end'];
        this.appointment.sections.info.fields.bottling_date.interval = settings['bottling.schedule.interval'];
        this.appointment.sections.info.fields.bottling_flags.flags = bottlingFlags;
        this.appointment.sections.info.fields.bottling_nocolour_flags.flags = bottlingNocolourFlags;
        this.appointment.bottlingStatus = bottlingStatus;
    
        if( M.modFlagOn('ciniki.wineproduction', 0x0200) ) {
            this.order.sections.details2.fields.racking_date.colourize = 'bg';
            this.order.sections.details2.fields.filtering_date.colourize = 'bg';
        } else {
            this.order.sections.details2.fields.racking_date.colourize = '';
            this.order.sections.details2.fields.filtering_date.colourize = '';
        }
        //
        // Check for transfers and colour options to setup offset for fields in table.
        //
        if( M.modFlagOn('ciniki.wineproduction', 0x0a00) ) {
            this.menu.offset = 'both';
        }
        else if( M.modFlagOn('ciniki.wineproduction', 0x0200) ) {
            this.menu.offset = 'colours';
        }
        else if( M.modFlagOn('ciniki.wineproduction', 0x0800) ) {
            this.menu.offset = 'location';
        }
        else {
            this.menu.offset = 'none';
        }

        if( args.search != null && args.search != '' ) {
            this.search.open(cb, args.search);
        }
        else if( args.appointment_id && args.appointment_id != '' ) {
            this.appointment.open(cb, args.appointment_id);
        }
        else if( args.order_id != null && args.order_id > 0 ) {
            this.order.open(cb, args.order_id);
        }
        else if( args.add != null && args.add == 'yes' ) {
            this.add.open(cb);
        } 
        else if( args.ctb != null && args.ctb == 'yes' ) {

        }
        else if( args.schedule != null && args.schedule == 'today' ) {
            this.menu.scheduledate = 'today';
            this.menu.view = 'schedule';
            this.menu.open(cb);
        }
        else {
            this.menu.open(cb);
        }
    }
}
