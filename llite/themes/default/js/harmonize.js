var loader = '<div class="text-center"><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>';
var u = $("#hf_base_url").val();
var l = $("#hf_user_language").val();
var pageGET = $("#hf_get").val();
const baseLink = u+l;		


var page = {
	url : $("#hf_base_url").val(),
	lang : $("#hf_user_language").val(),
	get : $("#hf_get").val(),
	
	ScrollDown : function() {
		var p = $( "#stopka" );
		var position = p.position();
		var WH = $(window).height();  
		var SH = $('body').prop("scrollHeight");
		$('html, body').animate({ scrollTop: SH-WH+100 }, "slow");
		},
	ScrollUp : function() {
		$('html, body').animate({ scrollTop: 0 }, 1000);
		},
		
	myInfoCloud: function(c,t) {
		var htop = $('header').height()+10;
		
		$('#myInfoCloud').html(c); 
		$('#myInfoCloud').animate({ top: htop+'px'}, 'fast');
		
		setTimeout( function () { 
			$('#myInfoCloud').animate({ top: '-120px'}, 'slow');
			$('#myInfoCloud').html(''); 
			
			}, t);
		},

	ajax(box,slink) {
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			
			//var s = $("#searchInput").val();
			var f = $("#searchForm_type").val();
			
			
			$.ajax({url: u+l+"/ajax/"+slink, success: function(result){
				if (result.length>1)
					$("#"+box).html(result);
				}});	
			},
	
	post : function(box,slink,pdata) {
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();

			$.post(u + l + "/ajax/" + slink, { pdata: pdata })
				.done(function(result) {
					//console.log("Result:", result);
					$("#" + CSS.escape(box)).html(result);
				})
				.fail(function(xhr, status, error) {
					console.error("Błąd AJAX:", error);
				});
			},
	
	postLT : function(box,slink,pdata) {
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			$("#"+box).css('opacity', '0.4');
			$.post( 
				u+l+"/ajax/"+slink, { 'pdata':pdata }, function(result){ 
					$("#"+box).html(result); 
					}
				);
			},
			
	postInModal : function(t, slink, pdata, isstatic = true) {
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			var k = $("#hf_request_uri").val();
			$("#inModalTitle").html(t);
			$.post( 
				u+l+"/ajax/"+slink, { 'pdata':pdata }, function(result){ 
					$("#inModalBox").html(result); 
					}
				);
			if (isstatic)	
				$("#myModal").modal({backdrop: "static"}); 
				else 
				$("#myModal").modal(); 	
			},		
	
	clipboard(field)  {
		  // Get the text field
		  var copyText = document.getElementById(field);

		  // Select the text field
		  copyText.select();
		  copyText.setSelectionRange(0, 99999); // For mobile devices

		  // Copy the text inside the text field
		  navigator.clipboard.writeText(copyText.value);
		  
		  // Alert the copied text
		  alert("Copied the text: " + copyText.value);
		},
	
	clearForm(formname) {
		$("#"+formname).trigger("reset");
		},

	// methods of operating on lists
	phpFolder : 'funkcje/',
	
	phpResults : '',
	phpFilters : '',
	phpAction : '',
	
	resultsField : 'resultsField',
	filterField : 'filterField',
	
	results : function (p, s, d, a) {
		$("#"+page.resultsField).css('opacity','0.4');
		$.post(page.url+page.lang+'/ajax/'+page.phpResults, {currentPage: p, sorting: s, sortingdesc: d, action: a}, function(data){
			$("#"+page.resultsField).html(data);
			});
		}, 
	
	filters : function () {
		$("#"+page.filterField).css('opacity','0.4');
		$.post(page.url+page.lang+'/ajax/'+page.phpFilters, {toSend: 'test'}, function(data){
			$('#'+page.filterField).html(data);
			});
		},			
	
	onClickActions : function (id) {
		$('#okno_formularza').css('opacity','0.1');
		$.post(this.php_folder+this.phpAction, {id_post: id}, function(data){
			if(data.length >1) {
				$('#okno_formularza_zawartosc').html(data);
				$('#myModal').modal({show: true, keyboard: true});
				}
			});
		}
		
	
	}

var coreMenu = {
		
		Show : function() {
			$('.core-menu-items').addClass('active');
			$('.bg-off').addClass('active');
			},
			
		Hide : function() {
			$('.core-menu-items').removeClass('active');
			$('.bg-off').removeClass('active');
			}
		}
		
		
