//
function ciniki_wineproduction_reports() {
    //
    // Panels
    //
    this.init = function() {
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
            return 'M.startApp(\'ciniki.customers.main\',null,\'M.ciniki_wineproduction_reports.show();\',\'mc\',{\'customer_id\':\'' + d.customer_id + '\'});';
        }
        this.yearly.addClose('Back');
    }

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

        this.yearlyShow(cb);
    };

    this.yearlyShow = function(cb) {
        M.api.getJSONCb('ciniki.wineproduction.reportCustomersYearly', {'business_id':M.curBusinessID}, function(rsp) {
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
}
