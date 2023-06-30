var loader = '<div class="text-center"><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>';
var u = $("#hf_base_url").val();
var l = $("#hf_user_language").val();
const baseLink = u+l;		


var page = {
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

			$.post( 
				u+l+"/ajax/"+slink, { 'pdata':pdata }, function(result){ 
					$("#"+box).html(result); 
					}
				);
			},
	
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
		
		timeFacetLink : function(x,a,f,i) { 
			$("#recalculateLink").css("opacity","0.4");
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			var g = $("#hf_get").val();
			
			$.ajax({url: u+l+"/ajax/search/setTimeRangeLink/"+f+"/"+i+"?"+x+"="+a+"&"+g, success: function(result){
				  $("#recalculateLink").html(result);
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
		
	
		InModal : function(t,n,fc) {
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			var k = $("#hf_request_uri").val();
			$("#inModalTitle").html(t);
			
			$.ajax({url: u+l+"/ajax/inModalFacet/build/"+n+k, success: function(result){
				  $("#inModalBox").html(result);
				}});
			$("#myModal").modal("show"); 
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

var results = {
		waiter : '<div class="text-center"><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>',
		u : $("#hf_base_url").val(),
		l : $("#hf_user_language").val(),
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
			$.ajax({url: hp+"/search/record/inmodal/cite/"+id+".html", success: function(result){
				  $("#inModalBox").html(result);
				}});
			}, 
		
		btnPrevNext : function(cp) {
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			hp = u+l;
			$('#recordAjaxAddsOn').html(this.waiter);
			
			$.ajax({url: hp+"/ajax/results/prevNext/"+cp+"/", success: function(result){
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
			$.ajax({url: u+l+"/ajax/results/"+m+"/"+a+"/", success: function(result){
				  $("#ch_"+a).html(result);
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
		
		Autocomplete(s) {
			$("#searchFormAC").html('<div class="text-center"><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>');
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			
			//var s = $("#searchInput").val();
			var f = $("#searchForm_type").val();
			
			$.ajax({url: u+"/functions/ajax/autocomplete.php?in="+f+"&s="+s, success: function(result){
				  $("#searchFormAC").html(result);
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
			$.ajax({url: u+l+"/search/record/inmodal/"+n+".html", success: function(result){
				  $("#inModalBox").html(result);
				}});
			},

		miniPreView : function(id,lp) {
			var u = $("#hf_base_url").val();
			var l = $("#hf_user_language").val();
			$.ajax({url: u+l+"/miniPreView/"+id+".html?lp="+lp, success: function(result){
				  $("#extra_rec_"+id).html(result);
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
		
		maps : {
			baseLink() {
				var u = $("#hf_base_url").val();
				var l = $("#hf_user_language").val();
				
				return u+l;
				},

			
			addBiblioRecRelatations(biblioId) {
				$('#mapRelationsAjaxArea').css('opacity', '0.4');
				
				var a = $("#map_checkbox_1").is(':checked');
				var b = $("#map_checkbox_2").is(':checked');
				var c = $("#map_checkbox_3").is(':checked');
				var d = $("#map_checkbox_4").is(':checked');
				var e = $("#map_checkbox_5").is(':checked');
				
				$.ajax({url: this.baseLink()+"/ajax/wiki/biblio.wikiRelations/"+biblioId+'/'+a+'/'+b+'/'+c+'/'+d+'/'+e, success: function(result){
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
			
			moved(t,s) {
				var g = $("#hf_get").val();
				
				$.post( this.baseLink()+"/ajax/wiki/map.moved/?"+g, {
							'bN':$("#mapBoundN").val(),
							'bS':$("#mapBoundS").val(),
							'bE':$("#mapBoundE").val(),
							'bW':$("#mapBoundW").val(),
							'zoomOld':$("#mapStartZoom").val(),
							'zoom':$("#mapZoom").val(),
							'total': t,
							'visible': s
							},  function(result){
					  if (result.length>1)
						$("#mapMovedActions").html(result);
					});
				}	
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
	$('body').css('margin-top', top+'px');
	$('.cms_box_home').css('background-position-y', top+'px'); 
	$('.userBox-menu').css('top', top+10+'px');
	}
	
	
	
window.addEventListener("resize", resizeTopWhiteSpace);	

$(document).ready(function(){
	
	
	resizeTopWhiteSpace();
	facets.SlideIn();
	// $('#slideinbtn').toggle("hide");
	$('[data-toggle="tooltip"]').tooltip(); 
	
	$("#searchForm_lookfor").focus(function(){
		var w = $("#searchInput").innerWidth();
		
		$("#searchFormAC").addClass("active");
		$("#searchFormAC").innerWidth(w);
		$("#searchInput").addClass("active");
		});
		
	$("#searchForm_lookfor").blur(function(){
		$("#searchFormAC").removeClass("active");
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