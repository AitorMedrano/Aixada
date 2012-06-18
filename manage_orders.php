<?php include "inc/header.inc.php" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=$language;?>" lang="<?=$language;?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $Text['global_title']; ?> Manage Orders - Overview</title>

 	<link rel="stylesheet" type="text/css"   media="screen" href="css/aixada_main.css" />
  	<link rel="stylesheet" type="text/css"   media="screen" href="js/fgmenu/fg.menu.css"   />
    <link rel="stylesheet" type="text/css"   media="screen" href="css/ui-themes/<?=$default_theme;?>/jqueryui.css"/>
    <!-- link rel="stylesheet" type="text/css" 	 media="screen" href="js/tablesorter/themes/blue/style.css"/-->
    
    <?php if (isset($_SESSION['dev']) && $_SESSION['dev'] == true ) { ?> 
	    <script type="text/javascript" src="js/jquery/jquery.js"></script>
		<script type="text/javascript" src="js/jqueryui/jqueryui.js"></script>
		<script type="text/javascript" src="js/fgmenu/fg.menu.js"></script>
		<script type="text/javascript" src="js/aixadautilities/jquery.aixadaMenu.js"></script>     	 
	   	<script type="text/javascript" src="js/aixadautilities/jquery.aixadaXML2HTML.js" ></script>   	
	   	<script type="text/javascript" src="js/aixadautilities/jquery.aixadaUtilities.js" ></script>
	   	<script type="text/javascript" src="js/tablesorter/jquery.tablesorter.js" ></script>
	   	<script type="text/javascript" src="js/jeditable/jquery.jeditable.mini.js" ></script>
	   	
	   	

	   	  
   	<?php  } else { ?>
	   	<script type="text/javascript" src="js/js_for_manage_orders.min.js"></script>
    <?php }?>
     
	   
	<script type="text/javascript">
	$(function(){

			$('.detailElements').hide();

			var header = [];

			var tblHeaderComplete = false; 

			var global_oder_id = 0; 


			//STEP 1: retrieve all active ufs in order to construct the table header
			$.ajax({
					type: "POST",
					url: 'smallqueries.php?oper=getActiveUFs',
					dataType:"xml",
					success: function(xml){
						var theadStr = ''; 
						$(xml).find('row').each(function(){
							var id = $(this).find('id').text();
							var colClass = 'Col-'+id;
							header.push(id);
							theadStr += '<th class="'+colClass+' hidden col">'+id+'</th>'
						});

						$('#tbl_reviseOrder thead tr').last().append(theadStr);

						tblHeaderComplete = true; 

					},
					error : function(XMLHttpRequest, textStatus, errorThrown){
						$.showMsg({
							msg:XMLHttpRequest.responseText,
							type: 'error'});
						
					},
					complete : function(msg){
						
					}
			}); //end ajax	

			
			//STEP 2: construct table structure: products and col-cells. 
			$('#tbl_reviseOrder tbody').xml2html('init',{
				url : 'ctrlOrders.php',
				loadOnInit : false, 
				rowComplete : function (rowIndex, row){
					var tbodyStr = '';
					var product_id = $(row).children(':first').text();
					
					for (var i=0; i<header.length; i++){
						
						var colClass = 'Col-'+header[i];
						var rowClass = 'Row-'+product_id;
						//var tdid 	 = header[i] + "_" + product_id
						tbodyStr += '<td class="'+colClass+' '+rowClass+' hidden interactiveCell"></td>';

					}
					$(row).last().append(tbodyStr);
					
				},
				complete : function (rowCount){
					
					//STEP 3: populate cells with product quantities
					$.ajax({
					type: "POST",
					url: 'ctrlOrders.php?oper=getProductQuantiesForUfs&order_id='+ global_order_id,
					dataType:"xml",
					success: function(xml){

						$(xml).find('row').each(function(){
							
							var product_id = $(this).find('product_id').text();
							var uf_id = $(this).find('uf_id').text();
							var qu = $(this).find('quantity').text();
							var tblCol = '.Col-'+uf_id;
							var tblRow = '.Row-'+product_id;
							var pid	= product_id + '_' + uf_id; 
							
							$(tblCol+tblRow).append('<p id="'+pid+'">'+qu+'</p>')

							
							$(tblCol).show();
							

						});

						$('#tbl_reviseOrder tbody tr:even').addClass('highlight');


					},
					error : function(XMLHttpRequest, textStatus, errorThrown){
						$.showMsg({
							msg:XMLHttpRequest.responseText,
							type: 'error'});
						
					}
			}); //end ajax	
						
				}
			});

			
			/**
			 *	returns user to order overview
			 */
			$("#btn_overview").button({
				 icons: {
		        		primary: "ui-icon-circle-arrow-w"
		        	}
				 })
        		.click(function(e){
    				$('.detailElements').hide();
    				$('.overviewElements').fadeIn(1000);
					
    				global_order_id = 0; 

        		}).hide();

			/**
			 *	copies order_items after revision into aixada_shop_item only if not already
			 *	validated items exist;  
			 */
			$("#btn_setShopDate").button({
				 icons: {
		        		primary: "ui-icon-cart"
		        	}
				 })
       			.click(function(e){
       				$.showMsg({
						msg:"Are you sure you all products have been revised and that you want to put the current items into people's cart?",
						buttons: {
							"<?=$Text['btn_ok'];?>":function(){						
								//changeProductStatus(product_id,'deactivateProduct');
								$(this).dialog("close");
							},
							"<?=$Text['btn_cancel'];?>" : function(){
								$( this ).dialog( "close" );
							}
						},
						type: 'confirm'});

       			})
       			.hide()
       			.addClass('ui-state-highlight');

			
			
			

			//interactivity for editing cells
			$('td.interactiveCell')
				.live('click', function(e){
										
				
										

				})
				//make each cell editable on mouseover. 
				.live('mouseover', function(e){

					if (!$(this).hasClass('editable')){

						$(this).children(':first')
							.addClass('editable')
							.editable('ctrlOrders.php', {
									submitdata : {
										oper: 'editQuantity',
										order_id : global_order_id
										},
									id : 'product_uf',
									name : 'quantity',
									indicator: 'Saving',
									tooltip: 	'click to edit'
						});

					}

				})
				
				
				//uncheck an entire product row (did not arrive)
				$('input:checkbox[name="hasArrived"]').live('click', function(e){
					var product_id = $(this).attr('hasArrivedId');
					var has_arrived = ($(this).is(':checked'))? 1:0;

					$.ajax({
						type: "POST",
						url: 'ctrlOrders.php?oper=toggleProduct&order_id='+global_order_id+'&product_id='+product_id+'&has_arrived='+has_arrived,
						success: function(txt){

							if (has_arrived){
								$('.Row-'+product_id).removeClass('deactivated'); //not working yet...?!
							} else {
								$('.Row-'+product_id).addClass('deactivated');
							}
						},
						error : function(XMLHttpRequest, textStatus, errorThrown){
							$.showMsg({
								msg:XMLHttpRequest.responseText,
								type: 'error'});
							
						},
						complete : function(msg){
							
						}

					});
				});
					
			

			
			/***********************************************************
			 *		ORDER OVERVIEW FUNCTIONALITY
			 **********************************************************/
			$('#tbl_orderOverview tbody').xml2html('init',{
				url : 'ctrlOrders.php',
				params : 'oper=getOrdersListing&filter=prevMonth', 
				loadOnInit : true, 
				rowComplete : function (rowIndex, row){
					var orderId = $(row).attr("id");
					var timeLeft = parseInt($(row).children().eq(3).text());
					
					if (orderId > 0 || timeLeft <= 0){ // order is closed
						$('#orderClosedIcon'+orderId).removeClass('ui-icon-unlocked').addClass('ui-icon-locked');
					} else {
						//while open and not send off, no order_id exists
						$(row).children(':first').html('<p>-</p>');
						$(row).children().eq(5).html('<p class="minPadding iconContainer"><span class="ui-icon ui-icon-alert ui-state-highlight floatLeft"></span>not yet send to provider</p>');
					}

					//set shopping date

				
					 	
				},
				complete : function (rowCount){
					$("#tbl_orderOverview").trigger("update"); 
					$('#tbl_orderOverview tbody tr:even').addClass('highlight');
				}
			});


			$("#tbl_orderOverview").tablesorter(); 

			
			$('.iconContainer')
				.live('mouseover', function(e){
					$(this).addClass('ui-state-hover');
				})
				.live('mouseout', function (e){
					$(this).removeClass('ui-state-hover');
				});
			

			//revise order icon 
			$('.ui-icon-check')
				.live('click', function(e){
					global_order_id = $(this).parents('tr').attr('id');

					$('.col').hide();

					//if table header ajax call has not finished, wait
					if (!tblHeaderComplete){
						$.showMsg({
							msg:'The table header is still being constructed. Depending on your internet connection this might take a little while. Try again in 5 seconds. ',
							type: 'error'});
						return false; 
					}

					//if table header ajax call has not finished, wait
					if (global_order_id <= 0){
						$.showMsg({
							msg:'No valid ID for order found!',
							type: 'error'});
						return false; 
					}
					

					//set info
					var provider_name = $(this).parents('tr').children().eq(2).text();
					$('#providerName').text(provider_name);
					
					
					$('.overviewElements').hide();
					$('.detailElements').fadeIn(1000);
					
					

					
					
					$('#tbl_reviseOrder tbody').xml2html("reload", {
						params : 'oper=getOrderedProductsList&order_id='+global_order_id
					})
					
					//$("#dialog_setShopDate").data('tmpData', {orderID:id});
					//$('#dialog_setShopDate').dialog('open');					
					
				});

			
			

			/*$('#dialog_setShopDate').dialog({
				autoOpen:false,
				width:500,
				height:540,
				buttons: {  
					"<?=$Text['btn_ok'];?>" : function(){
						
						//setClosingDate($(this).data('tmpData').orderDate); 
						},
				
					"<?=$Text['btn_cancel'];?>"	: function(){
						//$('td, th').removeClass('ui-state-hover');
						$( this ).dialog( "close" );
						} 
				}
			});*/

			
			/*$('tbody tr')
				.live('mouseover', function(e){
					$(this).addClass('ui-state-hover');
				})
				.live('mouseout', function(e){
					$(this).removeClass('ui-state-hover');
				});*/
			
			
			
			
	});  //close document ready