var facets = {
		leftW : $('.sidebar').width(),
		rightW : $('.mainbody').width(),
		
		SlideOut : function() {
			var is_mobile = false;
			
			
			$('#content').addClass('mainbodyFullScreen');
			$('.sidebar').addClass('sidebarHidden');
			$('.sidebar-buttons').addClass('shown');
			
			if( $('#IsMobile').css('display')=='none') {
				is_mobile = true;       
				} else {
				$('.main').animate({
					width: '133%',
					left: '-33%'
					}); 
				};
			},
			
		SlideIn : function() {
			var is_mobile = false;
			
			$('#content').removeClass('mainbodyFullScreen');
			$('.sidebar').removeClass('sidebarHidden');
			$('.sidebar-buttons').removeClass('shown');
			
			if( $('#IsMobile').css('display')=='none') {
				is_mobile = true;       
				} else {
				$('.main').animate({
					width: '100%',
					left: '0%'
					}); 
				};
			
			//$('#slideinbtn').addClass('hidden');		
			},
		
		graphActive : function(i,c) {
			var stroke = $('#pie_'+i).attr("stroke");
			var c = stroke.substring(0, 7);
			
			$('#pie_'+i).attr('stroke',c+'ff');
			$('#pie_'+i).attr('stroke-width','50%');
			$('#trow_'+i).addClass('active');
			},
			
		graphDisActive : function(i,c) {
			var stroke = $('#pie_'+i).attr("stroke");
			var c = stroke.substring(0, 7);
			
			$('#pie_'+i).attr('stroke',c+'88');
			$('#pie_'+i).attr('stroke-width','40%');
			$('#trow_'+i).removeClass('active');
			},
		
		timeFacetLink : function(x,a,f,i,c = 'search') { 
			$("#recalculateLink").css("opacity","0.4");
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			var g = $("#hf_get").val();
			
			
			$.ajax({url: u+l+"/ajax/search/setTimeRangeLink/"+f+"/"+i+"/"+c+"?"+x+"="+a+"&"+g, success: function(result){
				  $("#recalculateLink").html(result);
				}});	
			},	

		timeStatLink : function(uid) { 
			$('#range_area_'+uid).css("opacity","0.4");
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			var g = $("#hf_get").val();
			
			var bc = $("#base_conditions_"+uid).val();
			var from = $("#year_str_from_"+uid).val();
			var to = $("#year_str_to_"+uid).val();
			var rangeField = $("#range_field_"+uid).val();
			
			
			$.ajax({url: u+l+"/ajax/search/setTimeRangeLink.stat/"+rangeField+"/"+from+"/"+to+"/"+bc+"/", success: function(result){
				  $('#range_area_'+uid).html(result);
				}});	
			},	

		
		cascade : function(p,k,f,n,c) { 
			// $("#facetLink"+k).css("opacity","0.4");
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			var g = $("#hf_get").val();
			
			$.ajax({url: u+l+"/ajax/search/cascadeFacet/"+k+"/"+f+"/"+p+"/"+c+"?n="+n+"&"+g, success: function(result){
				  $("#facetLink"+k).html(result);
				}});	
			},	
		
		place (k) {
			var pos = $('#facetBase'+k).position();
			var wid = $('#facetBase'+k).width();
			left = pos.left+wid+15;
			
			$('#facetBase'+k).css('background-color', '#eee');
			$('#facetLink'+k).css('top', pos.top+'px');
			$('#facetLink'+k).css('left', left+'px');
			},
		
		out (k) {
			$('#facetBase'+k).css('background-color', 'transparent');
			},
		
		cascade2 : function(k,f, lst) { 
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			var g = $("#hf_get").val();
			
			var pos = $('#facetBase'+k).position();
			var wid = $('#facetBase'+k).width();
			left = pos.left+wid+15;
			
			$('#facetLink'+k).css('top', pos.top+'px');
			$('#facetLink'+k).css('left', left+'px');
			$('#facetLink'+k).width(wid);
			$('#facetLink'+k).css('opacity', 1);
			
			$.post( 
				u+l+"/ajax/search/cascadeFacet/"+k+"/"+f+"?"+g, { 'list':lst }, function(result){ 
					if (result.length>1) {
						$('#caret_'+k).css('color','#888');
						$("#facetLink"+k).html(result); 
						} else {
						$('#caret_'+k).css('color','transparent');
						$("#facetLink"+k).html(''); 
						}
					}
				);
			},	
		
		cascadeSearch : function(f, idx, formatter, translated) { 
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			var g = $("#hf_get").val();
			
			var txt = $("#subfacetInput"+idx).val();
			$("#subfacetCascadeResults_"+idx).css('opacity', '0.5');
			
			$.post( 
				u+l+"/ajax/search/cascadeFacetSearch/"+f+"/"+idx+"?"+g, { 'lookfor':txt, 'formatter': formatter , 'translated': translated }, function(result){ 
					$("#subfacetCascadeResults_"+idx).html(result); 
					}
				);
			},	
		
		Search : function() { 
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			var k = $("#hf_request_uri").val();
			var n = $("#hf_facet").val();
			var q = $("#ajaxSearchInput").val();
			var s = $("input[name='facetsort']:checked").val();
			
			if (k.includes("?"))
				var operator = "&";
				else 
				var operator = "?";
			
			$.ajax({url: u+l+"/ajax/inModalFacet/search/"+n+k+operator+"q="+q+"&sort="+s, success: function(result){
				  $("#ajaxSearchBox").html(result);
				}});	
			},	
		
		AddRemove : function(x,a,i) { 
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			var k = $("#hf_request_uri").val();
			var n = $("#hf_facet").val();
			var q = $("#ajaxSearchInput").val();
			var s = $("input[name='facetsort']:checked").val();
			
			if (k.includes("?"))
				var operator = "&";
				else 
				var operator = "?";
			
			$.ajax({url: u+l+"/ajax/inModalFacetChosen/search/"+n+k+operator+"q="+q+"&sort="+s+"&"+x+"="+a+"&lp="+i, success: function(result){
				  $("#ajaxSearchChosen").html(result);
				}});	
				
			},	
		
		Load : function(n,w) {
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			var g = $("#hf_get").val();
			$.ajax({url: u+l+"/ajax/facet/"+n+"/"+w+"/?"+g, success: function(result){
				  $("#loadbox_"+n).html(result);
				}});
			},
		
	
		InModal : function(t,n,c = '') {
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			var k = $("#hf_request_uri").val();
			$("#inModalTitle").html(t);
			
			$.ajax({url: u+l+"/ajax/inModalFacet"+c+"/build/"+n+k, success: function(result){
				  $("#inModalBox").html(result);
				}});
			$("#myModal").modal("show"); 
			},
		
		cores : {
			
			InModal : function(t,n,f) {
				var u = $("#hf_base_url").val();
				var l = $("#hf_user_language").val();
				var k = $("#hf_request_uri").val();
				$("#inModalTitle").html(t);
				if (f == 'undefined')
					f = 0;
				$.ajax({url: u+l+"/ajax/inModalFacetCore/build/"+n+':'+f+k, success: function(result){
					  $("#inModalBox").html(result);
					}});
				$("#myModal").modal("show"); 
				},
				
			Search : function(f) { 
				var u = $("#hf_base_url").val();
				var l = $("#hf_user_language").val();
				var k = $("#hf_request_uri").val();
				var n = $("#hf_facet").val();
				var q = $("#ajaxSearchInput").val();
				var s = $("input[name='facetsort']:checked").val();
				
				if (k.includes("?"))
					var operator = "&";
					else 
					var operator = "?";
				
				$.ajax({url: u+l+"/ajax/inModalFacetCore/search/"+n+k+operator+"q="+q+"&sort="+s+"&facetsCode="+f, success: function(result){
					  $("#ajaxSearchBox").html(result);
					}});	
				},	
			
			AddRemove : function(x,a,i) { 
				var u = $("#hf_base_url").val();
				var l = $("#hf_user_language").val();
				var k = $("#hf_request_uri").val();
				var n = $("#hf_facet").val();
				var q = $("#ajaxSearchInput").val();
				var s = $("input[name='facetsort']:checked").val();
				
				if (k.includes("?"))
					var operator = "&";
					else 
					var operator = "?";
				
				$.ajax({url: u+l+"/ajax/inModalFacetCoreChosen/search/"+n+k+operator+"q="+q+"&sort="+s+"&"+x+"="+a+"&lp="+i, success: function(result){
					  $("#ajaxSearchChosen").html(result);
					}});	
					
				},		
				
			}
		
		}

