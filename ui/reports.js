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
        'mc', 'xlarge', 'sectioned', 'ciniki.wineproduction.reports.cellarnights');
    this.cellarnights.sections = {
        '_years':{'label':'', 'type':'paneltabs', 'selected':'', 'tabs':{}, 'visible':'no'},
        'orders':{'label':'Orders', 'type':'simplegrid', 'num_cols':6, 
            'headerValues':['Invoice #', 'Customer', 'Product', 'Status', 'Bottling Date', 'Bottling Status'],
            'sortable':'yes',
            'sortTypes':['number', 'text', 'text', 'altnumber', 'date', 'altnumber'],
            },
    };
    this.cellarnights.sectionData = function(s) { return this.data[s]; }
    this.cellarnights.cellValue = function(s, i, j, d) {
        switch (j) {
            case 0: return d.invoice_number;
            case 1: return d.display_name;
            case 2: return d.product_name;
            case 3: return d.status_text;
            case 4: return d.bottling_date;
            case 5: return d.bottling_status_text;
        }
        return '';
    }
    this.cellarnights.cellSortValue = function(s, i, j, d) {
        switch (j) {
            case 0: return d.invoice_number;
            case 1: return d.display_name;
            case 2: return d.product_name;
            case 3: return d.status;
            case 4: return d.bottling_date;
            case 5: return d.bottling_status;
        }
    }
    this.cellarnights.rowFn = function(s, i, d) {
        return 'M.startApp(\'ciniki.wineproduction.main\',null,\'M.ciniki_wineproduction_reports.cellarnights.open();\',\'mc\',{\'order_id\':\'' + d.id + '\'});';
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
                    console.log(rsp.years[i]);
                    p.sections._years.tabs[rsp.years[i]] = {'label':rsp.years[i], 'fn':'M.ciniki_wineproduction_reports.cellarnights.switchYear(' + rsp.years[i] + ');'};
                }
                if( rsp.years.length > 1 ) {
                    p.sections._years.visible = 'yes';
                }
            }
            console.log(p.sections);
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