</script>


</head>
<body>
<div id="wrap">
	<div id="headwrap">
		<?php include "inc/menu2.inc.php" ?>
	</div>
	<!-- end of headwrap -->
	
	<div id="stagewrap">
	
		<div id="titlewrap" class="ui-widget">
			<div id="titleLeftCol">
				<button id="btn_overview" class="floatLeft detailElements">Overview</button>
				<h1 class="detailElements">Manager order detail for <span id="providerName"></span></h1>
		    	<h1 class="overviewElements">Manage orders</h1>
		    </div>
		   	<div id="titleRightCol">
		   		<button id="btn_setShopDate" class="detailElements floatRight">Activate for today's shopping!</button>
								
		   	</div> 	
		</div> <!--  end of title wrap -->
	
		<div id="orderOverview" class="ui-widget overviewElements">
			<div class="ui-widget-header ui-corner-all">
				<p>&nbsp;</p>
			</div>
			<div class="ui-widget-content">
			<table id="tbl_orderOverview" class="tblOverviewOrders">
				<thead>
					<tr>
						<th class="clickable">id <span class="ui-icon ui-icon-triangle-2-n-s floatRight"></span></th>
						<th class="clickable">Ordered for <span class="ui-icon ui-icon-triangle-2-n-s floatRight"></span></th>
						<th class="clickable">Provider <span class="ui-icon ui-icon-triangle-2-n-s floatRight"></span></th>
						<th>Days left</th>
						<th>&nbsp;</th>
						<th>Send off to provider</th>
						<th class="clickable">Shop date <span class="ui-icon ui-icon-triangle-2-n-s floatRight"></span></th>
						<th class="clickable">Total  <span class="ui-icon ui-icon-triangle-2-n-s floatRight"></span></th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<tr id="{id}">
						<td>{id}</td>
						<td class="textAlignCenter">{date_for_order}</td>
						<td class="textAlignRight minPadding">{provider_name}</td>
						<td class="textAlignCenter">{time_left}</td>
						<td class="textAlignCenter"><span id="orderClosedIcon{id}" class="tdIconCenter ui-icon ui-icon-unlocked"></span></td>
						<td class="textAlignCenter">{ts_send_off}</td>
						<td class="textAlignCenter">{date_for_shop}</td>
						<td class="textAlignRight">{order_total} €</td>
						<td class="textAlignCenter">
							<p class="ui-corner-all iconContainer ui-state-default floatLeft"><span class="ui-icon ui-icon-cart" title="Set shop date"></span></p>							
							<p class="ui-corner-all iconContainer ui-state-default floatRight"><span class="ui-icon ui-icon-check" title="Revise order"></span></p>
						</td>
					</tr>
				</tbody>
				<tfoot>
				
				</tfoot>
			</table>
			</div> <!-- widget content -->
		</div>
		
		
		<div id="reviseOrder" class="ui-widget detailElements">
			<div class="ui-widget-header ui-corner-all textAlignCenter">
				<h3 id="orderInfoDate">No shopping date set yet!! </h3>
			</div>
			<div class="ui-widget-content">
				<table id="tbl_reviseOrder" class="tblReviseOrder">
					<thead>
						<tr>
							<th>id</th>
							<th>Name</th>
							<th>Arrived</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>{id}</td>
							<td>{name}</td>
							<td class="textAlignCenter"><input type="checkbox" name="hasArrived" hasArrivedId="{id}" checked="checked" /></td>
						</tr>
					</tbody>
					<tfoot>
					
					</tfoot>
				</table>
			</div>
		</div>		
	
	</div>
	<!-- end of stage wrap -->
</div>
<!-- end of wrap -->


<!-- div id="dialog_setShopDate" title="Set shopping date">
	<p>&nbsp;</p>
	<p>If the current order has arrived, you can set a shopping date. This will place the corresponding products 
	into the shopping cart. 
	</p>
	<br/>
	<p>Select new closing date: </p>
	<br/>
	<div id="closingDatePicker"></div>
</div-->

<!-- / END -->
</body>
</html>












