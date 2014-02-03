function ciniki_wineproduction_customer() {
	this.init = function() {
		this.orders = new M.panel('Customer Orders',
			'ciniki_wineproduction_customer', 'orders',
			'mc', 'medium', 'sectioned', 'ciniki.wineproduction.customer.orders');
		this.orders.customer_id = 0;
		this.orders.year = null;
		this.orders.month = 0;
		this.orders.status = 0;
		this.orders.data = {};
		this.orders.sections = {
//			'years':{'label':'', 'type':'paneltabs', 'selected':'', 'tabs':{}},
//			'months':{'label':'', 'visible':'no', 'type':'paneltabs', 'selected':'0', 'tabs':{
//				'0':{'label':'All', 'fn':'M.ciniki_sapos_invoices.showInvoices(null,null,0);'},
//				'1':{'label':'Jan', 'fn':'M.ciniki_sapos_invoices.showInvoices(null,null,1);'},
//				'2':{'label':'Feb', 'fn':'M.ciniki_sapos_invoices.showInvoices(null,null,2);'},
//				'3':{'label':'Mar', 'fn':'M.ciniki_sapos_invoices.showInvoices(null,null,3);'},
//				'4':{'label':'Apr', 'fn':'M.ciniki_sapos_invoices.showInvoices(null,null,4);'},
//				'5':{'label':'May', 'fn':'M.ciniki_sapos_invoices.showInvoices(null,null,5);'},
//				'6':{'label':'Jun', 'fn':'M.ciniki_sapos_invoices.showInvoices(null,null,6);'},
//				'7':{'label':'Jul', 'fn':'M.ciniki_sapos_invoices.showInvoices(null,null,7);'},
//				'8':{'label':'Aug', 'fn':'M.ciniki_sapos_invoices.showInvoices(null,null,8);'},
//				'9':{'label':'Sep', 'fn':'M.ciniki_sapos_invoices.showInvoices(null,null,9);'},
//				'10':{'label':'Oct', 'fn':'M.ciniki_sapos_invoices.showInvoices(null,null,10);'},
//				'11':{'label':'Nov', 'fn':'M.ciniki_sapos_invoices.showInvoices(null,null,11);'},
//				'12':{'label':'Dec', 'fn':'M.ciniki_sapos_invoices.showInvoices(null,null,12);'},
//				}},
//			'statuses':{'label':'', 'visible':'yes', 'type':'paneltabs', 'selected':'0', 'tabs':{
//				'0':{'label':'All', 'fn':'M.ciniki_sapos_customerinvoices.showInvoices(null,null,null,null,0);'},
//				'20':{'label':'Payment Required', 'fn':'M.ciniki_sapos_customerinvoices.showInvoices(null,null,null,null,20);'},
//				'40':{'label':'Deposit', 'fn':'M.ciniki_sapos_customerinvoices.showInvoices(null,null,null,null,40);'},
//				'50':{'label':'Paid', 'fn':'M.ciniki_sapos_customerinvoices.showInvoices(null,null,null,null,50);'},
//				'55':{'label':'Refunded', 'fn':'M.ciniki_sapos_customerinvoices.showInvoices(null,null,null,null,55);'},
//				'60':{'label':'Void', 'fn':'M.ciniki_sapos_customerinvoices.showInvoices(null,null,null,null,60);'},
//				}},
			'customer':{'label':'Customer', 'type':'simplegrid', 'num_cols':2,
				'cellClasses':['label',''],
				},
			'orders':{'label':'Orders', 'type':'simplegrid', 'num_cols':5,
				'sortable':'yes',
				'headerValues':['INV#', 'Status', 'Wine', 'Ordered', 'Bottled'],
				'cellClasses':['', '', '', '', ''],
				'sortTypes':['number', 'altnumber', 'text', 'date', 'date'],
				'dataMaps':['invoice_number', 'status_text', 'wine_name', 'order_date', 'bottling_date'],
				'noData':'No orders found',
				},
		};
		this.orders.sectionData = function(s) {
			return this.data[s];
		};
		this.orders.noData = function(s) {
			return this.sections[s].noData;
		};
		this.orders.cellSortValue = function(s, i, j, d) {
			return d.order.status;
		};
		this.orders.cellValue = function(s, i, j, d) {
			if( s == 'customer' ) {
				switch(j) {
					case 0: return d.detail.label;
					case 1: return d.detail.value.replace(/\n/, '<br/>');
				}
			}
			if( s == 'orders' ) {
//				if( j == 0 ) {
//					return '<span class="maintext">' + d.order.invoice_number + '</span><span class="subtext">' + d.order.status_text + '</span>';
//				}
				return d.order[this.sections[s].dataMaps[j]];
			}
		};
		
		this.orders.rowFn = function(s, i, d) {
			if( s == 'orders' ) {
				return 'M.startApp(\'ciniki.wineproduction.main\',null,\'M.ciniki_wineproduction_customer.showOrders();\',\'mc\',{\'order_id\':' + d.order.id + '});';
			}
		};
		this.orders.addClose('Back');
	};

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
		var appContainer = M.createContainer(appPrefix, 'ciniki_wineproduction_customer', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		} 
		this.showOrders(cb, args.customer_id);
	};

	this.showOrders = function(cb, cid) {
		if( cid != null ) { this.orders.customer_id = cid; }
		M.api.getJSONCb('ciniki.wineproduction.customerOrders', {'business_id':M.curBusinessID,
			'customer_id':this.orders.customer_id}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_wineproduction_customer.orders;
				p.data = rsp;
				p.refresh();
				p.show(cb);
			});
	};
}