var colbox = {
		
		Check : function(a) {
			var minSize = $('#'+a+'_minSize').val();
			
			var boxsize = $('#'+a+'>.collapseBox-body').height();
			if (boxsize > minSize) {
				this.Hide(a);
				$('#'+a+'_maxSize').val(boxsize);
				} else {
				$('#'+a+'>.collapseBox-bottom').hide();
				}
			},
		
		Show : function(a) {
			var maxSize = $('#'+a+'_maxSize').val();
			$('#'+a+'>.collapseBox-body').animate( { 'height':maxSize+'px' } , "slow");
			$('#'+a+' .hide-btn').show();
			$('#'+a+' .show-btn').hide();
			
			},
				
		Hide : function(a) {
			var minSize = $('#'+a+'_minSize').val();
			$('#'+a+'>.collapseBox-body').css( 'overflow','hidden' );
			$('#'+a+'>.collapseBox-body').animate( { 'height':minSize+'px' } , "slow");
			$('#'+a+' .hide-btn').hide();
			$('#'+a+' .show-btn').show();
			}
				
		}


var user = {
	
		eatsCookie : function() {
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();

			$.post( 
				u+l+"/ajax/user/acceptCookie/", { 'accept':'ok' }, function(result){ $("#cookiesBox").html(result); }
				);
			},
		
		register : function() {
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();

			var a = $("#account_firstname").val();
			var b = $("#account_lastname").val();
			var c = $("#account_email").val();
			var d = $("#account_username").val();
			var e = $("#account_password").val();
			var f = $("#account_repassword").val();
			

			$.post( 
				u+l+"/ajax/user/register/", 
				{ firstname: a, lastname: b, email: c, username: d, password: e, repassword: f},
				function(result){ $("#registerBox").html(result); }
				);
			}, 
			
		LogIn : function() {
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			var log = $("#LogInLogin").val();
			var pas = $("#LogInPass").val(); 
			var code = $("#vcode").val(); 
			
			$.post( 
				u+l+"/ajax/user/login/", 
				{ test: 'test-2y', login: log, pass: pas, code: code },
				function(result){ $("#logInBox").html(result); }
				);
			}
		
		}

var service = {
		waiter : '<div class="text-center"><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>',
		
		checkFolder : function(t,n,a) {
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			$("#ajaxBox_"+t).html(this.waiter);
			$.post( u+l+"/ajax/service/checkfolder/", { 'name':t, 'folder':n, 'action':a }, function(result){ 
				$("#ajaxBox_"+t).html(result); 
				});
			},

		InModal : function(t,n) {
			
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			$("#myModal").modal("show"); 
			$("#inModalTitle").html(t);
			$("#inModalBox").html(atob(n));
			}
			
		}


var bookcart = {
		saveList : function() {
			var u = $("#hf_base_url").val();
			var r = $("#hf_request_uri").val();
			var get = $("#hf_results_get").val();
			
			var id = $("#field_id").val();
			var list_name = $("#field_list_name").val();
			var list_ico = $("#field_list_ico").val();
			var list_description  = $("#field_tracking").html();
			var saveBtn  = $("#buttonArea").html();
			
			hp = u+r;
			if (get !== "undefined") {
					hp = u+r+'?'+get;
				}
			var l = $("#hf_user_language").val();

			$("#buttonArea").html(results.waiter);
			$.post( 
				u+l+"/ajax/user/lists/save/", { 'id':id, 'list_name':list_name, 'list_ico':list_ico, 'list_description':list_description, 'reLoadLink':hp, 'saveBtn':saveBtn }, function(result){ 
					$("#saveArea").html(result); 
					}
				);
			},
			
		saveStickyNote : function() {
			var u = $("#hf_base_url").val();
			var r = $("#hf_request_uri").val();
			var get = $("#hf_results_get").val();
			
			var id = $("#field_id").val();
			var rec_id = $("#field_rec_id").val();
			var stickynote  = $("#field_tracking").html();
			var saveBtn  = $("#buttonArea").html();
			
			hp = u+r;
			if (get !== "undefined") {
					hp = u+r+'?'+get;
				}
			var l = $("#hf_user_language").val();

			$("#buttonArea").html(results.waiter);
			$.post( 
				u+l+"/ajax/user/lists/sticky.note.save/", { 'id':id, 'rec_id':rec_id, 'stickynote':stickynote, 'reLoadLink':hp, 'saveBtn':saveBtn }, function(result){ 
					$("#saveArea").html(result); 
					}
				);
			}
		}

