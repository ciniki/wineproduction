//
function ciniki_wineproduction_main() {
	//
	// Panels
	//
	this.main = null;
	this.original_add = null;
	this.add = null;

	this.cb = null;

	//
	// FIXME: Add code to grab the list of colours and status codes from API 
	//        which are specific to this business
	//
	this.statusOptions = {
		'10':'Ordered',
		'20':'Started',
		'25':'SG Ready',
		'30':'Racked',
		'40':'Filtered',
		'60':'Bottled',
		'100':'Removed',
		'*':'Unknown',
		};

	this.init = function() {
		//
		// Load the possible colours
		//
	};
	
	this.initStart = function() {
		var rsp = M.api.getJSONCb('ciniki.wineproduction.settingsGet', 
			{'business_id':M.curBusinessID}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var o = M.ciniki_wineproduction_main;
				o.settings = rsp.settings;
				o.orderColours = {'#ffffff':1};
				o.rackingColours = {'#ffffff':1};
				o.filteringColours = {'#ffffff':1};
				o.orderFlags = {};
				o.bottlingFlags = {};
				o.bottlingNocolourFlags = {};
				o.bottlingStatus = {};


				//
				// Setup the list of colours used for orders, racking and filtering
				//
				for(i in rsp.settings) {
					if( i.match(/order.colourtags/) ) {
						o.orderColours[rsp.settings[i]] = rsp.settings[i];
					} else if( i.match(/racking.autocolour/) ) {
						o.rackingColours[rsp.settings[i]] = rsp.settings[i];
					} else if( i.match(/filtering.autocolour/) ) {
						// this.filteringColours[] = rsp.settings[i];
						o.filteringColours[rsp.settings[i]] = rsp.settings[i];
					}
				}
				o.dateToday = rsp.date_today;

				for(i=1;i<9;i++) {
					if( rsp.settings['order.flags.' + i + '.name'] != null && rsp.settings['order.flags.' + i + '.name'] != '' ) {
						o.orderFlags[i] = {'name':rsp.settings['order.flags.' + i + '.name'],
							'bgcolour':rsp.settings['order.flags.' + i + '.colour'],
							'fontcolour':rsp.settings['order.flags.' + i + '.fontcolour']};
					}
					if( rsp.settings['bottling.flags.' + i + '.name'] != null && rsp.settings['bottling.flags.' + i + '.name'] != '' ) {
						o.bottlingFlags[i] = {'name':rsp.settings['bottling.flags.' + i + '.name'],
							'bgcolour':rsp.settings['bottling.flags.' + i + '.colour'],
							'fontcolour':rsp.settings['bottling.flags.' + i + '.fontcolour']};
					}
					if( rsp.settings['bottling.nocolour.flags.' + i + '.name'] != null && rsp.settings['bottling.nocolour.flags.' + i + '.name'] != '' ) {
						o.bottlingNocolourFlags[i] = {'name':rsp.settings['bottling.nocolour.flags.' + i + '.name']};
					}
					if( rsp.settings['bottling.status.' + i + '.name'] != null && rsp.settings['bottling.status.' + i + '.name'] != '' ) {
						o.bottlingStatus[i] = {'name':rsp.settings['bottling.status.' + i + '.name'],
							'bgcolour':rsp.settings['bottling.status.' + i + '.colour'],
							'fontcolour':rsp.settings['bottling.status.' + i + '.fontcolour']};
					}
				}

				o.bottlingDurations = {'30':'30', '45':'45', '60':'60', '90':'90'};

				M.ciniki_wineproduction_main.initFinish();
			});
	};

	this.initFinish = function() {
		//
		// The main panel, which lists the options for production
		//
		this.main = new M.panel('Wine Production',
			'ciniki_wineproduction_main', 'main',
			'mc', 'medium', 'sectioned', 'ciniki.wineproduction.main');
		this.main.sections = {
			'search':{'label':'Search', 'type':'livesearchgrid', 'livesearchcols':8, 'hint':'inv#, customer or product',
				'headerValues':['INV#', 'Wine', 'Type', 'BD', 'OD', 'SD', 'RD', 'FD'],
				'dataMaps':['invoice_number', 'wine_and_customer', 'wine_type_and_length', 'bottling_date', 'order_date', 'start_date', 'racking_date', 'filtering_date'],
				'noData':'No active orders found',
				},
			'today':{'label':'Todays Production', 'type':'simplelist', 'list':{
				'start':{'label':'Starting', 'precount':'no', 'count':0, 'fn':'M.ciniki_wineproduction_main.showOrders(null, \'start\');'},
				'todayssgreadings':{'label':'SG Readings', 'precount':'no', 'count':0, 'fn':'M.ciniki_wineproduction_main.showOrders(null, \'todayssgreadings\');'},
				'todaysracking':{'label':'Racking', 'precount':'no', 'count':0, 'fn':'M.ciniki_wineproduction_main.showOrders(null, \'todaysracking\');'},
				'todaysfiltering':{'label':'Filtering', 'precount':'no', 'count':0, 'fn':'M.ciniki_wineproduction_main.showOrders(null, \'todaysfiltering\');'},
				}},
			'orders':{'label':'Orders', 'type':'simplelist', 'list':{
				'ordered':{'label':'Ordered', 'count':0, 'fn':'M.ciniki_wineproduction_main.showOrders(null, \'ordered\');'},
				'started':{'label':'Started', 'count':0, 'fn':'M.ciniki_wineproduction_main.showOrders(null, \'started\');'},
				'sgready':{'label':'SG Ready', 'count':0, 'fn':'M.ciniki_wineproduction_main.showOrders(null, \'sgready\');'},
				'racked':{'label':'Racked', 'count':0, 'fn':'M.ciniki_wineproduction_main.showOrders(null, \'racked\');'},
				'filtered':{'label':'Filtered', 'count':0, 'fn':'M.ciniki_wineproduction_main.showOrders(null, \'filtered\');'},
				}},
			'reports':{'label':'Reports', 'type':'simplelist', 'list':{
				'schedule':{'label':'Production Schedule', 'fn':'M.ciniki_wineproduction_main.showSchedule(\'M.ciniki_wineproduction_main.showMain();\',null,\'today\');'},
				'workdone':{'label':'Work Completed', 'count':0, 'fn':'M.ciniki_wineproduction_main.showWorkDone(\'today\', \'all\');'},
				'latewines':{'label':'Late Wines', 'count':0, 'fn':'M.ciniki_wineproduction_main.showOrders(null, \'latewines\');'},
				'ctb':{'label':'Call to Book', 'count':0, 'fn':'M.ciniki_wineproduction_main.showOrders(\'M.ciniki_wineproduction_main.showMain();\', \'ctb\');'},
				'export':{'label':'Export Orders', 'fn':'M.ciniki_wineproduction_main.downloadXLS();'},
				}},
			};
		this.main.listValue = function(s, i, d) { 
			if( d.count != null && d.precount != null && d.precount == 'yes' ) {
				return d.count + ' ' + d.label + ''; 
			} else {
				return d.label;
			}
		};
		this.main.listFn = function(s, i, d) { 
			if( d.fn != null ) { return d.fn; } 
			return '';
		};
		this.main.fieldValue = function(s, i, d) { return ''; }
		// FIXME: Also resides in core/js/menu.js
		this.main.liveSearchCb = function(s, i, value) {
			if( s == 'search' && value != '' ) {
				M.api.getJSONBgCb('ciniki.wineproduction.searchQuick', {'business_id':M.curBusinessID, 'start_needle':value, 'limit':'10'}, 
					function(rsp) { 
						M.ciniki_wineproduction_main.main.liveSearchShow('search', null, M.gE(M.ciniki_wineproduction_main.main.panelUID + '_' + s), rsp.orders); 
					});
				return true;
			}
		};
		this.main.liveSearchResultClass = function(s, f, i, j, d) {
			if( j > 2 ) { return 'multiline aligncenter'; }
			return 'multiline';
		};
		this.main.liveSearchResultValue = function(s, f, i, j, d) {
			if( s == 'search' ) {
				if( j == 0 ) {
					return "<span class='maintext'>" + d.order.invoice_number + "</span>" + "<span class='subtext'>" + M.ciniki_wineproduction_main.statusOptions[d.order.status] + "</span>";
				}
				if( this.sections[s].dataMaps[j] == 'wine_and_customer' ) {
					return "<span class='maintext'>" + d.order.wine_name + "</span>" + "<span class='subtext'>" + d.order.customer_name + "</span>";
				} else if( this.sections[s].dataMaps[j] == 'wine_type_and_length' ) {
					return "<span class='maintext'>" + d.order.wine_type + "</span>" + "<span class='subtext'>" + d.order.kit_length + "&nbsp;weeks</span>";
				} else if( this.sections[s].dataMaps[j] == 'order_date' 
					|| this.sections[s].dataMaps[j] == 'start_date' 
					|| this.sections[s].dataMaps[j] == 'racking_date' 
					|| this.sections[s].dataMaps[j] == 'filtering_date' 
					|| this.sections[s].dataMaps[j] == 'bottling_date' 
					|| this.sections[s].dataMaps[j] == 'bottling_date_and_flags' 
					) {
					var dt = d.order[this.sections[s].dataMaps[j]];
					// Check for missing filter date, and try to take a guess
					if( dt != null && dt != '' ) {
						return dt.replace(/(...)\s([0-9]+),\s([0-9][0-9][0-9][0-9])/, "<span class='maintext'>$1<\/span><span class='subtext'>$2<\/span>");
					} else {
						return '';
					}
				}
				return d.order[this.sections[s].dataMaps[j]];
			}
			return '';
		}
		this.main.liveSearchResultRowFn = function(s, f, i, j, d) { 
			return 'M.ciniki_wineproduction_main.showOrder(\'' + d.order.id + '\', \'M.ciniki_wineproduction_main.main.show();\');'; 
		};
		this.main.liveSearchSubmitFn = function(s, search_str) {
			M.ciniki_wineproduction_main.search.cb = 'M.ciniki_wineproduction_main.showMain();';
			M.ciniki_wineproduction_main.searchOrders(search_str);
		};

		this.main.addButton('add', 'Add', 'M.ciniki_wineproduction_main.showAdd(\'M.ciniki_wineproduction_main.showMain();\');');
		this.main.addClose('Back');

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
				'order_flags':{'label':'Flags', 'type':'flags', 'join':'yes', 'flags':this.orderFlags},
			}},
			'bottling':{'label':'Bottling', 'fields':{
				'bottling_duration':{'label':'Duration', 'type':'multitoggle', 'toggles':this.bottlingDurations},
				'bottling_date':{'label':'Date', 'type':'appointment', 'caloffset':0,
					'start':this.settings['bottling.schedule.start'], 
					'end':this.settings['bottling.schedule.end'], 
					'interval':this.settings['bottling.schedule.interval'],
					'notimelabel':'CTB',
					},
				'bottling_nocolour_flags':{'label':'Flags', 'join':'yes', 'toggle':'no', 'type':'flags', 'flags':this.bottlingNocolourFlags},
				}},
			'_notes':{'label':'Notes', 'fields':{
				'notes':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'small'},
				}},
			'_buttons':{'label':'', 'buttons':{
				'save':{'label':'Save order', 'fn':'M.ciniki_wineproduction_main.addOrder();'},
				}},
			};
		this.add.fieldValue = function(s, i, d) { 
			if( i == 'bottling_duration' ) { return '60'; }
			if( i == 'order_date' ) {
				return M.ciniki_wineproduction_main.dateToday;
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
						{'business_id':M.curBusinessID, 'customer_id':cv, 'start_needle':value, 'limit':11},
						function(rsp) { 
							M.ciniki_wineproduction_main.add.liveSearchShow(s, i, M.gE(M.ciniki_wineproduction_main.add.panelUID + '_' + i), rsp.names); 
						});
				}
			}
			if( i == 'customer_id' && value != '' ) {
				var rsp = M.api.getJSONBgCb('ciniki.customers.searchQuick', {'business_id':M.curBusinessID, 'start_needle':value, 'limit':25},
					function(rsp) { 
						M.ciniki_wineproduction_main.add.liveSearchShow(s, i, M.gE(M.ciniki_wineproduction_main.add.panelUID + '_' + i), rsp.customers); 
					});
			}
		};
		this.add.liveSearchResultValue = function(s, f, i, j, d) {
			if( f.match(/^product_id/) ) { return d.name.wine_name; }
			if( f == 'customer_id') {  return d.customer.display_name; }
			return '';
		};
		// this.add.liveSearchResultID = function(i, result_index, d) { return result_index; }
		this.add.liveSearchResultRowFn = function(s, f, i, j, d) { 
			if( f.match(/^product_id/) ) {
				var x = f.replace(/^product_id_/, '');
				return 'M.ciniki_wineproduction_main.add.updateProduct(\'' + s + '\',\'' + x + '\',\'' + d.name.id + '\',\'' + escape(d.name.wine_name) + '\',\'' + d.name.wine_type + '\',\'' + d.name.kit_length + '\',\'' + d.name.order_flags + '\');';
			} else if( f == 'customer_id' ) {
				return 'M.ciniki_wineproduction_main.add.updateCustomer(\'' + s + '\',\'' + escape(d.customer.display_name) + '\',\'' + d.customer.id + '\');';
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
				M.api.getJSONCb('ciniki.calendars.appointments', {'business_id':M.curBusinessID, 'date':day}, cb);
			}
		};
