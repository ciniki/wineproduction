//
function ciniki_wineproduction_reports() {
    //
    // The yearly panel, which lists the customers and the years they made wine, along with percentage increase or decrease between years
    //
    this.yearly = new M.panel('Customers Yearly Production',
        'ciniki_wineproduction_reports', 'yearly',
        'mc', 'large', 'sectioned', 'ciniki.wineproduction.reports.yearly');
    this.yearly.sections = {
        'customers':{},
    };
    this.yearly.sectionData = function(s) { return this.data[s]; }
    this.yearly.cellValue = function(s, i, j, d) {
        if( j == 0 ) {
            return d.display_name;
        }
        if( j > 2 && j%2 == 0 && d.years[this.sections[s].headerValues[j-3]] != null && d.years[this.sections[s].headerValues[j-1]] != null ) {
            return d.years[this.sections[s].headerValues[j-1]].pi + '%';
        } else if( j%2 == 1 && d.years[this.sections[s].headerValues[j]] != null ) {
            return d.years[this.sections[s].headerValues[j]].num_orders;
        }
        return '';
    }
    this.yearly.cellSortValue = function(s, i, j, d) {
        if( j == 0 ) {
            return d.display_name;
        }
        if( j > 2 && j%2 == 0 && d.years[this.sections[s].headerValues[j-3]] != null && d.years[this.sections[s].headerValues[j-1]] != null ) {
            return d.years[this.sections[s].headerValues[j-1]].pi;
        } else if( j%2 == 1 && d.years[this.sections[s].headerValues[j]] != null ) {
            return d.years[this.sections[s].headerValues[j]].num_orders;
        }
        return -0.1;
    }

    this.yearly.rowFn = function(s, i, d) {
        return 'M.startApp(\'ciniki.customers.main\',null,\'M.ciniki_wineproduction_reports.yearly.open();\',\'mc\',{\'customer_id\':\'' + d.customer_id + '\'});';
    }
    this.yearly.open = function(cb) {
        M.api.getJSONCb('ciniki.wineproduction.reportCustomersYearly', {'tnid':M.curTenantID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            } 
            var p = M.ciniki_wineproduction_reports.yearly;
            p.data = rsp;
            p.sections.customers = {'label':rsp.start_year + ' - ' + rsp.end_year, 
                'type':'simplegrid',
//                'num_cols':((rsp.start_year-rsp.end_year+1)*2)+1,
                'num_cols':1,
                'sortable':'yes',
                'sortTypes':['text'],
                'headerValues':['Customer'],
            };
            for(var i = rsp.start_year;i<=rsp.end_year;i++) {
                p.sections.customers.num_cols+=2;
                p.sections.customers.sortTypes.push('number');
                p.sections.customers.sortTypes.push('altnumber');
                p.sections.customers.headerValues.push(i);
                p.sections.customers.headerValues.push('%');
            }

            p.refresh();
            p.show(cb);
        });
    };
    this.yearly.addClose('Back');

    //
    // The cellarnights panel, which lists the customers and the years they made wine, along with percentage increase or decrease between years
    //
    this.cellarnights = new M.panel('Cellar Nights Orders',
        'ciniki_wineproduction_reports', 'cellarnights',
        'mc', 'full', 'sectioned', 'ciniki.wineproduction.reports.cellarnights');
    this.cellarnights.sections = {
        '_years':{'label':'', 'type':'paneltabs', 'selected':'', 'tabs':{}, 'visible':'no'},
        'orders':{'label':'Orders', 'type':'simplegrid', 'num_cols':9,
            'headerValues':['Invoice #', 'Product', 'Status', 'Customer A', 'Bottling A', 'Customer B', 'Bottling B', 'Customer C', 'Bottling C'],
            'cellClasses':['', '', '', 'multiline', 'multiline', 'multiline', 'multiline', 'multiline', 'multiline'],
            'sortable':'yes',
            'sortTypes':['number', 'text', 'text', 'text', 'text', 'text', 'text', 'text', 'text'],
            },
        'badorders':{'label':'Bad Orders', 'type':'simplegrid', 'num_cols':6, 
            'headerValues':['Invoice #', 'Customer', 'Product', 'Status', 'Bottling Date', 'Bottling Status'],
            'sortable':'yes',
            'sortTypes':['number', 'text', 'text', 'altnumber', 'date', 'altnumber'],
            },
    };
    this.cellarnights.sectionData = function(s) { return this.data[s]; }
    this.cellarnights.cellValue = function(s, i, j, d) {
        if( s == 'orders' ) {
            switch (j) {
                case 0: return d.invoice_number;
                case 1: return d.product_name;
                case 2: return d.status_text;
                case 3: return '<span class="maintext">' + d.display_name + '</span>'
                    + '<span class="subtext">' + d.invoice_number + '</span>';
                case 4: return '<span class="maintext">' + d.bottling_status_text + '</span>'
                    + '<span class="subtext">' + d.bottling_date + '</span>';
                case 5: return '<span class="maintext">' + d.B.display_name + '</span>'
                    + '<span class="subtext">' + d.B.invoice_number + '</span>';
                case 6: return '<span class="maintext">' + d.B.bottling_status_text + '</span>'
                    + '<span class="subtext">' + d.B.bottling_date + '</span>';
                case 7: return '<span class="maintext">' + d.C.display_name + '</span>'
                    + '<span class="subtext">' + d.C.invoice_number + '</span>';
                case 8: return '<span class="maintext">' + d.C.bottling_status_text + '</span>'
                    + '<span class="subtext">' + d.C.bottling_date + '</span>';
            }
        } 
        if( s == 'badorders' ) {
            switch (j) {
                case 0: return d.invoice_number;
                case 1: return d.display_name;
                case 2: return d.product_name;
                case 3: return d.status_text;
                case 4: return d.bottling_date;
                case 5: return d.bottling_status_text;
            }
        }
        return '';
    }
    this.cellarnights.cellFn = function(s, i, j, d) {
        if( s == 'orders' ) {
            switch (j) {
                case 0:
                case 1:
                case 2: 
                case 3:
                case 4: return 'M.startApp(\'ciniki.wineproduction.main\',null,\'M.ciniki_wineproduction_reports.cellarnights.open();\',\'mc\',{\'order_id\':\'' + d.id + '\'});';
                case 5:
                case 6: return 'M.startApp(\'ciniki.wineproduction.main\',null,\'M.ciniki_wineproduction_reports.cellarnights.open();\',\'mc\',{\'order_id\':\'' + d.B.id + '\'});';
                case 7:
                case 8: return 'M.startApp(\'ciniki.wineproduction.main\',null,\'M.ciniki_wineproduction_reports.cellarnights.open();\',\'mc\',{\'order_id\':\'' + d.C.id + '\'});';
            }
        }
        return '';
    }
    this.cellarnights.cellClass = function(s, i, j, d) {
        if( s == 'orders' ) {
            if( ((j == 3 || j == 4) && d.status == 60) 
                || ((j == 5 || j == 6) && d.B.status == 60) 
                || ((j == 7 || j == 8) && d.C.status == 60) 
                ) {
                return 'multiline statusgrey';
            }
            if( ((j == 3 || j == 4) && d.bottling_status > 0 && d.bottling_status < 128 ) 
                || ((j == 5 || j == 6) && d.B.bottling_status > 0 && d.B.bottling_status < 128 ) 
                || ((j == 7 || j == 8) && d.C.bottling_status > 0 && d.C.bottling_status < 128 ) 
                ) {
                return 'multiline statusorange';
            }
            if( ((j == 3 || j == 4) && d.bottling_status == 128 ) 
                || ((j == 5 || j == 6) && d.B.bottling_status == 128 ) 
                || ((j == 7 || j == 8) && d.C.bottling_status == 128 ) 
                ) {
                return 'multiline statusgreen';
            }
        }
        if( this.sections[s].cellClasses != null ) {
            return this.sections[s].cellClasses[j];
        } 
        return '';
    }
    this.cellarnights.cellSortValue = function(s, i, j, d) {
        switch (j) {
            case 0: return d.invoice_number;
            case 1: return d.product_name;
            case 2: return d.status;
            case 3: return d.display_name;
            case 4: return d.bottling_date;
            case 5: return d.bottling_status;
        }
    }
    this.cellarnights.rowFn = function(s, i, d) {
        if( s == 'badorders' ) {
            return 'M.startApp(\'ciniki.wineproduction.main\',null,\'M.ciniki_wineproduction_reports.cellarnights.open();\',\'mc\',{\'order_id\':\'' + d.id + '\'});';
        }
        return '';
    }
    this.cellarnights.switchYear = function(y) {
        this.sections._years.selected = y;
        this.open();
    }
    this.cellarnights.open = function(cb) {
        M.api.getJSONCb('ciniki.wineproduction.reportCellarNights', {'tnid':M.curTenantID, 'year':this.sections._years.selected}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            } 
            var p = M.ciniki_wineproduction_reports.cellarnights;
            p.data = rsp;
            p.sections._years.visible = 'no';
            p.sections._years.tabs = {};
            p.sections._years.selected = year = rsp.year;
            if( rsp.years != null && rsp.years != '' ) {
                var i = 0;
                for(i in rsp.years) {
                    p.sections._years.tabs[rsp.years[i]] = {'label':rsp.years[i], 'fn':'M.ciniki_wineproduction_reports.cellarnights.switchYear(' + rsp.years[i] + ');'};
                }
                if( rsp.years.length > 1 ) {
                    p.sections._years.visible = 'yes';
                }
            }
            p.refresh();
            p.show(cb);
        });
    };
    this.cellarnights.addClose('Back');

    //
    // Arguments:
    // aG - The arguments to be parsed into args
    //
    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) { args = eval(aG); }

        //
        // Create the app container if it doesn't exist, and clear it out
        // if it does exist.
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_wineproduction_reports', 'yes');
        if( appContainer == null ) {
            alert('App Error');
            return false;
        } 

        if( args.report != null && args.report == 'cellarnights' ) {
            this.cellarnights.open(cb);    
        } else {
            this.yearly.open(cb);
        }
    };

}