var results = {
		waiter : '<div class="text-center"><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>',
		u : $("#hf_base_url").val(),
		l : $("#hf_user_language").val(),
		get : $("#hf_results_get").val(),
		baseLink() {
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			
			return u+l;
			},

		citeThis : function(t,id) {
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			hp = u+l;
			$("#myModal").modal("show");
			$("#inModalTitle").html(t);
			$.ajax({url: u+l+"/ajax/results/cite.this/"+id, success: function(result){
				  $("#inModalBox").html(result);
				}});
			}, 
		
		fixedLink : function(t,id) {
			$("#myModal").modal("show"); 
			$("#inModalTitle").html(t);
			$.ajax({url: page.baseLink+"/ajax/results/fixedLink/"+id, success: function(result){
				  $("#inModalBox").html(result);
				}});
			}, 
		
		btnPrevNext : function(cp) {
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			var get = $("#hf_results_get").val();
			hp = u+l;
			$('#recordAjaxAddsOn').html(this.waiter);
			
			$.ajax({url: hp+"/ajax/results/prevNext/"+cp+"/"+get, success: function(result){
				  $("#recordAjaxAddsOn").html(result);
				}});
				
			},
		
		personBox: function(b,p) {
			var content = $('#'+b).html();
			$('#point_'+p).html('<i class="glyphicon glyphicon-info-sign"></i><div class="cloud-info">'+content+'</div>');
			},
		
		personBox2Class: function(b) {
			var content = $('#'+b).html();
			$('.'+b).html('<i class="glyphicon glyphicon-info-sign"></i><div class="cloud-info">'+content+'</div>');
			},
		
		Rotate : function(a) {
			if ($(a).hasClass('collapsed')) {
				$(a).removeClass('collapsed')
				} else {
				$(a).addClass('collapsed');
				}
			},
		
		
		FocusOn(id) {
			//$('.result').css('opacity','0.5');
			//$('.result').css('filter','blur(2px)');
			//$('#'+id).css('opacity','1');
			//$('#'+id).css('filter','none');
			},	
		
		FocusOff() {
			//$('.result').css('opacity','1');
			//$('.result').css('filter','none');
			},

		myList : function(a, m) {
			if (m == undefined)
				m = 'myList';
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			$.ajax({url: u+l+"/ajax/user/lists/positions/"+a+"/"+m+"/", success: function(result){
				  $("#myListsArea").html(result);
				}});
			},
			
		selectAll : function(a) {
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			$.ajax({url: u+l+"/ajax/results/myListSelectAll/"+a+"/", success: function(result){
				  $("#SelectAllResponse").html(result);
				}});
			},
		
		
		
		Print (t,f) {
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			var g = $("#hf_get").val();
			
			$("#myModal").modal("show"); 
			
			$("#inModalTitle").html(t);
			$.ajax({url: u+l+"/ajax/print/full/"+f+"/?"+g, success: function(result){
				  $("#inModalBox").html(result);
				}});
			},
		
		export2 (t,m,f) {
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			var g = $("#hf_get").val();
			
			$("#myModal").modal("show"); 
			
			$("#inModalTitle").html(t);
			$.ajax({url: u+l+"/ajax/export/execMethod/"+m+"/"+f+"/?"+g, success: function(result){
				  $("#inModalBox").html(result);
				}});
			},

		Export (t,m,f) {
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			var g = $("#hf_get").val();
			
			$("#myModal").modal("show"); 
			
			$("#inModalTitle").html(t);
			$.ajax({url: u+l+"/ajax/export/multi/"+m+"/"+f+"/?"+g, success: function(result){
				  $("#inModalBox").html(result);
				}});
			},

		ExportStart(m,f,fn) {
			$("#exportBtn").html(results.waiter);
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			var g = $("#hf_get").val();
			
			$.post( u+l+"/ajax/export/multi/"+m+"/"+f+"/?"+g, {'options':fn},  function(result){
				  $("#exportControlField").html(result);
				});
				
			},
			
		ExportPart(p,f,fn) {
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			var g = $("#hf_get").val();
			var m = $("#exportMethod").val();
			var link = u+l+"/ajax/export/multi/"+f+"/"+p+"/"+fn+"/"+m+"/?"+g;
			alert (link);
			$.ajax({url: link, success: function(result){
				  $("#export_box").html(result);
				}});
			},
		
		
		saveList() {
			$('#sessionBox').html(this.waiter);
			
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			var g = $("#hf_get").val();
			
			$.ajax({url: u+l+"/ajax/results/save/", success: function(result){
				  $("#sessionBox").html(result);
				}});
			
			},

		preView : function(t,n) {
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			$("#myModal").modal("show"); 
			
			$("#inModalTitle").html(t);
			$.ajax({url: u+l+"/ajax/results/preView/"+n+".html", success: function(result){
				  $("#inModalBox").html(result);
				}});
			},

		preViewCopy : function(title, boxid) {
			$("#myModal").modal("show"); 
			$("#inModalTitle").html(title);
			var textContent = $("#previewbox_"+boxid).html();
			console.log (textContent.length);
			$("#inModalBox").html(textContent);
			},

		miniPreView : function(id,lp) {
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			let field = id.replace('.','_');
			$.ajax({url: u+l+"/miniPreView/"+id+".html?lp="+lp, success: function(result){
				  if (result.length>1) {
					  $("#extra_rec_"+field).html(result);
				  }
				}});
			},

		InModal : function(t,n) {
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			$("#myModal").modal("show"); 
			$("#inModalTitle").html(t);
			$("#inModalBox").html(atob(n));
			},

		relatedPersons : function(lst) { 
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			
			var boxtop = $('#relatedPersons').position().top;
			
			$.post( 
				u+l+"/ajax/search/relatedPersons/", { 'list':lst }, function(result){ 
					if (result.length>1) {
						$("#relatedPersons").html(result); 
						} 
					}
				);
			},	
		
		collapseLongValues : function() {
			const collection = document.getElementsByClassName("detailsview-item");
			for (let i = 0; i < collection.length; i++) {
				var line = collection[i];
				}
			},
		
				
		mapsMenu : {
			
				
			calcDistance : function (p1,p2) {
				var dx = p2.x-p1.x;
				var dy = p2.y-p1.y;
				return Math.sqrt(dx*dx + dy*dy);
				},

			calcAngle :	function (p1, p2) {
				return Math.atan2(p2.y - p1.y, p2.x - p1.x) * 180 / Math.PI;
				}, 
			
			drawRelationLine(uid, from, to, color = '#ddd') {
				$('#ropesBlock').append('<div id="rope_'+uid+'" class="rope"></div>');
				let globalCorect = {
					x: $('#ropesBlock').offset().top,
					y: $('#ropesBlock').offset().left
					}
				
				let selectedPin = $('#'+from);
				let pin = $('#'+to);
				let selectedPinPos = {
					x: selectedPin.offset().top - globalCorect.x + (selectedPin.outerHeight()/2),
					y: selectedPin.offset().left - globalCorect.y + selectedPin.outerWidth()
					}

				if (typeof pin.offset() !== 'undefined') {
					let pinPos = {
						x: pin.offset().top - globalCorect.x +(selectedPin.outerHeight()/2),
						y: pin.offset().left - globalCorect.y
						}

					let distance = this.calcDistance(selectedPinPos, pinPos);
					let angle = 90-this.calcAngle(selectedPinPos, pinPos);

					$('#rope_'+uid).css({
						transform: 'rotate('+angle+'deg)',
						width: distance+'px',
						background : color,
						top: selectedPinPos.x,
						left: selectedPinPos.y
						});
					}
				},	
				
			drawLine(uid, from, to, color = '#ddd') {
				$('#mapRopesBlock').append('<div id="rope_'+uid+'" class="rope"></div>');
				let globalCorect = {
					x: $('#mapRelationsBlock').offset().top,
					y: $('#mapRelationsBlock').offset().left
					}
				
				let selectedPin = $('#'+from);
				let pin = $('#'+to);
				let selectedPinPos = {
					x: selectedPin.offset().top - globalCorect.x + (selectedPin.outerHeight()/2),
					y: selectedPin.offset().left - globalCorect.y + selectedPin.outerWidth()
					}

				if (typeof pin.offset() !== 'undefined') {
					let pinPos = {
						x: pin.offset().top - globalCorect.x +(selectedPin.outerHeight()/2),
						y: pin.offset().left - globalCorect.y
						}

					let distance = this.calcDistance(selectedPinPos, pinPos);
					let angle = 90-this.calcAngle(selectedPinPos, pinPos);

					$('#rope_'+uid).css({
						transform: 'rotate('+angle+'deg)',
						width: distance+'px',
						background : color,
						top: selectedPinPos.x,
						left: selectedPinPos.y
						});
					}
				}	
			},
		
	
		
		maps : {
			
			baseLink() {
				var u = $("#hf_base_url").val();
				var l = $("#hf_user_language").val();
				
				return u+l;
				},

			
			addBiblioRecRelatations(biblioId, changeField) {
				$('#mapRelationsAjaxArea').css('opacity', '0.4');
			
				$.ajax({url: this.baseLink()+"/ajax/wiki/biblio.wikiRelations/"+biblioId+'/'+changeField, success: function(result){
					  $("#mapRelationsAjaxArea").html(result);
					}});
				},
			
			addPersonRelatations(wikiQ) {
				$('#mapRelationsAjaxArea').css('opacity', '0.4');
				
				var a = $("#map_checkbox_1").is(':checked');
				var b = $("#map_checkbox_2").is(':checked');
				var c = $("#map_checkbox_3").is(':checked');
				
				$.ajax({url: this.baseLink()+"/ajax/wiki/person.WikiRelations/"+wikiQ+'/'+a+'/'+b+'/'+c, success: function(result){
					  $("#mapRelationsAjaxArea").html(result);
					}});
				},
			
			addInstitutionRelatations(wikiQ) {
				$('#mapRelationsAjaxArea').css('opacity', '0.4');
				
				var a = $("#map_checkbox_1").is(':checked');
				var b = $("#map_checkbox_2").is(':checked');
				var c = $("#map_checkbox_3").is(':checked');
				var d = $("#map_checkbox_4").is(':checked');
				var e = $("#map_checkbox_5").is(':checked');
				
				
				$.ajax({url: this.baseLink()+"/ajax/wiki/institution.WikiRelations/"+wikiQ+'/'+a+'/'+b+'/'+c+'/'+d+'/'+e, success: function(result){
					  $("#mapRelationsAjaxArea").html(result);
					}});
				},
			
			addPlaceRelatations(wikiQ) {
				$('#mapRelationsAjaxArea').css('opacity', '0.4');
				
				var a = $("#map_checkbox_1").is(':checked');
				var b = $("#map_checkbox_2").is(':checked');
				var c = $("#map_checkbox_3").is(':checked');
				var d = $("#map_checkbox_4").is(':checked');
				var e = $("#map_checkbox_5").is(':checked');
				
				
				$.ajax({url: this.baseLink()+"/ajax/wiki/place.WikiRelations/"+wikiQ+'/'+a+'/'+b+'/'+c+'/'+d+'/'+e, success: function(result){
					  $("#mapRelationsAjaxArea").html(result);
					}});
				},
			
			start(facetsCode) {
				var g = $("#hf_get").val();
				$.ajax({url: this.baseLink()+"/ajax/wiki/map.first.run/"+facetsCode+'/?'+g, success: function(result){
					  $("#mapPopupSummary").html(result);
					}});
				},
			
			currentPlace(wikiq) {
				$.ajax({url: this.baseLink()+"/ajax/wiki/map.currentPlace/"+wikiq+'/', success: function(result){
					  $("#mapPopupCurrentPlace").html(result);
					}})
				},
				
			currentPlacePost(wikiq, addOns) {
				$.post( this.baseLink()+"/ajax/wiki/map.currentPlace/"+wikiq+'/', 
					{add: addOns}, 
					function(result){
					  $("#mapPopupCurrentPlace").html(result);
					  $("#mapPopupCurrentPlace").append(addOns);
					});
				},
			
			currentPlaceWikiPost(wikiq, addOns) {
				$.post( this.baseLink()+"/ajax/wiki/map.currentPlaceWiki/"+wikiq+'/', 
					{add: addOns}, 
					function(result){
					  $("#mapPopupCurrentPlace").html(result);
					});
				},
			
			moved(facetsCode) {
				const minPause = 800;
				var g = $("#hf_get").val();
				var totalResults = $("#totalResults").val();
				var visible = $("#visibleResults").val();
				$.post( this.baseLink()+"/ajax/wiki/map.moved/"+facetsCode+"?"+g, {
						'bN':$("#mapBoundN").val(),
						'bS':$("#mapBoundS").val(),
						'bE':$("#mapBoundE").val(),
						'bW':$("#mapBoundW").val(),
						'zoomOld':$("#mapStartZoom").val(),
						'zoom':$("#mapZoom").val(),
						'total': totalResults,
						'visible': visible
						},  
						function(result){
							if (result.length>1)
								$("#mapPopupCurrentView").html(result);
						});
				}	
			}
		}