// Move into ciniki_panels
//		this.add.appointmentEventText = function(ev) { return ev.subject; };
//		this.add.appointmentColour = function(ev) {
//			if( ev != null && ev.colour != null && ev.colour != '' ) {
//				return ev.colour;
//			}
//			return '#aaddff';
//		};
		// this.add.addLeftButton('cancel', 'Cancel', 'M.ciniki_wineproduction_main.showMain();');
		this.add.addButton('save', 'Save', 'M.ciniki_wineproduction_main.addOrder();');
		this.add.addClose('cancel');

		//
		// The list panel to display a list of wines, with the status information
		// Turn off completed, unless needed
		//
		this.list = new M.panel('Wine List',
			'ciniki_wineproduction_main', 'list',
			'mc', 'xlarge', 'sectioned', 'ciniki.wineproduction.main.list');
		this.list.gridSorting = {};
		this.list.sections = {
			'pending':{'label':'', 'num_cols':1, 'headerValues':[], 'sortable':'yes', 'searchable':'yes', 'savesort':'M.ciniki_wineproduction_main.list.saveSortOrder', 'type':'simplegrid'},
			'completed':{'label':'', 'num_cols':1, 'headerValues':[], 'sortable':'yes', 'savesort':'M.ciniki_wineproduction_main.list.saveSortOrder', 'visible':'no', 'type':'simplegrid'},
			};
		//
		// Sort and retrieve sorting information for columns
		// This is complicated because this one panel displays many different lists
		//
		this.list.saveSortOrder = function(tid, c, t, o) {
			// Save order based on table ID *AND* the ordertype
			if( M.ciniki_wineproduction_main.list.gridSorting[tid] == null ) {
				M.ciniki_wineproduction_main.list.gridSorting[tid] = {};
			}
			M.ciniki_wineproduction_main.list.gridSorting[tid][M.ciniki_wineproduction_main.list.ordertype] = {'col':c, 'type':t, 'order':o};
		};
		this.list.sortOrder = function(tid) {
			if( this.gridSorting[tid] != null && this.gridSorting[tid][this.ordertype] != null ) {
				return this.gridSorting[tid][this.ordertype];
			}
			return null;
		};
		this.list.dataMaps = {'pending':[], 'completed':[]};
		this.list.data = {'pending':{}, 'completed':{}};
		this.list.noData = function(section) { return 'No orders found'; }
		this.list.num_cols = 5;
		this.list.rowStyle = function(section, i, d) {
			if( this.title == 'Call to Book' ) {
				for(j in M.ciniki_wineproduction_main.bottlingStatus) {
					if( (d.order.bottling_status&Math.pow(2, j-1)) == Math.pow(2,j-1) 
						&& M.ciniki_wineproduction_main.bottlingStatus[j].bgcolour != '' 
						&& M.ciniki_wineproduction_main.bottlingStatus[j].bgcolour != '#ffffff' ) {
						return 'background: ' + M.ciniki_wineproduction_main.bottlingStatus[j].bgcolour;
					}
				}
//				for(j in M.ciniki_wineproduction_main.bottlingFlags) {
//					if( (d['order']['bottling_flags']&Math.pow(2, j-1)) == Math.pow(2,j-1) 
//						&& M.ciniki_wineproduction_main.bottlingFlags[j].bgcolour != '' 
//						&& M.ciniki_wineproduction_main.bottlingFlags[j].bgcolour != '#ffffff' ) {
//						return 'background: ' + M.ciniki_wineproduction_main.bottlingFlags[j].bgcolour;
//					}
//				}
			}
			if( d.order.order_flags > 0 ) {
				for(j in M.ciniki_wineproduction_main.orderFlags) {
					if( (d.order.order_flags&Math.pow(2, j-1)) == Math.pow(2,j-1) 
						&& M.ciniki_wineproduction_main.orderFlags[j].bgcolour != '' 
						&& M.ciniki_wineproduction_main.orderFlags[j].bgcolour != '#ffffff' ) {
						return 'background: ' + M.ciniki_wineproduction_main.orderFlags[j].bgcolour;
					}
				}
			}
			if( d.order.colour_tag != '' ) {
				return 'background:' + d.order.colour_tag + ';';
			}
			return '';
		};
		this.list.headerClass = function(s, i) {
			if( this.dataMaps[s][i] == 'buttons' ) {
				return 'noprint';
			}
			if( this.dataMaps[s][i] == 'notes' ) {
				return 'printborder';
			}
			return '';
		};
		this.list.cellClass = function(s, i, col, d) {
			if( this.dataMaps[s][col] == 'status' && col == 0 ) {
				return '';	// FIXME: Create special class for flexible width label
			} else if( this.dataMaps[s][col] == 'wine_and_customer' 
				|| this.dataMaps[s][col] == 'invoice_number_and_flags' 
				|| this.dataMaps[s][col] == 'invoice_number_and_status' 
				|| this.dataMaps[s][col] == 'bottling_date_and_flags' 
				|| this.dataMaps[s][col] == 'wine_type_and_length' ) {
				return 'multiline';
			}
			if( this.dataMaps[s][col] == 'buttons' ) {
				return 'textbuttons noprint';
			}
			if( this.dataMaps[s][col] == 'notes' ) {
				return 'printborder';
			}
			return null;
		};
		this.list.cellValue = function(section, i, col, d) { 
			if( this.dataMaps[section][col] == 'bottling_flags_colour' ) {
				for(j in M.ciniki_wineproduction_main.bottlingFlags) {
					if( (d.order.bottling_flags&Math.pow(2, j-1)) == Math.pow(2,j-1) 
						&& M.ciniki_wineproduction_main.bottlingFlags[j].bgcolour != '' 
						&& M.ciniki_wineproduction_main.bottlingFlags[j].bgcolour != '#ffffff' ) {
						// return 'background: ' + M.ciniki_wineproduction_main.bottlingFlags[j].bgcolour;
						return "<span class='colourswatch' style='background-color:" + M.ciniki_wineproduction_main.bottlingFlags[j].bgcolour + ";'>&nbsp;</span>";
					}
				}
				return "<span class='colourswatch' style='background-color:#ffffff;'>&nbsp;</span>";
			} else if( this.dataMaps[section][col] == 'invoice_number_and_status' ) {
				if( d.order.bottling_status != '' ) {
					return "<span class='maintext'>" + d.order.invoice_number + "</span>" + "<span class='subtext'>" + M.ciniki_wineproduction_main.statusOptions[d.order.status] + "</span>";
				}
				return "<span class='maintext'>" + d.order.invoice_number + "</span>" + "<span class='subtext'>" + "&nbsp;</span>";
			} else if( this.dataMaps[section][col] == 'wine_and_customer' ) {
				return "<span class='maintext'>" + d.order.wine_name + "</span>" + "<span class='subtext'>" + d.order.customer_name + "</span>";
			} else if( this.dataMaps[section][col] == 'wine_type_and_length' ) {
				return "<span class='maintext'>" + d.order.wine_type + "</span>" + "<span class='subtext'>" + d.order.kit_length + "&nbsp;weeks</span>";
			} else if( this.dataMaps[section][col] == 'invoice_number_and_flags' ) {
				if( d.order.order_flags > 0 ) {
					var flags = '';
					for(j in M.ciniki_wineproduction_main.orderFlags) {
						if( (d.order.order_flags&Math.pow(2, j-1)) == Math.pow(2,j-1) ) {
							if( flags != '' ) { flags += ', '; }
							flags += M.ciniki_wineproduction_main.orderFlags[j].name;
						}
					}
					return "<span class='maintext'>" + d.order.invoice_number + "</span>" + "<span class='subtext'>" + flags + "</span>";
				}
				return "<span class='maintext'>" + d.order.invoice_number + "</span>" + "<span class='subtext'>" + "&nbsp;</span>";
			} else if( this.dataMaps[section][col] == 'bottling_date_and_flags' ) {
				var value = d.order.bottling_date;
				if( value == '0000-00-00' || value == null || value == 'null' ) {
					value = '';
				} 
				if( d.order.bottling_flags > 0 ) {
					var flags = '';
					for(j in M.ciniki_wineproduction_main.bottlingFlags) {
						if( (d.order.bottling_flags&Math.pow(2, j-1)) == Math.pow(2,j-1) ) {
							if( flags != '' ) { flags += ', '; }
							flags += M.ciniki_wineproduction_main.bottlingFlags[j].name;
						}
					}
					return "<span class='maintext'>" + value + "</span>" + "<span class='subtext'>" + flags + "</span>";
				}
				return "<span class='maintext'>" + value + "</span>" + "<span class='subtext'>" + "&nbsp;</span>";
			} else if( this.dataMaps[section][col] == 'rack_colour' ) {
				if( d.order.rack_colour != null && d.order.rack_colour != '' ) {
					return "<span class='colourswatch' style='background-color: " + d.order.rack_colour + ";'>&nbsp;</span>";	
				} else {
					return "<span class='colourswatch' style='background-color: #ffffff;'>&nbsp;</span>";	
				}
			} else if( this.dataMaps[section][col] == 'filter_colour' ) {
				if( d.order.filter_colour != null && d.order.filter_colour != '' ) {
					return "<span class='colourswatch' style='background-color: " + d.order.filter_colour + ";'>&nbsp;</span>";	
				} else {
					return "<span class='colourswatch' style='background-color: #ffffff;'>&nbsp;</span>";	
				}
			} else if( this.dataMaps[section][col] == 'status' ) {
				if( M.ciniki_wineproduction_main.statusOptions[d.order.status] != null ) {
					return M.ciniki_wineproduction_main.statusOptions[d.order.status];
				} else {
					return 'Unknown';
				}
			} else if( this.dataMaps[section][col] == 'notes' ) {
				if( d.order.notes != '' ) {
					return '*';
				}
				return '';
			} else if( this.dataMaps[section][col] == 'buttons' ) {
				// Check that SG is filled, before allowing button to be pressed
				var sg = Number(d.order.sg_reading);
				if( this.buttonText != 'Racked' || (this.buttonText == 'Racked' && d.order.status == 25) || (this.buttonText == 'Racked' && sg != '' 
					&& ((sg > .990 && sg < .999) || (sg > 990 && sg < 999))) ) {
					return "<button onclick=\"event.stopPropagation(); M.ciniki_wineproduction_main.actionOrder('" + d.order.id + "', '" + this.buttonText + "','" + d.order.kit_length + "'); return false;\">" + this.buttonText + "</button>";
				}
			} else if( this.dataMaps[section][col] == 'sgbuttons' ) {
				return "<button onclick=\"event.stopPropagation(); M.ciniki_wineproduction_main.quickSGbutton('" + d.order.id + "','992'); return false;\">92</button>" + 
					"<button onclick=\"event.stopPropagation(); M.ciniki_wineproduction_main.quickSGbutton('" + d.order.id + "','993'); return false;\">93</button>" +
					"<button onclick=\"event.stopPropagation(); M.ciniki_wineproduction_main.quickSGbutton('" + d.order.id + "','994'); return false;\">94</button>" +
					"<button onclick=\"event.stopPropagation(); M.ciniki_wineproduction_main.quickSGbutton('" + d.order.id + "','995'); return false;\">95</button>" +
					"<button onclick=\"event.stopPropagation(); M.ciniki_wineproduction_main.quickSGbutton('" + d.order.id + "','996'); return false;\">96</button>" +
					"<button onclick=\"event.stopPropagation(); M.ciniki_wineproduction_main.quickSGbutton('" + d.order.id + "','997'); return false;\">97</button>" +
					"<button onclick=\"event.stopPropagation(); M.ciniki_wineproduction_main.quickSGbutton('" + d.order.id + "','998'); return false;\">98</button>" +
					"<button onclick=\"event.stopPropagation(); M.ciniki_wineproduction_main.quickSGbutton('" + d.order.id + "','999'); return false;\">99</button>" +
					"<button onclick=\"event.stopPropagation(); M.ciniki_wineproduction_main.quickSGbutton('" + d.order.id + "','1000'); return false;\">00</button>" +
					"<button onclick=\"event.stopPropagation(); M.ciniki_wineproduction_main.quickSGbutton('" + d.order.id + "','1001'); return false;\">01</button>" +
					"<button onclick=\"event.stopPropagation(); M.ciniki_wineproduction_main.quickSGbutton('" + d.order.id + "','1002'); return false;\">02</button>";
				
			} else {
				var value = d.order[this.dataMaps[section][col]];
				if( value == '0000-00-00' ) {
					return '';
				} 
				return value;
			}
			return '';
		}
		this.list.cellUpdateFn = function(section, row, col, d) {
			// Update the cell value, if it's the sg_reading
			if( this.dataMaps[section][col] == 'sg_reading' ) {
				return M.ciniki_wineproduction_main.updateSG;
			}
		}

		this.list.rowFn = function(s, i, d) { 
			if( this.title == 'Late Wines' ) {
				return 'M.ciniki_wineproduction_main.showAppointment(\'M.ciniki_wineproduction_main.showOrders(null, \\\'latewines\\\');\',\'' + d.order.appointment_id + '\');';
			}
			if( this.title == 'Call to Book' ) {
				return 'M.ciniki_wineproduction_main.showAppointment(\'M.ciniki_wineproduction_main.showOrders(null, \\\'ctb\\\');\',\'' + d.order.appointment_id + '\');';
			}
			return 'M.ciniki_wineproduction_main.showOrder(\'' + d.order.id + '\', \'M.ciniki_wineproduction_main.showOrders(null, M.ciniki_wineproduction_main.list.ordertype);\');'; }

		// Return a pointer to the data for the current section
		this.list.sectionData = function(section) {
			return this.data[section];
		}

		this.list.sectionLabel = function(section) { return this.sections[section].label; }
		//this.list.addLeftButton('back', 'Back', 'M.ciniki_wineproduction_main.showMain();');
		this.list.addClose('Back');

		//
		// The list panel to display a list of wines, with the status information
		// Turn off completed, unless needed
		//
		this.workdone = new M.panel('Work Completed',
			'ciniki_wineproduction_main', 'workdone',
			'mc', 'xlarge', 'sectioned', 'ciniki.wineproduction.main.workdone');
		this.workdone.workdate = 'today';
		this.workdone.worklist = 'all';
		this.workdone.datePickerValue = function(s, d) { return this.workdate; }
		this.workdone.sections = {
			'datepicker':{'label':'', 'type':'datepicker', 'fn':'M.ciniki_wineproduction_main.showWorkDoneCalFn'},
			'ordered':{'label':'Ordered', 'headerValues':[], 'collapsable':'yes', 'visible':'yes', 'num_cols':1, 'sortable':'yes', 'type':'simplegrid'},
			'started':{'label':'Started', 'headerValues':[], 'collapsable':'yes', 'visible':'yes', 'num_cols':1, 'sortable':'yes', 'type':'simplegrid'},
			'racked':{'label':'Racked', 'headerValues':[], 'collapsable':'yes', 'visible':'yes', 'num_cols':1, 'sortable':'yes', 'type':'simplegrid'},
			'filtered':{'label':'Filtered', 'headerValues':[], 'collapsable':'yes', 'visible':'yes', 'num_cols':1, 'sortable':'yes', 'type':'simplegrid'},
			'bottled':{'label':'Bottled', 'headerValues':[], 'collapsable':'yes', 'visible':'yes', 'num_cols':1, 'sortable':'yes', 'type':'simplegrid'},
			};
		this.workdone.dataMaps = {'ordered':[], 'started':[], 'racked':[], 'filtered':[], 'bottled':[]};
		this.workdone.data = {'ordered':{}, 'started':{}, 'racked':{}, 'filtered':{}, 'bottled':{}};
		this.workdone.noData = function(section) { return 'No orders found'; }
		this.workdone.rowStyle = function(section, i, d) {
			if( d.order.order_flags > 0 ) {
				for(j in M.ciniki_wineproduction_main.orderFlags) {
					if( (d.order.order_flags&Math.pow(2, j-1)) == Math.pow(2,j-1) 
						&& M.ciniki_wineproduction_main.orderFlags[j].bgcolour != '' 
						&& M.ciniki_wineproduction_main.orderFlags[j].bgcolour != '#ffffff' ) {
						return 'background: ' + M.ciniki_wineproduction_main.orderFlags[j].bgcolour;
					}
				}
			}
			if( d.order.colour_tag != '' ) {
				return 'background:' + d.order.colour_tag + ';';
			}
			return '';
		};
		this.workdone.cellValue = this.list.cellValue;

		this.workdone.rowFn = function(section, i, d) { return 'M.ciniki_wineproduction_main.showOrder(\'' + d['order']['id'] + '\', \'M.ciniki_wineproduction_main.showWorkDone(null, null);\');'; }

		// Return a pointer to the data for the current section
		this.workdone.sectionData = function(section) {
			return this.data[section];
		}
		// this.workdone.sectionLabel = this.list.sectionLabel;
		this.workdone.sectionLabel = function(s) { 
			if( this.data[s] != null ) {
				return this.sections[s].label + ' <span class="count">' + this.data[s].length + '</span>';
			} else {
				return this.sections[s].label;

			}
		};	
		this.workdone.addLeftButton('back', 'Back', 'M.ciniki_wineproduction_main.showMain();');

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
				'addFn':'M.startApp(\'ciniki.customers.edit\',null,\'M.ciniki_wineproduction_main.showOrder();\',\'mc\',{\'next\':\'M.ciniki_wineproduction_main.updateOrderCustomer\',\'customer_id\':M.ciniki_wineproduction_main.order.data.customer_id});',
				'changeTxt':'Change customer',
				'changeFn':'M.startApp(\'ciniki.customers.edit\',null,\'M.ciniki_wineproduction_main.showOrder();\',\'mc\',{\'next\':\'M.ciniki_wineproduction_main.updateOrderCustomer\',\'customer_id\':0});',
			},
			'info':{'label':'', 'aside':'yes', 'fields':{
				'order_date':{'label':'Ordered', 'type':'date', 'caloffset':0},
				'invoice_number':{'label':'Invoice #', 'type':'text', 'size':'small'},
			//	'customer_id':{'label':'Customer', 'type':'fkid', 'size':'medium', 'livesearch':'yes'},
				'product_id':{'label':'Wine', 'type':'fkid', 'size':'medium', 'livesearch':'yes', 'livesearchempty':'yes'},
				'wine_type':{'label':'Type', 'type':'text', 'size':'medium'},
				'kit_length':{'label':'Kit Length', 'hint':'4, 5, 6, 8', 'type':'text', 'size':'small'},
				'order_flags':{'label':'Flags', 'join':'yes', 'type':'flags', 'flags':this.orderFlags},
			}},
			'bottling':{'label':'Bottling', 'aside':'yes', 'fields':{
				'bottling_duration':{'label':'Duration', 'type':'multitoggle', 'toggles':this.bottlingDurations},
				'bottling_date':{'label':'Date', 'type':'appointment', 'caloffset':0,
					'start':this.settings['bottling.schedule.start'], 
					'end':this.settings['bottling.schedule.end'], 
					'interval':this.settings['bottling.schedule.interval'],
					'notimelabel':'CTB'},
//				'bottling_flags':{'label':'Flags', 'join':'yes', 'toggle':'yes', 'type':'flags', 'flags':this.bottlingFlags},
				'bottling_status':{'label':'Status', 'join':'yes', 'toggle':'yes', 'type':'flags', 'flags':this.bottlingStatus},
//				'bottling_nocolour_flags':{'label':'Other', 'join':'yes', 'toggle':'no', 'type':'flags', 'flags':this.bottlingNocolourFlags},
			}},
			'details':{'label':'Details', 'fields':{
				'status':{'label':'Status', 'type':'select', 'options':this.statusOptions},
				'rack_colour':{'label':'Rack', 'type':'colourswatches', 'colours':this.rackingColours},
				'filter_colour':{'label':'Filter', 'type':'colourswatches', 'colours':this.filteringColours},
				'start_date':{'label':'Started', 'type':'date', 'caloffset':0},
				'sg_reading':{'label':'SG', 'type':'text', 'size':'small'},
				'racking_date':{'label':'RD', 'type':'date', 'caloffset':0, 'colourize':'bg'},
				'rack_date':{'label':'Racked', 'type':'date', 'caloffset':0},
				'filtering_date':{'label':'FD', 'type':'date', 'caloffset':0, 'colourize':'bg'},
				'filter_date':{'label':'Filtered', 'type':'date', 'caloffset':0},
				'bottle_date':{'label':'Bottled', 'type':'date', 'caloffset':0},
				'batch_code':{'label':'Batch Code', 'type':'text'},
				}},
			'_notes':{'label':'Notes', 'fields':{
				'notes':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'small'},
				}},
			'_buttons':{'label':'', 'buttons':{
				'save':{'label':'Save order', 'fn':'M.ciniki_wineproduction_main.saveOrder();'},
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
				return M.ciniki_wineproduction_main.settings['racking.autocolour.week' + (Math.floor(((c.getTime()/1000) - 1468800)/604800))%3 + M.dayOfWeek(c)];
			} else if( i == 'filtering_date' ) {
				
				return M.ciniki_wineproduction_main.settings['filtering.autocolour.week' + (Math.floor(((c.getTime()/1000) - 1468800)/604800))%7 + M.dayOfWeek(c)];
			}
		};
		this.order.listValue = function(s, i, d) { return d['label']; };
		this.order.listFn = function(s, i, d) { return d['fn']; };
		this.order.fieldValue = function(s, i, d) { 
//			if( i == 'customer_id_fkidstr' ) {
//				return this.data['customer_name'];
//			} else 
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
					{'business_id':M.curBusinessID, 'customer_id':cv, 'start_needle':value, 'limit':11},
					function(rsp) { 
						M.ciniki_wineproduction_main.order.liveSearchShow(s, i, M.gE(M.ciniki_wineproduction_main.order.panelUID + '_' + i), rsp['names']); 
					});
			}
//			if( i == 'customer_id' && value != '' ) {
//				var rsp = M.api.getJSONBgCb('ciniki.customers.searchQuick', {'business_id':M.curBusinessID, 'start_needle':value, 'limit':25},
//					function(rsp) { 
//						M.ciniki_wineproduction_main.order.liveSearchShow(s, i, M.gE(M.ciniki_wineproduction_main.order.panelUID + '_' + i), rsp['customers']); 
//					});
//			}
		};
		this.order.liveSearchResultValue = this.add.liveSearchResultValue;
		this.order.liveSearchResultRowFn = function(s, f, i, j, d) { 
			if( f == 'product_id' ) {
				return 'M.ciniki_wineproduction_main.order.updateProduct(\'' + s + '\',\'' + f + '\',\'' + d.name.id + '\',\'' + escape(d.name.wine_name) + '\',\'' + d.name.wine_type + '\',\'' + d.name.kit_length + '\',\'' + d.name.order_flags + '\');';
//			} else if( f == 'customer_id' ) {
//				return 'M.ciniki_wineproduction_main.order.updateCustomer(\'' + s + '\',\'' + escape(d.customer.name) + '\',\'' + d.customer.id + '\');';
			}
		};
//		this.order.updateCustomer = function(s, customer_name, customer_id) {
//			M.gE(this.panelUID + '_customer_id').value = customer_id;
//			M.gE(this.panelUID + '_customer_id_fkidstr').value = unescape(customer_name);
//			this.removeLiveSearch(s, 'customer_id');
//		};
		this.order.updateProduct = function(s, field, product_id, wine_name, wine_type, kit_length, order_flags) {
			M.gE(this.panelUID + '_product_id').value = product_id;
			M.gE(this.panelUID + '_product_id_fkidstr').value = unescape(wine_name);
			M.gE(this.panelUID + '_wine_type').value = wine_type;
			M.gE(this.panelUID + '_kit_length').value = kit_length;
			this.setFieldValue('order_flags', order_flags);
			this.removeLiveSearch(s, 'product_id');
		};
		this.order.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.wineproduction.getHistory', 'args':{'business_id':M.curBusinessID, 
				'wineproduction_id':this.order_id, 'field':i}};
		}
		this.order.rowFn = function(s, i, d) {
			return '';
		};
		this.order.appointmentEventText = this.add.appointmentEventText;
		this.order.appointmentColour = this.add.appointmentColour;
		this.order.liveAppointmentDayEvents = this.add.liveAppointmentDayEvents;
		this.order.addButton('save', 'Save', 'M.ciniki_wineproduction_main.saveOrder();');
		this.order.addLeftButton('cancel', 'Cancel', 'M.ciniki_wineproduction_main.order.close();');

		//
		// Then to display an bottling appointment
		//
		this.appointment = new M.panel('Appointment',
			'ciniki_wineproduction_main', 'appointment',
			'mc', 'large', 'sectioned', 'ciniki.wineproduction.main.appointment');
		this.appointment.data = null;
		this.appointment.cb = null;
		this.appointment.sections = {
			'customer':{'label':'Customer', 'type':'simplegrid', 'num_cols':2,
				'cellClasses':['label',''],
				'addTxt':'Edit',
				'addFn':'M.startApp(\'ciniki.customers.edit\',null,\'M.ciniki_wineproduction_main.showAppointment();\',\'mc\',{\'next\':\'M.ciniki_wineproduction_main.updateAppointmentCustomer\',\'customer_id\':M.ciniki_wineproduction_main.appointment.customer_id});',
				'changeTxt':'Change customer',
				'changeFn':'M.startApp(\'ciniki.customers.edit\',null,\'M.ciniki_wineproduction_main.showAppointment();\',\'mc\',{\'next\':\'M.ciniki_wineproduction_main.updateAppointmentCustomer\',\'customer_id\':0});',
			},
			'info':{'label':'', 'fields':{
				'invoice_number':{'label':'Invoice #', 'type':'noedit', 'size':'small', 'history':'no'},
//				'customer_name':{'label':'Customer', 'type':'noedit', 'size':'medium', 'history':'no'},
				'bottling_duration':{'label':'Duration', 'type':'multitoggle', 'toggles':this.bottlingDurations},
				'bottling_date':{'label':'Date', 'type':'appointment', 'caloffset':0,
					'start':this.settings['bottling.schedule.start'], 
					'end':this.settings['bottling.schedule.end'], 
					'interval':this.settings['bottling.schedule.interval'],
					'notimelabel':'CTB',
					},
				'bottling_flags':{'label':'Flags', 'join':'yes', 'toggle':'yes', 'type':'flags', 'flags':this.bottlingFlags},
				'bottling_nocolour_flags':{'label':'', 'join':'yes', 'toggle':'no', 'type':'flags', 'flags':this.bottlingNocolourFlags},
				}},
			'_bottled':{'label':'', 'buttons':{
				'bottled':{'label':'Bottled', 'fn':'M.ciniki_wineproduction_main.saveAppointment(\'yes\');'},
			}},
			'wines':{'label':'Wines', 'type':'simplegrid', 'num_cols':'7', 'compact_split_at':6,
				'headerValues':['INV#', 'Wine', 'OD', 'SD', 'RD', 'FD', 'Status'],
				'dataMaps':['invoice_number_and_status', 'wine_name', 'order_date', 'start_date', 'racking_date', 'filtering_date', 'buttons'],
				'fields':{},
				'data':{}
			},
			'_notes':{'label':'Notes', 'fields':{
				'bottling_notes':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'small'},
				}},
			'_buttons':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_wineproduction_main.saveAppointment(\'no\');'},
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
		this.appointment.cellValue = function(s, i, col, d) {
			if( s == 'customer' ) {
				switch(col) {
					case 0: return d.detail.label;
					case 1: return d.detail.value;
				}
			}
			else if( s == 'wines' ) {
				if( col == 0 ) {
					return "<span class='maintext'>" + d['order']['invoice_number'] + "</span>" + "<span class='subtext'>" + M.ciniki_wineproduction_main.statusOptions[d['order']['status']] + "</span>";
				} else if( col == 1 ) {
					return "<span class='maintext'>" + d['order']['wine_name'] + "</span><span class='subtext'>" + d['order']['bottling_status'] + "</span>";
				} else if( col > 1 && col < 7 ) {
					var dt = d['order'][this.sections[s].dataMaps[col]];
					// Check for missing filter date, and try to take a guess
					if( dt == null && col == 5 ) {
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

				return d['order'][this.sections[s].dataMaps[col]];
			}
			return '';
		};
		this.appointment.rowFn = function(s, i, d) {
			if( s == 'wines' ) {
				return 'M.ciniki_wineproduction_main.showOrder(\'' + d.order.order_id + '\', \'M.ciniki_wineproduction_main.showAppointment(null, null);\');'; 
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
			return {'method':'ciniki.wineproduction.getHistory', 'args':{'business_id':M.curBusinessID, 
				'wineproduction_id':this.data.orders[0].order.order_id, 'field':i}};
		}
		this.appointment.appointmentEventText = this.add.appointmentEventText;
		this.appointment.appointmentColour = this.add.appointmentColour;
		this.appointment.liveAppointmentDayEvents = this.add.liveAppointmentDayEvents;
		this.appointment.addButton('save', 'Save', 'M.ciniki_wineproduction_main.saveAppointment(\'no\');');
		this.appointment.addClose('Cancel');

		//
		// The search panel will list all search results for a string.  This allows more advanced searching,
		// and will search the entire strings, not just start of the string like livesearch
		//
		this.search = new M.panel('Search Results',
			'ciniki_wineproduction_main', 'search',
			'mc', 'xlarge', 'sectioned', 'ciniki.wineproduction.main.search');
		this.search.sections = {
			'main':{'label':'', 'headerValues':[], 'num_cols':5, 'type':'simplegrid', 'sortable':'yes'},
		};
		this.search.data = {};
		this.search.noData = function() { return 'No orders found'; }
		this.search.dataMaps = {'main':[]};
		this.search.headerClass = this.list.headerClass;
		this.search.cellClass = this.list.cellClass;
		this.search.cellValue = this.list.cellValue;
		this.search.rowFn = function(s, i, d) { return 'M.ciniki_wineproduction_main.showOrder(\'' + d.order.id + '\', \'M.ciniki_wineproduction_main.search.show();\');'; }
		// this.search.addLeftButton('back', 'Back', 'M.ciniki_wineproduction_main.showMain();');
		this.search.addClose('Back');
		this.search.sectionData = this.list.sectionData;
		this.search.sectionLabel = this.list.sectionLabel;

		//
		// The schedule panel will display upcoming work
		//
		this.schedule = new M.panel('Schedule',
			'ciniki_wineproduction_main', 'schedule',
			'mc', 'xlarge', 'sectioned', 'ciniki.wineproduction.main.schedule');
		this.schedule.sections = {
			'stats':{'label':'', 'num_cols':17, 'type':'simplegrid', 'sortable':'no'},
			'orders':{'label':'Orders', 'num_cols':5, 'type':'simplegrid', 'sortable':'yes', 'headerValues':[]},
			};
		this.schedule.data = {'stats':{}, 'orders':{}, 'days':{}};
		this.schedule.dataMaps = {'stats':[], 'orders':[]};
		this.schedule.noData = function(section) { return 'No orders found'; }
		this.schedule.sectionLabel = function(i, d) { 
			if( i == 'orders' ) { 
				return d['label']; 
			} 
			return null; 
		};
		this.schedule.headerClass = function(s, i) {
			if( this.dataMaps[s][i] == 'buttons' ) {
				return 'noprint';
			}
			if( this.dataMaps[s][i] == 'notes' ) {
				return 'printborder';
			}
			return '';
		};
		this.schedule.rowStyle = function(section, i, d) {
			if( this.ordertype == 'racking' ) {
			} else if( this.ordertype == 'filtering' ) {
			} else if( (this.ordertype == 'bottling' || this.ordertype == 'before_bottling' || this.ordertype == 'after_bottling') && d['order'] != null && (d.order.bottling_flags != null || d.order.bottling_flags > 0) ) {
				for(j in M.ciniki_wineproduction_main.bottlingStatus) {
					if( (d['order']['bottling_status']&Math.pow(2, j-1)) == Math.pow(2,j-1) 
						&& M.ciniki_wineproduction_main.bottlingStatus[j].bgcolour != '' 
						&& M.ciniki_wineproduction_main.bottlingStatus[j].bgcolour != '#ffffff' ) {
						return 'background: ' + M.ciniki_wineproduction_main.bottlingStatus[j].bgcolour;
					}
				}
			}
			return '';
		};
		this.schedule.cellClass = function(s, i, col, d) {
			if( s == 'stats' ) {
				if( i == 'days' && col > 1 && col < 16 ) {
					return 'multiline aligncenter';	
				}
				else if( col > 0 ) {
					return 'aligncenter';
				} else {
					return '';
				}
			} else if( s == 'orders' ) {
				if( this.dataMaps[s][col] == 'wine_type_and_length' 
					|| this.dataMaps[s][col] == 'bottling_date_and_flags'
					|| this.dataMaps[s][col] == 'wine_and_customer'
					|| this.dataMaps[s][col] == 'invoice_number_and_status' ) {
					return 'multiline';
				}
				if( this.dataMaps[s][col] == 'buttons' ) {
					return 'textbuttons noprint';
				}
				if( this.dataMaps[s][col] == 'notes' ) {
					return 'printborder';
				}
			}
			return null;
		};
		this.schedule.cellValue = function(section, i, col, d) { 
			if( section == 'stats' ) {
				if( i == 'days' ) {
					return d[col];
				} else if( i == 'racking' && col == 0 ) {
					return 'Racking';
				} else if( i == 'filtering' && col == 0 ) {
					return 'Filtering';
				} else if( i == 'bottling' && col == 0 ) {
					return 'Bottling';
				} else if( col == 1 ) {
					if( d['past'] == 0 ) { return ''; }
					return d['past'];
				} else if( col == 16 ) {
					if( d['future'] == 0 ) { return ''; }
					return d['future'];
				} else if( i == 'racking' || i == 'filtering' || i == 'bottling' ) {
					if( this.data['stats'][i][(col-2)]['stat']['count'] == 0 ) { return ''; }
					return this.data['stats'][i][(col-2)]['stat']['count'];
				}
				return '';
			} else if( section == 'orders' ) {
				if( this.dataMaps[section][col] == 'bottling_flags_colour' ) {
					for(j in M.ciniki_wineproduction_main.bottlingFlags) {
						if( (d['order']['bottling_flags']&Math.pow(2, j-1)) == Math.pow(2,j-1) 
							&& M.ciniki_wineproduction_main.bottlingFlags[j].bgcolour != '' 
							&& M.ciniki_wineproduction_main.bottlingFlags[j].bgcolour != '#ffffff' ) {
							return "<span class='colourswatch' style='background-color: " + M.ciniki_wineproduction_main.bottlingFlags[j].bgcolour + ";'>&nbsp;</span>";	
						}
					}
					return "<span class='colourswatch' style='background-color:#ffffff;'>&nbsp;</span>";
				} else if( this.dataMaps[section][col] == 'wine_and_customer' ) {
					return "<span class='maintext'>" + d['order']['wine_name'] + "</span>" + "<span class='subtext'>" + d['order']['customer_name'] + "</span>";
				} else if( this.dataMaps[section][col] == 'wine_type_and_length' ) {
					return "<span class='maintext'>" + d['order']['wine_type'] + "</span>" + "<span class='subtext'>" + d['order']['kit_length'] + "&nbsp;weeks</span>";
				} else if( this.dataMaps[section][col] == 'rack_colour' ) {
					if( d['order']['rack_colour'] != null && d['order']['rack_colour'] != '' ) {
						return "<span class='colourswatch' style='background-color: " + d['order']['rack_colour'] + ";'>&nbsp;</span>";	
					} else {
						return "<span class='colourswatch' style='background-color: #ffffff;'>&nbsp;</span>";	
					}
				} else if( this.dataMaps[section][col] == 'filter_colour' ) {
					if( d['order']['filter_colour'] != null && d['order']['filter_colour'] != '' ) {
						return "<span class='colourswatch' style='background-color: " + d['order']['filter_colour'] + ";'>&nbsp;</span>";	
					} else {
						return "<span class='colourswatch' style='background-color: #ffffff;'>&nbsp;</span>";	
					}
				} else if( this.dataMaps[section][col] == 'bottling_date_and_flags' ) {
					var value = d['order']['bottling_date'];
					if( value == '0000-00-00' || value == null || value == 'null' ) {
						value = '';
					} 
					if( d.order.bottling_flags > 0 || d.order.bottling_status > 0 ) {
						var flags = '';
						for(j in M.ciniki_wineproduction_main.bottlingFlags) {
							if( (d['order']['bottling_flags']&Math.pow(2, j-1)) == Math.pow(2,j-1) ) {
								if( flags != '' ) { flags += ', '; }
								flags += M.ciniki_wineproduction_main.bottlingFlags[j].name;
							}
						}
						for(j in M.ciniki_wineproduction_main.bottlingStatus) {
							if( (d['order']['bottling_status']&Math.pow(2, j-1)) == Math.pow(2,j-1) ) {
								if( flags != '' ) { flags += ', '; }
								flags += M.ciniki_wineproduction_main.bottlingStatus[j].name;
							}
						}
						return "<span class='maintext'>" + value + "</span>" + "<span class='subtext'>" + flags + "</span>";
					}
					return "<span class='maintext'>" + value + "</span>" + "<span class='subtext'>" + "&nbsp;</span>";
				} else if( this.dataMaps[section][col] == 'invoice_number_and_status' ) {
					if( d['order']['bottling_status'] != '' ) {
						return "<span class='maintext'>" + d['order']['invoice_number'] + "</span>" + "<span class='subtext'>" + M.ciniki_wineproduction_main.statusOptions[d['order']['status']] + "</span>";
					}
					return "<span class='maintext'>" + d['order']['invoice_number'] + "</span>" + "<span class='subtext'>" + "&nbsp;</span>";
				} else if( this.dataMaps[section][col] == 'status' ) {
					if( M.ciniki_wineproduction_main.statusOptions[d['order']['status']] != null ) {
						return M.ciniki_wineproduction_main.statusOptions[d['order']['status']];
					} else {
						return 'Unknown';
					}
				} else if( this.dataMaps[section][col] == 'notes' ) {
					if( d['order']['notes'] != '' ) {
						return '*';
					}
					return '';
				} else if( this.dataMaps[section][col] == 'buttons' ) {
					// Check that SG is filled, before allowing button to be pressed
					var sg = Number(d.order.sg_reading);
					if( this.buttonText == 'Filter Today' && d.order.status == 40 ) {
						// If order is ready to be bottled, show quick button 'Bottled'
						return "<button onclick=\"event.stopPropagation(); M.ciniki_wineproduction_main.actionScheduleOrder('" + d.order.id + "', 'Bottled','" + d.order.kit_length + "'); return false;\">Bottled</button>";
					} else if( this.buttonText != 'Racked' || (this.buttonText == 'Racked' && sg != '' 
						&& ((sg > .990 && sg < .999) || (sg > 990 && sg < 999))) ) {
						return "<button onclick=\"event.stopPropagation(); M.ciniki_wineproduction_main.actionScheduleOrder('" + d.order.id + "', '" + this.buttonText + "','" + d.order.kit_length + "'); return false;\">" + this.buttonText + "</button>";
					} else if( this.buttonText == 'Racked' && sg == '' ) {
						// Display SG Buttons
						return "<button onclick=\"event.stopPropagation(); M.ciniki_wineproduction_main.actionScheduleSGbutton('" + d.order.id + "','992'); return false;\">92</button>" + 
							"<button onclick=\"event.stopPropagation(); M.ciniki_wineproduction_main.actionScheduleSGbutton('" + d.order.id + "','993'); return false;\">93</button>" +
							"<button onclick=\"event.stopPropagation(); M.ciniki_wineproduction_main.actionScheduleSGbutton('" + d.order.id + "','994'); return false;\">94</button>" +
							"<button onclick=\"event.stopPropagation(); M.ciniki_wineproduction_main.actionScheduleSGbutton('" + d.order.id + "','995'); return false;\">95</button>" +
							"<button onclick=\"event.stopPropagation(); M.ciniki_wineproduction_main.actionScheduleSGbutton('" + d.order.id + "','996'); return false;\">96</button>" +
							"<button onclick=\"event.stopPropagation(); M.ciniki_wineproduction_main.actionScheduleSGbutton('" + d.order.id + "','997'); return false;\">97</button>" +
							"<button onclick=\"event.stopPropagation(); M.ciniki_wineproduction_main.actionScheduleSGbutton('" + d.order.id + "','998'); return false;\">98</button>" +
							"<button onclick=\"event.stopPropagation(); M.ciniki_wineproduction_main.actionScheduleSGbutton('" + d.order.id + "','999'); return false;\">99</button>" +
							"<button onclick=\"event.stopPropagation(); M.ciniki_wineproduction_main.actionScheduleSGbutton('" + d.order.id + "','1000'); return false;\">00</button>" +
							"<button onclick=\"event.stopPropagation(); M.ciniki_wineproduction_main.actionScheduleSGbutton('" + d.order.id + "','1001'); return false;\">01</button>" +
							"<button onclick=\"event.stopPropagation(); M.ciniki_wineproduction_main.actionScheduleSGbutton('" + d.order.id + "','1002'); return false;\">02</button>";
					}
				} else {
					var value = d.order[this.dataMaps[section][col]];
					if( value == '0000-00-00' ) {
						return '';
					} 
					return value;
				}
				return '';
			}
		};
		this.schedule.cellFn = function(section, i, col, d) {
			// Check if any cells should be clickable.
			if( section == 'stats' && (i == 'racking' || i == 'sgready' || i == 'filtering' || i == 'bottling' ) ) {
				if( (col > 1 && col < 16) && this.data['stats'][i][(col-2)]['stat']['count'] > 0 ) {
					return 'M.ciniki_wineproduction_main.showScheduleList(\'' + i + '\',\'' + this.data['stats'][i][(col-2)]['stat']['year'] + '-' + this.data['stats'][i][(col-2)]['stat']['month'] + '-' + this.data['stats'][i][(col-2)]['stat']['day'] + '\',\'' + this.data['stats'][i][(col-2)]['stat'][i+'_date'] + '\');';
				} else if( col == 1 && d['past'] > 0 ) { 
					return 'M.ciniki_wineproduction_main.showScheduleList(\'before_' + i + '\',\'' + this.data['stats'][i][(col-1)]['stat']['year'] + '-' + this.data['stats'][i][(col-1)]['stat']['month'] + '-' + this.data['stats'][i][(col-1)]['stat']['day'] + '\',\'Past\');';
				} else if( col == 16 && d['future'] > 0 ) {
					return 'M.ciniki_wineproduction_main.showScheduleList(\'after_' + i + '\',\'' + this.data['stats'][i][(col-3)]['stat']['year'] + '-' + this.data['stats'][i][(col-3)]['stat']['month'] + '-' + this.data['stats'][i][(col-3)]['stat']['day'] + '\',\'Future\');';
				}
			} 
			return '';
		};
		this.schedule.cellStyle = function(s, i, j, d) {
			if( s == 'orders' && this.dataMaps[s][j] == 'bottling_date_and_flags') {
				for(j in M.ciniki_wineproduction_main.bottlingStatus) {
					if( (d['order']['bottling_status']&Math.pow(2, j-1)) == Math.pow(2,j-1) 
						&& M.ciniki_wineproduction_main.bottlingStatus[j].bgcolour != '' 
						&& M.ciniki_wineproduction_main.bottlingStatus[j].bgcolour != '#ffffff' ) {
						return 'background: ' + M.ciniki_wineproduction_main.bottlingStatus[j].bgcolour;
					}
				}
			}
		};
		this.schedule.rowFn = function(section, i, d) { 
			if( section == 'orders' ) {
				return 'M.ciniki_wineproduction_main.showOrder(\'' + d.order.id + '\', \'M.ciniki_wineproduction_main.showSchedule();\');'; 
			}
			return null;
		};
		this.schedule.sectionData = function(section) {
			return this.data[section];
		}
//		this.schedule.addLeftButton('back', 'Back', 'M.ciniki_wineproduction_main.showMain();');
		this.schedule.addClose('Back');
		this.startFinish();
	}

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
		var appContainer = M.createContainer(appPrefix, 'ciniki_wineproduction_main', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		} 

		this.cb = cb;
		this.initStart();
	};

	this.startFinish = function() {
		var cb = this.cb;
		// this.files.show(cb);
		if( args.search != null && args.search != '' ) {	
			this.search.cb = cb;
			this.searchOrders(args.search);
		} 
		else if( args.appointment_id != null && args.appointment_id != '' ) {
			this.showAppointment(cb, args.appointment_id);
		}
		else if( args.order_id != null && args.order_id > 0 ) {
			this.showOrder(args.order_id, cb);
		} else if( args.add != null && args.add == 'yes' ) {
			this.showAdd(cb);
		} else if( args.ctb != null && args.ctb == 'yes' ) {
			this.showOrders(cb, 'ctb');
		} else if( args.schedule != null && args.schedule == 'today' ) {
			this.schedule.cb = cb;
			this.showSchedule(cb, null, args.schedule);
		} else {
			this.showMain(cb);
		}
	};

	this.showAdd = function(cb) {
		// Reset form
		this.add.reset();
		this.add.data = {'wines':[]};
		this.add.data.wines[0] = {};
		for(i=1;i<=21;i++) {
			if( this.add.sections['wines_' + i] != null ) {
				delete this.add.sections['wines_' + i];
			}
		}
		this.add.show(cb);
	}

	//
	// Grab the stats for the business from the database and present the list of orders.
	//
	this.showMain = function(cb) {
		var rsp = M.api.getJSONCb('ciniki.wineproduction.stats', 
			{'business_id':M.curBusinessID}, function(rsp) { M.ciniki_wineproduction_main.showMainFinish(cb, rsp); });
	};
	
	this.showMainFinish = function(cb, rsp) {
		if( rsp.stat != 'ok' ) {
			M.api.err(rsp);
			return false;
		}
		var p = M.ciniki_wineproduction_main.main;
		p.stats = rsp.stats;
		//
		// Reset the current counts
		//
		p.sections.today.list.start.count = 0;
		p.sections.today.list.todayssgreadings.count = 0;
		p.sections.today.list.todaysracking.count = 0;
		p.sections.today.list.todaysfiltering.count = 0;
		p.sections.reports.list.workdone.count = 0;
		p.sections.reports.list.latewines.count = 0;
		p.sections.reports.list.ctb.count = 0;
		p.sections.orders.list.started.count = 0;
		p.sections.orders.list.sgready.count = 0;
		p.sections.orders.list.racked.count = 0;
		p.sections.orders.list.filtered.count = 0;
		for(i in rsp.stats) {
			if( rsp.stats[i].stat.status == 10 ) {
				p.sections.orders.list.ordered.count = rsp.stats[i].stat.count;
				p.sections.today.list.start.count = rsp.stats[i].stat.count;
			} else if( rsp.stats[i].stat.status == 20 ) {
				p.sections.orders.list.started.count = Number(rsp.stats[i].stat.count);
			} else if( rsp.stats[i].stat.status == 25 ) {
				p.sections.orders.list.sgready.count = Number(rsp.stats[i].stat.count);
			} else if( rsp.stats[i].stat.status == 30 ) {
				p.sections.orders.list.racked.count = rsp.stats[i].stat.count;
			} else if( rsp.stats[i].stat.status == 40 ) {
				p.sections.orders.list.filtered.count = rsp.stats[i].stat.count;
			}
		}
		p.statsPast = rsp.past;
		for(i in rsp.past) {
			if( rsp.past[i].stat.status == 20 ) {
				p.sections.today.list.todayssgreadings.count += Number(rsp.past[i].stat.count);
			} else if( rsp.past[i].stat.status == 30 ) {
				p.sections.today.list.todaysfiltering.count += Number(rsp.past[i].stat.count);
			}
		}
		p.statsToday = rsp.todays;
		for(i in rsp.todays) {
			if( rsp.todays[i].stat.status == 10 ) {
			} else if( rsp.todays[i].stat.status == 20 ) {
				p.sections.today.list.todayssgreadings.count += Number(rsp.todays[i].stat.count);
			} else if( rsp.todays[i].stat.status == 25 ) {
				p.sections.today.list.todaysracking.count += Number(rsp.todays[i].stat.count);
			} else if( rsp.todays[i].stat.status == 30 ) {
				p.sections.today.list.todaysfiltering.count += Number(rsp.todays[i].stat.count);
			}
		}
		
		for(i in rsp['latewines']) {
			p.sections.reports.list.latewines.count += Number(rsp.latewines[i].stat.count);
		}
		for(i in rsp['ctb']) {
			p.sections.reports.list.ctb.count += Number(rsp.ctb[i].stat.count);
		}
		for(i in rsp['workdone']) {
			p.sections.reports.list.workdone.count += Number(rsp.workdone[i].stat.count);
		}

		p.refresh();
		p.show(cb);
	}


	this.showWorkDoneCalFn = function(f, workdate) {
		this.showWorkDone(workdate, null);
	}

	// 
	// Note: the f argument is because this is called from mossi_panels, and it wants to pass a form field
	//
	this.showWorkDone = function(workdate, worklist) {
		var rsp = null;
		if( worklist != null && worklist != '' && worklist != 'undefined' ) {
			this.workdone.worklist = worklist;
		}

		if( workdate == null || workdate == '' ) {
			if( this.workdone.workdate == null || this.workdone.workdate == '' ) {
				this.workdone.workdate = 'today';
			}
			// else, workdone should not be changed
		} else {
			this.workdone.workdate = workdate;
		}

		if( this.workdone.worklist == 'all' || this.workdone.worklist == 'ordered' ) {
			this.workdone.sections['ordered'].visible = 'yes';
			this.workdone.sections['ordered'].num_cols = 6;
			this.workdone.sections['ordered'].headerValues = ['INV#', 'Wine', 'Type', 'Ordered', 'BD', ''];
			this.workdone.sections['ordered'].sortTypes = ['text', 'number', 'text', 'date', 'date', 'none'];
			this.workdone.dataMaps['ordered'] = ['invoice_number', 'wine_name', 'wine_type', 'order_date', 'bottling_date', 'notes'];
		} else {
			this.workdone.sections['ordered'].visible = 'no';
		}

		if( this.workdone.worklist == 'all' || this.workdone.worklist == 'started' ) {
			this.workdone.sections['started'].visible = 'yes';
			this.workdone.sections['started'].num_cols = 6;
			this.workdone.sections['started'].headerValues = ['', 'INV#', 'Wine', 'Ordered', 'BD', ''];
			this.workdone.sections['started'].sortTypes = ['colour', 'number', 'text', 'date', 'date', 'none'];
			this.workdone.dataMaps['started'] = ['rack_colour', 'invoice_number', 'wine_name', 'order_date', 'bottling_date', 'notes'];
		} else {
			this.workdone.sections['started'].visible = 'no';
		}

		if( this.workdone.worklist == 'all' || this.workdone.worklist == 'racked' ) {
			this.workdone.sections['racked'].visible = 'yes';
			this.workdone.sections['racked'].num_cols = 8;
			this.workdone.sections['racked'].headerValues = ['', 'INV#', 'Wine', 'SG', 'Started', 'BD', 'RD', '', ''];
			this.workdone.sections['racked'].sortTypes = ['colour', 'number', 'text', 'text', 'date', 'date', 'date', 'none'];
			this.workdone.dataMaps['racked'] = ['rack_colour', 'invoice_number', 'wine_name', 'sg_reading', 'start_date', 'bottling_date', 'racking_date', 'notes'];
		} else {
			this.workdone.sections['racked'].visible = 'no';
		}

		if( this.workdone.worklist == 'all' || this.workdone.worklist == 'filtered' ) {
			this.workdone.sections['filtered'].visible = 'yes';
			this.workdone.sections['filtered'].num_cols = 7;
			this.workdone.sections['filtered'].headerValues = ['', 'INV#', 'Wine', 'Racked', 'BD', 'FD', ''];
			this.workdone.sections['filtered'].sortTypes = ['colour', 'number', 'text', 'date', 'date', 'date', 'none'];
			this.workdone.dataMaps['filtered'] = ['filter_colour', 'invoice_number', 'wine_name', 'rack_date', 'bottling_date', 'filtering_date', 'notes'];
		} else {
			this.workdone.sections['filtered'].visible = 'no';
		}

		if( this.workdone.worklist == 'all' || this.workdone.worklist == 'bottled' ) {
			this.workdone.sections['bottled'].visible = 'yes';
			this.workdone.sections['bottled'].num_cols = 6;
			this.workdone.sections['bottled'].headerValues = ['INV#', 'Wine', 'Filtered', 'BD', ''];
			this.workdone.sections['bottled'].sortTypes = ['text', 'number', 'date', 'date', 'none'];
			this.workdone.dataMaps['bottled'] = ['invoice_number', 'wine_name', 'filter_date', 'bottling_date', 'notes'];
		} else {
			this.workdone.sections['bottled'].visible = 'no';
		}

		var td = new Date();	// Todays date
		var pieces = this.workdone.workdate.split('-');
		var dr = new Date(pieces[0], Number(pieces[1])-1, pieces[2]);		// Date requested
		// If date requested is in the future, request the schedule
		var args = {'business_id':M.curBusinessID};
		if( dr > td ) {
			if( this.workdone.worklist == 'ordered' ) {
				this.workdone.title = 'Order Schedule';
				args.order_date = this.workdone.workdate;
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'order_date':this.workdone.workdate});
			} else if( this.workdone.worklist == 'started' ) {
				this.workdone.title = 'Start Schedule';
				args.started_date = this.workdone.workdate;
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'started_date':this.workdone.workdate});
			} else if( this.workdone.worklist == 'racked' ) {
				this.workdone.title = 'Rack Schedule';
				this.workdone.sections['racked'].num_cols = 10;
				this.workdone.sections['racked'].headerValues = ['', 'INV#', 'Wine', 'Type', 'SG', 'Started', 'BD', 'RD', '', ''];
				this.workdone.sections['racked'].sortTypes = ['colour', 'number', 'text', 'text', 'text', 'date', 'date', 'date', 'none'];
				this.workdone.dataMaps['racked'] = ['rack_colour', 'invoice_number', 'wine_name', 'wine_type_and_length', 'sg_reading', 'start_date', 'bottling_date', 'racking_date', 'notes'];
				args.racking_date = this.workdone.workdate;
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'racking_date':this.workdone.workdate});
			} else if( this.workdone.worklist == 'filtered' ) {
				this.workdone.title = 'Filter Schedule';
				this.workdone.sections['filtered'].num_cols = 9;
				this.workdone.sections['filtered'].headerValues = ['', 'INV#', 'Wine', 'Type', 'Racked', 'BD', 'FD', ''];
				this.workdone.sections['filtered'].sortTypes = ['colour', 'number', 'text', 'text', 'date', 'date', 'date', 'none'];
				this.workdone.dataMaps['filtered'] = ['filter_colour', 'invoice_number', 'wine_name', 'wine_type_and_length', 'rack_date', 'bottling_date', 'filtering_date', 'notes'];
				args.filtering_date = this.workdone.workdate;
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'filtering_date':this.workdone.workdate});
			} else if( this.workdone.worklist == 'bottled' ) {
				this.workdone.title = 'Bottle Schedule';
				args.filtering_date = this.workdone.workdate;
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'bottling_date':this.workdone.workdate});
			} else {
				this.workdone.title = 'Schedule';
				args.schedule_date = this.workdone.workdate;
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'schedule_date':this.workdone.workdate});
			}
		} else {
			if( this.workdone.worklist == 'ordered' ) {
				this.workdone.title = 'Ordered';
				args.order_date = this.workdone.workdate;
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'order_date':this.workdone.workdate});
			} else if( this.workdone.worklist == 'started' ) {
				this.workdone.title = 'Started';
				args.started_date = this.workdone.workdate;
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'started_date':this.workdone.workdate});
			} else if( this.workdone.worklist == 'racked' ) {
				this.workdone.title = 'Racked';
				args.racked_date = this.workdone.workdate;
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'racked_date':this.workdone.workdate});
			} else if( this.workdone.worklist == 'filtered' ) {
				this.workdone.title = 'Filtered';
				args.filtered_date = this.workdone.workdate;
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'filtered_date':this.workdone.workdate});
			} else if( this.workdone.worklist == 'bottled' ) {
				this.workdone.title = 'Bottled';
				args.bottled_date = this.workdone.workdate;
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'bottled_date':this.workdone.workdate});
			} else {
				this.workdone.title = 'Work Completed';
				args.work_date = this.workdone.workdate;
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'work_date':this.workdone.workdate});
			}
		}

		var rsp = M.api.getJSONCb('ciniki.wineproduction.list', 
			{'business_id':M.curBusinessID, 'work_date':this.workdone.workdate}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				} 
				var p = M.ciniki_wineproduction_main.workdone;
				p.data = {'ordered':[], 'started':[], 'racked':[], 'filtered':[], 'bottled':[]};

				if( p.worklist == 'all' ) {
					// If necessary, divide up the orders.
					for(i in rsp.orders) {
						if( rsp.orders[i].order.status == 10 ) {
							p.data.ordered.push(rsp.orders[i]);
						} else if( rsp.orders[i].order.status == 20 ) {
							p.data.started.push(rsp.orders[i]);
						} else if( rsp.orders[i].order.status == 25 ) {
							p.data.started.push(rsp.orders[i]);
						} else if( rsp.orders[i].order.status == 30 ) {
							p.data.racked.push(rsp.orders[i]);
						} else if( rsp.orders[i].order.status == 40 ) {
							p.data.filtered.push(rsp.orders[i]);
						} else if( rsp.orders[i].order.status == 60 ) {
							p.data.bottled.push(rsp.orders[i]);
						}
					}
				} else {
					p.data[p.worklist] = rsp.orders;
				}

				p.refresh();
				p.show();
			});
	};

	this.showOrders = function(cb, ordertype) {
		if( cb != null ) {
			this.list.cb = cb;
		} else if( this.list.cb == null || this.list.cb == '' ) {
			this.list.cb = 'M.ciniki_wineproduction_main.showMain();';
		}
		var rsp = null;
		var rsp2 = null;
		var args = null;
		var args2 = null;
		this.list.ordertype = ordertype;
		if( ordertype == 'start' || ordertype == 'ordered' ) {
			if( ordertype == 'start' ) {
				this.list.buttonText = 'Started';
				this.list.title = 'Wines to be started';
				this.list.sections['completed'].visible = 'yes';
				this.list.sections['completed'].label = 'Started Today';
			} else {
				this.list.buttonText = 'Started';
				this.list.title = 'Ordered';
				this.list.sections['completed'].visible = 'no';
				this.list.sections['completed'].label = '';
			}
			this.list.sections['pending'].headerValues = ['INV#', 'Wine', 'Type', 'Ordered', 'BD', '', ''];
			this.list.sections['pending'].num_cols = 7;
			this.list.sections['pending'].sortTypes = ['number', 'text', 'text', 'date', 'date', 'none', 'none'];
			this.list.dataMaps['pending'] = ['invoice_number_and_flags', 'wine_and_customer', 'wine_type_and_length', 'order_date', 'bottling_date_and_flags', 'buttons', 'notes'];
			this.list.sections['completed'].headerValues = ['', 'INV#', 'Wine', 'Type', 'Ordered', 'BD', ''];
			this.list.sections['completed'].num_cols = 7;
			this.list.sections['completed'].sortTypes = ['colour', 'number', 'text', 'text', 'date', 'date', 'none'];
			this.list.dataMaps['completed'] = ['rack_colour', 'invoice_number_and_flags', 'wine_and_customer', 'wine_type_and_length', 'order_date', 'bottling_date_and_flags', 'notes'];
			args = {'business_id':M.curBusinessID, 'status':'10', 'sorting':'invoice_number'};
//			rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status':'10', 'sorting':'invoice_number'});
			if( ordertype == 'start' ) {
				args2 = {'business_id':M.curBusinessID, 'status':'20', 'started_date':'today', 'sorting':'invoice_number'};
//				rsp2 = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status':'20', 'started_date':'today', 'sorting':'invoice_number'});
			}
		} 
		
		else if( ordertype == 'todayssgreadings' ) {
			this.list.buttonText = 'Racked';
			this.list.title = 'Todays SG Readings';
			this.list.sections['completed'].label = 'Read Today';
			this.list.sections['completed'].visible = 'yes';

			this.list.sections['pending'].num_cols = 6;
			this.list.sections['pending'].headerValues = ['', 'INV#', 'Wine', 'Type', 'SG', ''];
			this.list.sections['pending'].sortTypes = ['colour', 'number', 'text', 'text', 'number', 'none'];
			this.list.dataMaps['pending'] = ['rack_colour', 'invoice_number_and_flags', 'wine_and_customer', 'wine_type_and_length', 'sg_reading', 'sgbuttons'];
			this.list.sections['completed'].label = 'SG Read';
			this.list.sections['completed'].visible = 'yes';
			this.list.sections['completed'].num_cols = 6;
			this.list.sections['completed'].headerValues = ['', 'INV#', 'Wine', 'Type', 'SG', ''];
			this.list.dataMaps['completed'] = ['rack_colour', 'invoice_number_and_flags', 'wine_and_customer', 'wine_type_and_length', 'sg_reading', 'sgbuttons'];
			args = {'business_id':M.curBusinessID, 'status':'20', 'before_racking_date':encodeURIComponent('today+4days'), 'sorting':'racking_date,invoice_number'};
			args2 = {'business_id':M.curBusinessID, 'status':'25', 'sorting':'invoice_number'};
//			rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status':'20', 'before_racking_date':encodeURIComponent('today+4days'), 'sorting':'racking_date,invoice_number'});
//			rsp2 = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status':'25', 'sorting':'invoice_number'});
		
		}
		
		else if( ordertype == 'todaysracking' || ordertype == 'started' || ordertype == 'sgready' || ordertype == 'rack' || ordertype == 'futureracking' ) {
			this.list.buttonText = 'Racked';
			if( ordertype == 'started' ) {
				this.list.title = 'Started';
				this.list.sections['completed'].label = '';
				this.list.sections['completed'].visible = 'no';
				args = {'business_id':M.curBusinessID, 'status':'20', 'sorting':'invoice_number'};
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status':'20', 'sorting':'invoice_number'});
			} else if( ordertype == 'sgready' ) {
				this.list.title = 'SG Ready';
				this.list.sections['completed'].label = '';
				this.list.sections['completed'].visible = 'no';
				args = {'business_id':M.curBusinessID, 'status':'25', 'sorting':'invoice_number'};
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status':'25', 'sorting':'invoice_number'});
			} else if( ordertype == 'todaysracking' ) {
				this.list.title = 'Todays Racking';
				this.list.sections['completed'].label = 'Racked Today';
				this.list.sections['completed'].visible = 'yes';
				args = {'business_id':M.curBusinessID, 'status':'25', 'sorting':'racking_date,invoice_number'};
				args2 = {'business_id':M.curBusinessID, 'status':'30', 'racked_date':'today', 'sorting':'invoice_number'};
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status':'25', 'sorting':'racking_date,invoice_number'});
//				rsp2 = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status':'30', 'racked_date':'today', 'sorting':'invoice_number'});
			} else {
				this.list.title = 'Wines to be racked';
				this.list.sections['completed'].label = '';
				this.list.sections['completed'].visible = 'no';
				args = {'business_id':M.curBusinessID, 'status':'25', 'after_racking_date':'today', 'sorting':'invoice_number'};
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status':'25', 'after_racking_date':'today', 'sorting':'invoice_number'});
//				rsp2 = null;
			}
			this.list.buttonText = 'Racked';
			this.list.sections['pending'].num_cols = 10;
			this.list.sections['pending'].headerValues = ['', 'INV#', 'Wine', 'Type', 'SG', 'Started', 'BD', 'RD', '', ''];
			this.list.sections['pending'].sortTypes = ['colour', 'number', 'text', 'text', 'text', 'date', 'date', 'date', 'none', 'none'];
			this.list.dataMaps['pending'] = ['rack_colour', 'invoice_number_and_flags', 'wine_and_customer', 'wine_type_and_length', 'sg_reading', 'start_date', 'bottling_date_and_flags', 'racking_date', 'buttons', 'notes'];
			this.list.sections['completed'].num_cols = 9;
			this.list.sections['completed'].headerValues = ['', 'INV#', 'Wine', 'Type', 'SG', 'Started', 'BD', 'FD', '', ''];
			this.list.sections['completed'].sortTypes = ['colour', 'number', 'text', 'text', 'number', 'date', 'date', 'date', 'none'];
			this.list.dataMaps['completed'] = ['filter_colour', 'invoice_number_and_flags', 'wine_and_customer', 'wine_type_and_length', 'sg_reading', 'start_date', 'bottling_date_and_flags', 'filtering_date', 'notes'];
		}
		
		else if( ordertype == 'todaysfiltering' || ordertype == 'racked' || ordertype == 'filter' || ordertype == 'futurefiltering') {
			this.list.buttonText = 'Filtered';
			if( ordertype == 'racked' ) {
				this.list.title = 'Racked';
				this.list.sections['completed'].label = '';
				this.list.sections['completed'].visible = 'no';
				args = {'business_id':M.curBusinessID, 'status':'30', 'sorting':'invoice_number'};
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status':'30', 'sorting':'invoice_number'});
			} else if( ordertype == 'todaysfiltering' ) {
				this.list.title = 'Todays Filtering';
				this.list.sections['completed'].label = 'Filtered Today';
				this.list.sections['completed'].visible = 'yes';
				// Want to include today, so should be before_tomorrow
				args = {'business_id':M.curBusinessID, 'status':'30', 'before_filtering_date':'tomorrow', 'sorting':'invoice_number'};
				args2 = {'business_id':M.curBusinessID, 'status':'40', 'filtered_date':'today', 'sorting':'invoice_number'};
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status':'30', 'before_filtering_date':'tomorrow', 'sorting':'invoice_number'});
//				rsp2 = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status':'40', 'filtered_date':'today', 'sorting':'invoice_number'});
			} else {
				this.list.title = 'Wines to be filtered';
				this.list.sections['completed'].label = '';
				this.list.sections['completed'].visible = 'no';
				args = {'business_id':M.curBusinessID, 'status':'30', 'after_filtering_date':'today', 'sorting':'invoice_number'};
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status':'30', 'after_filtering_date':'today', 'sorting':'invoice_number'});
//				rsp2 = null;
			}
			this.list.sections['pending'].headerValues = ['', 'INV#', 'Wine', 'Type', 'Racked', 'BD', 'FD', '', ''];
			this.list.sections['pending'].num_cols = 9;
			this.list.sections['pending'].sortTypes = ['colour', 'number', 'text', 'text', 'date', 'date', 'date', 'none', 'none'];
			this.list.dataMaps['pending'] = ['filter_colour', 'invoice_number_and_flags', 'wine_and_customer', 'wine_type_and_length', 'rack_date', 'bottling_date_and_flags', 'filtering_date', 'buttons', 'notes'];
			this.list.sections['completed'].headerValues = ['', 'INV#', 'Wine', 'Type', 'Racked', 'BD', 'FD', ''];
			this.list.sections['completed'].num_cols = 8;
			this.list.sections['completed'].sortTypes = ['colour', 'number', 'text', 'text', 'date', 'date', 'date', 'none'];
			this.list.dataMaps['completed'] = ['filter_colour', 'invoice_number_and_flags', 'wine_and_customer', 'wine_type_and_length', 'rack_date', 'bottling_date_and_flags', 'filtering_date', 'notes'];
		} 
		
		else if( ordertype == 'filtered' || ordertype == 'futurebottle' ) {
			this.list.buttonText = 'Bottled';
			if( ordertype == 'filtered' ) {
				this.list.title = 'Filtered';
				this.list.sections['completed'].visible = 'no';
				this.list.sections['completed'].label = '';
				args = {'business_id':M.curBusinessID, 'status':'40', 'sorting':'invoice_number'};
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status':'40', 'sorting':'invoice_number'});
			} else {
				this.list.title = 'Wines to be bottled';
				this.list.sections['completed'].visible = 'yes';
				this.list.sections['completed'].label = 'Bottled Today';
				args = {'business_id':M.curBusinessID, 'status':'40', 'sorting':'invoice_number'};
				args2 = {'business_id':M.curBusinessID, 'status':'60', 'bottled_date':'today', 'sorting':'invoice_number'};
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status':'40', 'sorting':'invoice_number'});
//				rsp2 = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status':'60', 'bottled_date':'today', 'sorting':'invoice_number'});
			}
			this.list.sections['pending'].headerValues = ['INV#', 'Wine', 'Filtered', 'BD', '', ''];
			this.list.sections['pending'].num_cols = 6;
			this.list.sections['pending'].sortTypes = ['number', 'text', 'date', 'date', 'none', 'none'];
			this.list.dataMaps['pending'] = ['invoice_number', 'wine_and_customer', 'filter_date', 'bottling_date_and_flags', 'buttons', 'notes'];
			this.list.sections['completed'].headerValues = ['INV#', 'Wine', 'Filtered', 'BD', ''];
			this.list.sections['completed'].num_cols = 6;
			this.list.sections['completed'].sortTypes = ['number', 'text', 'date', 'date', 'none', 'none'];
			this.list.dataMaps['completed'] = ['invoice_number', 'wine_and_customer', 'filter_date', 'bottling_date_and_flags', 'notes'];
		} 
		
		else if( ordertype == 'latewines' ) {
			this.list.title = 'Late Wines';
			this.list.sections['pending'].num_cols = 9;
			this.list.sections['pending'].headerValues = ['INV#', 'Wine', 'Type', 'Ordered', 'Started', 'RD', 'FD', 'BD', ''];
			this.list.sections['pending'].sortTypes = ['number', 'text', 'text', 'date', 'date', 'date', 'date', 'date', 'none'];
			this.list.dataMaps['pending'] = ['invoice_number', 'wine_and_customer', 'wine_type_and_length', 'order_date', 'start_date', 'racking_date', 'filtering_date', 'bottling_date_and_flags', 'notes'];
			this.list.sections['completed'].label = '';
			this.list.sections['completed'].visible = 'no';
			args = {'business_id':M.curBusinessID, 'status_list':'0,10,20,25,30', 'bottling_date':'late_wine', 'sorting':'bottling_date'};
//			rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status_list':'0,10,20,25,30', 'bottling_date':'late_wine', 'sorting':'bottling_date'});
//			rsp2 = null;
		} 
		else if( ordertype == 'ctb' ) {
			this.list.title = 'Call to Book';
			this.list.sections['pending'].num_cols = 9;
			this.list.sections['pending'].headerValues = ['', 'INV#', 'Wine', 'Type', 'OD', 'SD', 'RD', 'FD', 'BD', ''];
			this.list.sections['pending'].sortTypes = ['', 'number', 'text', 'text', 'date', 'date', 'date', 'date', 'date', 'none'];
			this.list.dataMaps['pending'] = ['bottling_flags_colour', 'invoice_number_and_status', 'wine_and_customer', 'wine_type_and_length', 'order_date', 'start_date', 'racking_date', 'filtering_date', 'bottling_date_and_flags', 'notes'];
			this.list.sections['completed'].label = '';
			this.list.sections['completed'].visible = 'no';
			args = {'business_id':M.curBusinessID, 'status_list':'0,10,20,25,30,40', 'bottling_date':'ctb', 'sorting':'invoice_number'};
//			rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status_list':'0,10,20,25,30,40', 'bottling_date':'ctb', 'sorting':'invoice_number'});
//			rsp2 = null;
		} 
		
		if( args == null ) {
			return false;
		}
		M.startLoad();
		var rsp = M.api.getJSONCb('ciniki.wineproduction.list', args, function(rsp) {
			if( rsp.stat != 'ok' ) {
				M.stopLoad();
				M.api.err(rsp);
				return false;
			} 
			var p = M.ciniki_wineproduction_main.list;
			p.data.pending = rsp.orders;

			if(args2 != null ) {
				var rsp2 = M.api.getJSON('ciniki.wineproduction.list', args2);
				if( rsp2.stat != 'ok' ) {
					M.stopLoad();
					M.api.err(rsp2);
					return false;
				}
				p.data.completed = rsp2.orders;
			}
			M.stopLoad();
			p.refresh();
			p.show();
		});

//		if( rsp2 != null && rsp2.stat != 'ok' ) {
//			M.api.err(rsp2);
//			return false;
//		}
//
//		if( rsp2 != null ) {
//			this.list.data.completed = rsp2.orders;
//		}
//
//		this.list.refresh();
//		this.list.show();
	}

	this.refreshList = function() {
		this.list.show();
	}


	this.addOrder = function() {
		// Add the customer if required
		if( this.add.formValue('customer_id') == 0 ) {
			var customer_name = M.gE(this.add.panelUID + '_customer_id_fkidstr').value;
			var rsp = M.api.getJSON('ciniki.customers.add', {'business_id':M.curBusinessID, 'name':encodeURIComponent(customer_name)});
			if( rsp.stat != 'ok' ) {
				M.api.err(rsp);
				return false;
			}
			M.gE(this.add.panelUID + '_customer_id').value = rsp['id'];
		}

		var bd = M.gE(this.add.panelUID + '_bottling_date');
		if( bd.value.match(/ctb/i) ) {
			bd.value = '';
		}

		// Serialize the basic order information
		var content = this.add.serializeFormSection('yes', 'info')
			+ this.add.serializeFormSection('yes', 'bottling')
			+ this.add.serializeFormSection('yes', '_notes');
		if( content == '' ) {
			return false;
		}
		var wines = [];

		var c = this.add.sectionCount('wines');
		for(var i=0;i<c;i++) {
//			if( this.add.sections['wines' + ext] != null && this.add.formFieldValue('product_id' + ext) == 0 ) {
//			if( this.add.formFieldValue(this.add.sections.wines.fields.product_id, 'product_id' + ext) == 0 ) {
			if( M.gE(this.add.panelUID + '_product_id_' + i).value == 0 ) {
				var wine_name = M.gE(this.add.panelUID + '_product_id_' + i + '_fkidstr').value;
				var wine_type = M.gE(this.add.panelUID + '_wine_type_' + i).value;
				var kit_length = M.gE(this.add.panelUID + '_kit_length_' + i).value;
				var rsp = M.api.getJSON('ciniki.products.productAdd', {'business_id':M.curBusinessID, 
					'name':encodeURIComponent(wine_name),
					'type_name_s':'Wine Kit',
					'status':10,
					'webflags':0,
					'detail01':encodeURIComponent(wine_type),
					'detail02':encodeURIComponent(kit_length)
					});
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				M.gE(this.add.panelUID + '_product_id_' + i).value = rsp.id;
			}
			// Check if there are multiple of this wine and add A/B/C after invoice number
			var pid = M.gE(this.add.panelUID + '_product_id_' + i).value;
			if( wines[pid] != null ) {
				wines[pid]['count'] += 1;
			} else {
				wines[pid] = {'count':1, 'cur':1};
			}
		}

		for(var i=0;i<c;i++) {
			// The status must be set to 10, we have removed the dropdown selection from the add form.
			var sc = this.add.serializeFormSection('yes', 'wines', i);
			var pid = M.gE(this.add.panelUID + '_product_id_' + i).value;
			if( wines[pid]['count'] > 1 ) {
				sc += '&batch_count=' + wines[pid]['cur'];
				wines[pid]['cur'] += 1;
			}
			var rsp = M.api.postJSON('ciniki.wineproduction.add', {'business_id':M.curBusinessID, 'status':10}, content + sc);
			if( rsp.stat != 'ok' ) {
				M.api.err(rsp);
				return false;
			} 
		}
		
		this.add.close();
	}

	this.actionOrder = function(orderID, action, kit_length) {
		if( action == 'Started' ) {
			var batch_code = prompt("Enter batch code", "");
			if( batch_code == null ) { // User clicked cancel
				return false;
			} 
			if( batch_code == '' ) {
				alert("Invalid batch code");
				return false;
			}
		}
		var rsp = M.api.getJSONCb('ciniki.wineproduction.actionOrder', 
			{'business_id':M.curBusinessID, 'wineproduction_id':orderID, 'action':action, 
				'kit_length':kit_length, 'batch_code':batch_code}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					} 
					M.ciniki_wineproduction_main.showOrders(null, M.ciniki_wineproduction_main.list.ordertype);		
				});
	};

	this.actionScheduleOrder = function(orderID, action, kit_length) {
		var rsp = M.api.getJSONCb('ciniki.wineproduction.actionOrder', 
			{'business_id':M.curBusinessID, 'wineproduction_id':orderID, 
				'action':action, 'kit_length':kit_length}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					} 
					M.ciniki_wineproduction_main.showSchedule();		
			});
	};

	this.actionScheduleSGbutton = function(oid, sg) {
		var rsp = M.api.getJSONCb('ciniki.wineproduction.actionOrder', 
			{'business_id':M.curBusinessID, 'wineproduction_id':oid, 'action':'SGRead', 'sg_reading':sg}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				} 
				M.ciniki_wineproduction_main.showSchedule();
			});
	};

	this.quickSGbutton = function(orderID, sg) {
		var rsp = M.api.getJSONCb('ciniki.wineproduction.actionOrder', 
			{'business_id':M.curBusinessID, 'wineproduction_id':orderID, 'action':'SGRead', 'sg_reading':sg}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				} 
				M.ciniki_wineproduction_main.showOrders(null, 'todayssgreadings');
			});
	};

	this.updateSG = function(section, r, c, d) {
		M.ciniki_wineproduction_main.quickSGbutton(M.ciniki_wineproduction_main.list.data[section][r].order.id, d);
	}

	this.showOrder = function(oid, cb) {
		if( oid != null ) {
			this.order.order_id = oid;
		}
		var rsp = M.api.getJSONCb('ciniki.wineproduction.getOrder', 
			{'business_id':M.curBusinessID, 'wineproduction_id':this.order.order_id}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_wineproduction_main.order;
				p.data = rsp.order;
				p.customer_id = rsp.order.customer_id;
				if( rsp.order.customer_id > 0 ) {
					p.sections.customer.addTxt = 'Edit Customer';
					p.sections.customer.changeTxt = 'Change Customer';
//					p.data.customer = M.ciniki_wineproduction_main.setupCustomer(rsp.order.customer);
					p.data.customer = rsp.order.customer;
				} else {
					p.sections.customer.addTxt = 'Add Customer';
					p.sections.customer.changeTxt = '';
				}

				p.refresh();
				p.show(cb);
			});
	};

	this.updateOrderCustomer = function(cid) {
		// If the customer has changed, then update the details of the invoice
		if( cid != null && this.order.customer_id != cid ) {
			this.order.customer_id = cid;
		}
		// Update the customer details
		M.api.getJSONCb('ciniki.customers.customerDetails', {'business_id':M.curBusinessID, 
			'customer_id':this.order.customer_id, 'phones':'yes', 'emails':'yes'}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_wineproduction_main.order;
//				p.data.customer = M.ciniki_wineproduction_main.setupCustomer(rsp.customer);
				p.data.customer = rsp.details;
				p.refreshSection('customer');
				p.show();
			});
	};