var search = {
	
		autocomplete(e) {
			const minimalnaPrzerwa = 800; 
			
			var acField = "#searchInput-ac";
			$(acField).html(loader);
			
			
			var core = $("#search_core").val();
			var i = -1;
			
			const inputField = document.getElementById("searchForm_lookfor");
			let lastKeyPressTime = 0;
			let timerId;
			
			function arrowPressed(e){    
				if (document.activeElement !== inputField) return; // sprawdzenie focusu
				// e.keyCode == 40 -down
				// e.keyCode == 38 -up
				// console.log(e.keyCode);
				const maxItems = $("#acItemsList").children().length;
				if (((e.keyCode == 40)||(e.keyCode==38))&(maxItems>0)) {
					if (e.keyCode == 40) i++;
					if (e.keyCode == 38) i--;
					if (i>= maxItems) i=maxItems-1;
					if (i<= 0) i=0;
					$(".ac-item").removeClass("active");
					const activeElement = $("#acItemsList a:eq("+i+")");
					activeElement.addClass("active");
					$("#searchForm_lookfor").val(activeElement.html());
					}
				}
			window.addEventListener('keydown', arrowPressed,false);
				
			inputField.addEventListener("input", function() {
				const teraz = Date.now();
				
				if (timerId) {
					clearTimeout(timerId);
					}

				timerId = setTimeout(function() {
					// Wykonaj polecenia po zadanym czasie bez wciśnięcia innych klawiszy
					// $(acField).html("Searching for: "+teraz+": "+inputField.value);
					var f = $("#searchForm_type").val();
					$(acField).addClass("inprogress");
					$.post(page.baseLink+"/autocomplete/ac.html.list/", {sstring: inputField.value, sfield: f, score: core}, function(result){
						if (result.length>1)
							$(acField).html(result);
						i = -1;
						});
						
					
					}, minimalnaPrzerwa);

				// Zaktualizuj czas ostatniego wciśnięcia klawisza
				lastKeyPressTime = teraz;
				});
			},
		
		oneSearcher() {
			const minimalnaPrzerwa = 800; 
			
			var acField = "#oneSearcherArea";
			//$(acField).html(loader);
			
			
			var core = $("#search_core").val();
			var i = -1;
			
			const inputField = document.getElementById("searchForm_lookfor");
			let lastKeyPressTime = 0;
			let timerId;
				
			inputField.addEventListener("input", function() {
				const teraz = Date.now();
				
				if (timerId) {
					clearTimeout(timerId);
					}

				timerId = setTimeout(function() {
					// Wykonaj polecenia po zadanym czasie bez wciśnięcia innych klawiszy
					// $(acField).html("Searching for: "+teraz+": "+inputField.value);
					var f = $("#searchForm_type").val();
					$(acField).addClass("inprogress");
					$.post(page.baseLink+"/ajax/results/oneSearcherSummary/", {sstring: inputField.value}, function(result){
						if (result.length>1)
							$(acField).html(result);
						i = -1;
						});
						
					
					}, minimalnaPrzerwa);

				// Zaktualizuj czas ostatniego wciśnięcia klawisza
				lastKeyPressTime = teraz;
				});
			},
			
		start() {
			var acField = "#searchInput-ac";
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			
			var f = $("#searchForm_type").val();
			var core = $("#search_core").val();
			
			const inputField = document.getElementById("searchForm_lookfor");
			
			$.post(u+l+"/autocomplete/ac.html.list/", {sstring: inputField.value, sfield: f, score: core}, function(result){
						if (result.length>1)
							$(acField).html(result);
						});
			
			},
		
		clickItem : function(string) {
			document.getElementById('searchForm_lookfor').value = string;
			}	
			
	}


var advancedSearch = {
	
	refresh : function (a) {
			$("#formBox").css('opacity','0.4'); 
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			$.post(u+l+"/ajax/search/advancedForm/", {action: a}, function(result){
				if (result.length>1)
					$("#formBox").html(result);
				});	
			}, 
	
	newValue : function (a) {
			$("#querySummary").css('opacity','0.4'); 
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			$.post(u+l+"/ajax/search/querySummary/", {action: a}, function(result){
				if (result.length>1)
					$("#querySummary").html(result);
				});	
			}, 
	
	facets : function (a) {
			$("#facetsBox").css('opacity','0.4'); 
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			$.post(u+l+"/ajax/search/advancedFacets/", {action: a}, function(result){
				if (result.length>1)
					$("#facetsBox").html(result);
				});	
			}, 
	
	sortby : function (a) {
			$("#sortbyBox").css('opacity','0.4'); 
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			$.post(u+l+"/ajax/search/advancedSortBy/", {action: a}, function(result){
				if (result.length>1)
					$("#sortbyBox").html(result);
				});	
			}, 
		
	AddRemove : function(x,a,f,i) { 
			$("#querySummary").css("opacity","0.4");
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			
			$.ajax({url: u+l+"/ajax/search/querySummary/"+f+"/"+i+"?"+x+"="+a, success: function(result){
				  $("#querySummary").html(result);
				}});	
			},	

	summary : function() { 
			$("#querySummary").css("opacity","0.4");
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			
			$.ajax({url: u+l+"/ajax/search/querySummary/", success: function(result){
				  $("#querySummary").html(result);
				}});	
			},	
	
	fSearch : function(f) { 
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			var k = $("#hf_request_uri").val();
			var q = $("#ajaxSearchInput_"+f).val();
			var s = $("input[name='facetsort"+f+"']:checked").val();
			
			if (k.includes("?"))
				var operator = "&";
				else 
				var operator = "?";
			
			$.ajax({url: u+l+"/ajax/search/inModalFacet/search/"+f+k+operator+"q="+q+"&sort="+s, success: function(result){
				  $("#ajaxSearchBox_"+f).html(result);
				}});	
			}	
			
	
	
	}