//	this.setupCustomer = function(c) {
//		customer = {};
//		customer['name'] = {'label':'Name', 'value':c.display_name};
//		if( c.phone_home != null && c.phone_home != '' ) {
//			customer.phone_home = {'label':'Home Phone', 'value':c.phone_home};
//		}
//		if( c.phone_work != null && c.phone_work != '' ) {
//			customer.phone_work = {'label':'Work Phone', 'value':c.phone_work};
//		}
//		if( c.phone_cell != null && c.phone_cell != '' ) {
//			customer.phone_cell = {'label':'Cell Phone', 'value':c.phone_cell};
//		}
//		if( c.phone_fax != null && c.phone_fax != '' ) {
//			customer.phone_fax = {'label':'Fax', 'value':c.phone_fax};
//		}
//		if( c.emails != null && c.emails != '' ) {
//			customer.emails = {'label':'Email', 'value':c.emails};
//		}
//		return customer;
//	};

	this.showSchedule = function(cb, status, scheduleDate) {
		if( cb != null ) { this.schedule.cb = cb; }
		if( scheduleDate == null || scheduleDate == '' ) {
			if( this.schedule.date == null || this.schedule.date == '' ) {
				var dt = new Date();
				this.schedule.date = dt.getFullYear() + '-' + (dt.getMonth()+1) + '-' + dt.getDate();
			}
			// else, workdone should not be changed
		} else {
			this.schedule.date = scheduleDate;
		}
		var rsp = M.api.getJSONCb('ciniki.wineproduction.statsSchedule', 
			{'business_id':M.curBusinessID, 'start_date':this.schedule.date, 'days':14}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_wineproduction_main.schedule;
				p.data.stats = rsp.stats;

				var dt = new Date();
				p.data['stats']['days'] = {'0':M.monthOfYear(dt), '1':'...'};
				for(i=2;i<16;i++) {
		//			dt.setTime(dt.getTime()+86400000);
					p.data.stats.days[i] = "<span class='subtext'>" + rsp.stats.racking[i-2].stat.weekday + "</span><span class='maintext'>" + rsp.stats.racking[i-2].stat.day + "</span>";
				}
				p.data.stats.days['16'] = '...';
			
				M.ciniki_wineproduction_main.showScheduleList(p.ordertype);
			});
	}

	this.showScheduleList = function(ordertype, scheduleDate, displayDate) {
		var rsp = null;
		this.schedule.ordertype = ordertype;
		if( scheduleDate == null || scheduleDate == '' ) {
			if( this.schedule.ordersDate == null || this.schedule.ordersDate == '' ) {
				var dt = new Date();
				this.schedule.ordersDate = dt.getFullYear() + '-' + (dt.getMonth()+1) + '-' + dt.getDate();
			}
			// else, workdone should not be changed
		} else {
			this.schedule.ordersDate = scheduleDate;
		}
		var dt = new Date();
		var todaysDate = dt.getFullYear() + '-' + (dt.getMonth()+1) + '-' + dt.getDate();
		if( displayDate == null || displayDate == '' ) {
			if( this.schedule.displayDate == null || this.schedule.displayDate == '' ) {
				this.schedule.displayDate = 'today';
			}
			// else, workdone should not be changed
		} else {
			this.schedule.displayDate = displayDate;
		}
		var args = {'business_id':M.curBusinessID};
		if( ordertype == 'racking' || ordertype == 'before_racking' ) {
			this.schedule.buttonText = 'Racked';
			if( displayDate != null ) {
				this.schedule.sections.orders.label = 'Racking - ' + displayDate;
			} else {
				this.schedule.sections.orders.label = 'Racking';
			}
			if( ordertype == 'racking' ) {
				args.status_list = '20,25';
				args.racking_date = this.schedule.ordersDate;
				args.sorting = 'invoice_number';
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status_list':'20,25', 'racking_date':this.schedule.ordersDate, 'sorting':'invoice_number'});
			} else if( ordertype == 'after_racking' ) {
				args.status_list = '20,25';
				args.after_racking_date = this.schedule.ordersDate;
				args.sorting = 'invoice_number';
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status_list':'20,25', 'after_racking_date':this.schedule.ordersDate, 'sorting':'invoice_number'});
			} else {
				args.status_list = '20,25';
				args.before_racking_date = this.schedule.ordersDate + ' 12:00AM';
				args.sorting = 'invoice_number';
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status_list':'20,25', 'before_racking_date':this.schedule.ordersDate + ' 12:00AM', 'sorting':'invoice_number'});
			}
			this.schedule.sections.orders.num_cols = 10;
			this.schedule.sections.orders.headerValues = ['', 'INV#', 'Wine', 'Type', 'SG', 'Started', 'BD', 'RD', '', ''];
			this.schedule.sections.orders.sortTypes = ['colour', 'number', 'text', 'text', 'number', 'date', 'date', 'date', 'none', 'none'];
			this.schedule.dataMaps.orders = ['rack_colour', 'invoice_number', 'wine_and_customer', 'wine_type_and_length', 'sg_reading', 'start_date', 'bottling_date_and_flags', 'racking_date', 'buttons', 'notes'];
		} 
		
		else if( ordertype == 'filtering' || ordertype == 'before_filtering' || ordertype == 'after_filtering' ) {
			this.schedule.buttonText = 'Filter Today';
			if( this.schedule.ordersDate == todaysDate ) {
				this.schedule.buttonText = 'Filtered';
			}
			if( displayDate != null ) {
				this.schedule.sections.orders.label = 'Filtering - ' + displayDate;
			} else {
				this.schedule.sections.orders.label = 'Filtering';
			}
			if( ordertype == 'filtering' ) {
				args.status = '30';
				args.filtering_date = this.schedule.ordersDate;
				args.sorting = 'invoice_number';
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status':'30', 'filtering_date':this.schedule.ordersDate, 'sorting':'invoice_number'});
			} else if( ordertype == 'after_filtering' ) {
				args.status = '30';
				args.after_filtering_date = this.schedule.ordersDate;
				args.sorting = 'invoice_number';
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status':'30', 'after_filtering_date':this.schedule.ordersDate, 'sorting':'invoice_number'});
			} else {
				args.status = '30';
				args.before_filtering_date = this.schedule.ordersDate + ' 12:00AM';
				args.sorting = 'invoice_number';
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status':'30', 'before_filtering_date':this.schedule.ordersDate + ' 12:00AM', 'sorting':'invoice_number'});
			}
			this.schedule.sections.orders.headerValues = ['', 'INV#', 'Wine', 'Type', 'Racked', 'BD', 'FD', '', ''];
			this.schedule.sections.orders.num_cols = 9;
			this.schedule.sections.orders.sortTypes = ['colour', 'number', 'text', 'text', 'date', 'date', 'date', 'none', 'none'];
			this.schedule.dataMaps.orders = ['filter_colour', 'invoice_number', 'wine_and_customer', 'wine_type_and_length', 'rack_date', 'bottling_date_and_flags', 'filtering_date', 'buttons', 'notes'];
		} 
		else if( ordertype == 'bottling' || ordertype == 'before_bottling' || ordertype == 'after_bottling' ) {
			this.schedule.buttonText = 'Filter Today';
//			if( this.schedule.ordersDate == todaysDate ) {
//				this.schedule.buttonText = 'Bottled';
//			}
			if( displayDate != null ) {
				this.schedule.sections.orders.label = 'Bottling - ' + displayDate;
			} else {
				this.schedule.sections.orders.label = 'Bottling';
			}
			if( ordertype == 'bottling' ) {
				args.status_list = '10,20,25,30,40';
				args.bottling_date = this.schedule.ordersDate;
				args.sorting = 'appointments';
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status_list':'10,20,25,30,40', 'bottling_date':this.schedule.ordersDate, 'sorting':'appointments'});
			} else if( ordertype == 'after_bottling' ) {
				args.status_list = '10,20,25,30,40';
				args.after_bottling_date = this.schedule.ordersDate;
				args.sorting = 'appointments';
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status_list':'10,20,25,30,40', 'after_bottling_date':this.schedule.ordersDate, 'sorting':'appointments'});
			} else {
				args.status_list = '10,20,25,30,40';
				args.before_bottling_date = this.schedule.ordersDate;
				args.sorting = 'appointments';
//				rsp = M.api.getJSON('ciniki.wineproduction.list', {'business_id':M.curBusinessID, 'status_list':'10,20,25,30,40', 'before_bottling_date':this.schedule.ordersDate, 'sorting':'appointments'});
			}
			this.schedule.sections.orders.headerValues = ['', 'INV#', 'Wine', 'Type', 'Racked', 'BD', 'FD', '', ''];
			this.schedule.sections.orders.num_cols = 8;
			this.schedule.sections.orders.sortTypes = ['colour', 'number', 'text', 'text', 'date', 'date', 'date', 'none', 'none'];
			this.schedule.dataMaps.orders = ['bottling_flags_colour', 'invoice_number_and_status', 'wine_and_customer', 'wine_type_and_length', 'rack_date', 'bottling_date_and_flags', 'filtering_date', 'buttons', 'notes'];
		} 

		if( args.sorting != null ) {
			var rsp = M.api.getJSONCb('ciniki.wineproduction.list', args, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				} 
				var p = M.ciniki_wineproduction_main.schedule;
				p.data.orders = rsp.orders;
				p.sections.orders.label += ' <span class="count">' + rsp.orders.length + '</span>';

				p.refresh();
				p.show();
			});
		} else {
			this.schedule.refresh();
			this.schedule.show();
		}
	};

	this.saveOrder = function() {
		// First check to see if customer needs to be added
//		if( this.order.formValue('customer_id') == 0 ) {
//			var customer_name = M.gE(this.order.panelUID + '_customer_id_fkidstr').value;
//			var rsp = M.api.getJSON('ciniki.customers.add', 
//				{'business_id':M.curBusinessID, 'name':encodeURIComponent(customer_name)});
//			if( rsp.stat != 'ok' ) {
//				M.api.err(rsp);
//				return false;
//			}
//			M.gE(this.order.panelUID + '_customer_id').value = rsp['id'];
//		}
		// Check to see if product_id needs to be updated
		if( this.order.formValue('product_id') == 0 ) {
			var wine_name = M.gE(this.order.panelUID + '_product_id_fkidstr').value;
			var wine_type = M.gE(this.order.panelUID + '_wine_type').value;
			var kit_length = M.gE(this.order.panelUID + '_kit_length').value;
			var rsp = M.api.getJSON('ciniki.products.productAdd', {'business_id':M.curBusinessID, 
				'name':encodeURIComponent(wine_name),
				'wine_type':encodeURIComponent(wine_type),
				'kit_length':encodeURIComponent(kit_length),
				});
			if( rsp.stat != 'ok' ) {
				M.api.err(rsp);
				return false;
			}
			M.gE(this.order.panelUID + '_product_id').value = rsp.id;
		}

		var bd = M.gE(this.order.panelUID + '_bottling_date');
		if( bd.value.match(/ctb/i) ) {
			bd.value = '';
		}
		var c = this.order.serializeForm('no');
		// Check if customer_id has changed
		if( this.order.customer_id != 0 && this.order.data.customer_id != this.order.customer_id ) {
			c += 'customer_id=' + this.order.customer_id + '&';
		}
		if( c != '' ) {
			var rsp = M.api.postJSON('ciniki.wineproduction.update', 
				{'business_id':M.curBusinessID, 'wineproduction_id':M.ciniki_wineproduction_main.order.order_id}, c);
			if( rsp.stat != 'ok' ) {
				M.api.err(rsp);
				return false;
			} 
		}
		M.ciniki_wineproduction_main.order.close();
		// this.showOrders(null, this.list.ordertype);		
	}

	this.searchOrders = function(search_str) {
		var rsp = M.api.getJSONBg('ciniki.wineproduction.searchFull', {'business_id':M.curBusinessID, 'search_str':search_str, 'limit':100});
		if( rsp['stat'] != 'ok' ) {
			M.api.err(rsp);
			return false;
		}
		this.search.data['main'] = rsp['orders'];
		this.search.sections['main'].headerValues = ['Status', 'INV#', 'Wine', 'Type', 'Ordered', 'Started', 'Racked', 'Filtered', 'BD'];
		this.search.sections['main'].sortTypes = ['text', 'number', 'text', 'text', 'date', 'date', 'date', 'date', 'date'];
		this.search.sections['main'].num_cols = 9;
		this.search.dataMaps['main'] = ['status', 'invoice_number', 'wine_and_customer', 'wine_type_and_length', 'order_date', 'start_date', 'rack_date', 'filter_date', 'bottling_date'];
		this.search.refresh();
		this.search.show();
	}

	this.downloadXLS = function() {
		M.api.openFile('ciniki.wineproduction.downloadXLS', {'business_id':M.curBusinessID});
	}

	this.showAppointment = function(cb, aid) {
		if( aid != null ) {
			this.appointment.appointment_id = aid;
		}
		var rsp = M.api.getJSONCb('ciniki.wineproduction.appointment', {'business_id':M.curBusinessID, 
			'appointment_id':this.appointment.appointment_id}, function(rsp) {
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
					p.sections.wines.fields[i] = {'6':{'id':'order_' + rsp.appointments[0].appointment.orders[i].order.order_id + '_bottling_status', 'label':'Status', 'type':'flags', 'join':'yes', 'toggle':'yes', 'flags':M.ciniki_wineproduction_main.bottlingStatus}};
					for(var j in M.ciniki_wineproduction_main.bottlingStatus) {
						if( M.ciniki_wineproduction_main.bottlingStatus[j].name == rsp.appointments[0].appointment.orders[i].order.bottling_status ) {
							p.sections.wines.fields[i]['6']['value'] = Math.pow(2,j-1);
						}
					}
				}

				p.customer_id = rsp.appointments[0].appointment.customer_id;
				if( p.customer_id > 0 ) {
					p.sections.customer.addTxt = 'Edit Customer';
					p.sections.customer.changeTxt = 'Change Customer';
//					p.data.customer = M.ciniki_wineproduction_main.setupCustomer(rsp.appointments[0].appointment.customer);
					p.data.customer = rsp.appointments[0].appointment.customer;
				} else {
					p.sections.customer.addTxt = 'Add Customer';
					p.sections.customer.changeTxt = '';
				}
				// this.appointment.refresh();
				p.refreshSection('info');
				p.refreshSection('wines');
				p.show(cb);
			});
	}

	this.updateAppointmentCustomer = function(cid) {
		// If the customer has changed, then update the details of the invoice
		if( cid != null && this.appointment.customer_id != cid ) {
			this.appointment.customer_id = cid;
			var wids = '';
			var cma = '';
			for(var i in this.appointment.data.orders) {
				wids += cma + this.appointment.data.orders[i].order.order_id;
				cma = ',';
			}
			var rsp = M.api.getJSONCb('ciniki.wineproduction.updateAppointment', 
				{'business_id':M.curBusinessID, 'wineproduction_ids':wids, 
				'customer_id':this.appointment.customer_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					} 
					M.ciniki_wineproduction_main.updateAppointmentCustomerFinish();
				});
		} else {
			this.updateAppointmentCustomerFinish();
		}
	};

	this.updateAppointmentCustomerFinish = function() {
		// Update the customer details
		M.api.getJSONCb('ciniki.customers.get', {'business_id':M.curBusinessID, 
			'customer_id':this.appointment.customer_id, 'emails':'list'}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_wineproduction_main.appointment;
//				p.data.customer = M.ciniki_wineproduction_main.setupCustomer(rsp.customer);
				p.refreshSection('customer');
				p.show();
			});
	};


	this.saveAppointment = function(bottled) {
		var bd = M.gE(this.appointment.panelUID + '_bottling_date');
		if( bd.value.match(/ctb/i) ) {
			bd.value = '';
		}
		var c = this.appointment.serializeForm('no');
		if( c != '' || bottled == 'yes' ) {
			var wids = '';
			var cma = '';
			for(var i in this.appointment.data.orders) {
				wids += cma + this.appointment.data.orders[i].order.order_id;
				cma = ',';
			}
			var rsp = M.api.postJSONCb('ciniki.wineproduction.updateAppointment', 
				{'business_id':M.curBusinessID, 'wineproduction_ids':wids, 'bottled':bottled}, c, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					} 
					M.ciniki_wineproduction_main.appointment.close();
				});
		} else {
			M.ciniki_wineproduction_main.appointment.close();
		}
	}
}