var ac = {
		lookfor : function(area, r, p = '') {
			const waitWithAction = 800;
			var actionFieldStr = "ac_search_"+area;
			var inputFieldStr = "ac_input_"+area;
			const inputField = document.getElementById(inputFieldStr);
			
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();

			let timerId;
			
			inputField.addEventListener("input", function() {
				const teraz = Date.now();
				
				if (timerId) {
					clearTimeout(timerId);
					}

				timerId = setTimeout(function() {
					var lookfor = $("#"+inputFieldStr).val();
					$.post(u + l + "/ajax/ac/lookfor/" + r , { 'field': area, 'lookfor': lookfor, 'params': p }, function (result) {
							$("#"+actionFieldStr).html(result); 
							}
						);	
					}, waitWithAction);

				lastKeyPressTime = teraz;
				});
			}
	
	}

var stat = {
		
		comparsionStart : function(area) {
			const waitWithAction = 800;
			var actionFieldStr = "ajax_comparsion_"+area;
			var inputFieldStr = "comp_input_lookfor_"+area;
			const inputField = document.getElementById(inputFieldStr);
			
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();

			var list = $("#listOf_"+area).val();
			var graphRange = $("#input_range_"+area).val();
			var lookfor = $("#"+inputFieldStr).val();
			$.post( u+l+"/ajax/wiki/comparsion/lookfor", { 'solrIndex':area, 'lookfor':lookfor, 'graphRange':graphRange, 'list':list }, function(result){ 
					if (result.length>1) {
						$("#"+actionFieldStr).html(result); 
						} 
					}
				);	
					
			},
		
		comparsion : function(area) {
			//console.log("Starting listener at "+area);
			const waitWithAction = 800;
			var actionFieldStr = "ajax_comparsion_"+area;
			var inputFieldStr = "comp_input_lookfor_"+area;
			var rangeFieldStr = "comp_input_range_"+area;
			const inputField = document.getElementById(inputFieldStr);
			const rangeField = document.getElementById(rangeFieldStr);
			
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();

			var list = $("#comp_listOf_"+area).val();
			var graphMode = $("#comp_graphMode_"+area).val();
			var graphRange = $("#"+rangeFieldStr).val();
			
			let timerId;
			rangeField.addEventListener("change", function() {
				console.log("got range: ", $.active);
				var lookfor = $("#"+inputFieldStr).val();
				var graphRange = $("#"+rangeFieldStr).val();
				$.post( u+l+"/ajax/wiki/comparsion/lookfor", { 'solrIndex':area, 'lookfor':lookfor, 'graphMode':graphMode, 'graphRange':graphRange, 'list':list }, function(result){ 
						if (result.length>1) {
							$("#"+actionFieldStr).html(result); 
							} 
						}
					);
				});
		
			//if (typeof inputField === "string" && inputField.length > 0) 
			inputField.addEventListener("input", function() {
				const teraz = Date.now();
				
				if (timerId) {
					clearTimeout(timerId);
					}

				timerId = setTimeout(function() {
					var lookfor = $("#"+inputFieldStr).val();
					var graphRange = $("#"+rangeFieldStr).val();
					$.post( u+l+"/ajax/wiki/comparsion/lookfor", { 'solrIndex':area, 'lookfor':lookfor, 'graphMode':graphMode, 'graphRange':graphRange, 'list':list }, function(result){ 
							if (result.length>1) {
								$("#"+actionFieldStr).html(result); 
								} 
							}
						);	
					}, waitWithAction);

				lastKeyPressTime = teraz;
				});
			},
		
		lookfor : function(area) {
			const waitWithAction = 800;
			var actionFieldStr = "ajax_stat_"+area;
			var inputFieldStr = "stat_input_lookfor_"+area;
			var rangeNumberStr = "stat_graph_number_"+area;
			var rangeFieldStr = "stat_graph_range_"+area;
			const inputField = document.getElementById(inputFieldStr);
			const rangeNumber = document.getElementById(rangeNumberStr);
			const rangeField = document.getElementById(rangeFieldStr);
			
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();

			var list = $("#stat_listOf_"+area).val();
			var graphMode = $("#stat_graphMode_"+area).val();
			var graphRange = $("#"+rangeFieldStr).val();
			
			let timerId;
			
			function handleRangeChange() {
				var lookfor = $("#" + inputFieldStr).val();
				var graphRange = $("#" + rangeFieldStr).val();
				$.post(u + l + "/ajax/wiki/stats/lookfor", { 'solrIndex': area, 'lookfor': lookfor, 'graphMode': graphMode, 'graphRange': graphRange, 'list': list }, function (result) {
					if (result.length > 1) {
						$("#" + actionFieldStr).html(result);
					}
				});
				}

			rangeField.addEventListener("change", handleRangeChange);
			rangeNumber.addEventListener("change", handleRangeChange);

				
			inputField.addEventListener("input", function() {
				const teraz = Date.now();
				
				if (timerId) {
					clearTimeout(timerId);
					}

				timerId = setTimeout(function() {
					var lookfor = $("#"+inputFieldStr).val();
					var graphRange = $("#"+rangeFieldStr).val();
					$.post( u+l+"/ajax/wiki/stats/lookfor", { 'solrIndex':area, 'lookfor':lookfor, 'graphMode':graphMode, 'graphRange':graphRange, 'list':list }, function(result){ 
							if (result.length>1) {
								$("#"+actionFieldStr).html(result); 
								} 
							}
						);	
					}, waitWithAction);

				lastKeyPressTime = teraz;
				});
			}
		}
		
var importer = {
		
		All : function(start,step) {
			var u = $("#hiddenFieldURL").val();
			$.ajax({url: u+"ajax/import.part.from.vufind/"+start+"/"+step, success: function(result){
				  $("#import_area").html(result);
				}});
			},
		
		acIndeks : function(start,step) {
			var u = $("#hiddenFieldURL").val();
			$.ajax({url: u+"import/autocomplete/step/"+start+"/"+step, success: function(result){
				  $("#import_area").html(result);
				}});
			},
		
		One : function(id) {
			var u = $("#hiddenFieldURL").val();
			$.ajax({url: u+"ajax/import.one.record/"+id+"/"+step, success: function(result){
				  $("#import_area").html(result);
				}});
			}
		}



function resizeTopWhiteSpace() {
	var top = $('header').height();
	let vid = $('header').width();
	$('body').css('margin-top', top+'px');
	$('.cms_box_home').css('background-position-y', top+'px'); 
	$('.userBox-menu').css('top', top+10+'px');
	$('#constInfoCloud').html(vid);
	
	var barHeight = $('.search-header').height()+13;
	$('.facets-header').css('min-height', barHeight+'px');
	$('.facets-header').css('height', barHeight+'px');
	console.log(barHeight);
	}
	
	
	


$(document).ready(function(){
	window.addEventListener("resize", resizeTopWhiteSpace);	
	
	page.url = $("#hf_base_url").val();
	page.lang = $("#hf_user_language").val();
	page.baseLink = page.url+page.lang;
	
	resizeTopWhiteSpace();
	facets.SlideIn();
	
	
    $("[title]").each(function () {
        var $this = $(this);
        var title = $this.attr("title");
        
        if (title) {
            $this.attr("data-toggle", "tooltip") // Bootstrap 3
                 .attr("data-original-title", title) // Wymagane dla Bootstrap 3
                 .removeAttr("title"); // Usunięcie domyślnego tooltipa przeglądarki
			}
		});
    $('[data-toggle="tooltip"]').tooltip();
	
	
	
	$("#searchForm_lookfor").focus(function(){
		$(".searchInput-ac").addClass("active");
		$("#searchInput").addClass("active");
		});
		
	$("#searchForm_lookfor").blur(function(){
		$(".searchInput-ac").removeClass("active");
		$("#searchInput").removeClass("active");
		});
		
	$('#myModal').on('hidden.bs.modal', function () { $("#inModalBox").html('loading ...');	});
	});
	
	
// https://stackoverflow.com/questions/487073/how-to-check-if-element-is-visible-after-scrolling	
	
function Utils() { }

Utils.prototype = {
    constructor: Utils,
    isElementInView: function (element, fullyInView) {
        var pageTop = $(window).scrollTop();
        var pageBottom = pageTop + $(window).height();
        var elementTop = $(element).offset().top;
        var elementBottom = elementTop + $(element).height();

        if (fullyInView === true) {
            return ((pageTop < elementTop) && (pageBottom > elementBottom));
        } else {
            return ((elementTop <= pageBottom) && (elementBottom >= pageTop));
        }
    }
};

var Utils = new Utils();	