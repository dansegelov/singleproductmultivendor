$product_type = '';	
$product_cat = '';
$product_taxonomy = {};
$product_vendor = '';

jQuery(document).ready(function($) {
	//$('#dropdown_vendor_multi').select2();

	$spmv_products_table = $('#single-products-tbl').DataTable( {
		"processing": true,
		"serverSide": true,
		"responsive": true,
		"pageLength": parseInt(dataTables_config.pageLength),
		"language"  : $.parseJSON(dataTables_language),
		
		'ajax': {
			"type"   : "POST",
			"url"    : spmv_params.ajax_url,
			"data"   : function( d ) {
				d.action     = 'spmv_ajax_controller'
				
			},
			"complete" : function () {
				$('#dropdown_vendor_multi').select2();

				//initiateTip();
				//if (typeof intiateWCFMuQuickEdit !== 'undefined' && $.isFunction(intiateWCFMuQuickEdit)) //intiateWCFMuQuickEdit();
				
				// Fire wcfm-products table refresh complete
				$( document.body ).trigger( 'updated_wcfm-products' );
			}
		}
	} );

	$( document.body ).on( 'updated_wcfm-products', function() {
		$('#assign_to_verndors').each(function() {
			$(this).change(function(event) {
				//event.preventDefault();
				
					AssignAllVedorstoProducts($(this));
				return false;
			});
		});
	});

	function AssignAllVedorstoProducts(item) {
		 $('.dataTables_processing', $('#single-products-tbl').closest('.dataTables_wrapper')).show();
		var data = {
			action : 'spmv_assign_all_stores',
			is_checked : item.prop("checked"),
			proid : item.data('proid')
		}	
		jQuery.ajax({
			type:		'POST',
			url: spmv_params.ajax_url,
			data: data,
			success:	function(response) {
				//if($spmv_products_table) $spmv_products_table.ajax.reload();
				 $('.dataTables_processing', $('#single-products-tbl').closest('.dataTables_wrapper')).hide();
				
			}
		});
	}

	$( document.body ).on( 'updated_wcfm-products', function() {
		$('#dropdown_vendor_multi').each(function() {
			$(this).change(function(event) {
				event.preventDefault();
				
					ExcludeVedorsformProducts($(this));
				return false;
			});
		});
	});

	function ExcludeVedorsformProducts(item) {
		$('.dataTables_processing', $('#single-products-tbl').closest('.dataTables_wrapper')).show();
		var data = {
			action : 'spmv_exclude_stores',
			exclude : item.val(),
			proid : item.data('proid')
		}	
		jQuery.ajax({
			type:		'POST',
			url: spmv_params.ajax_url,
			data: data,
			success:	function(response) {
				$('.dataTables_processing', $('#single-products-tbl').closest('.dataTables_wrapper')).hide();
			}
		});
	}
	
	/*if( $('#dropdown_product_type').length > 0 ) {
		$('#dropdown_product_type').on('change', function() {
		  $product_type = $('#dropdown_product_type').val();
		  $spmv_products_table.ajax.reload();
		});
	}
	
	if( $('#dropdown_product_cat').length > 0 ) {
		$('#dropdown_product_cat').on('change', function() {
			$product_cat = $('#dropdown_product_cat').val();
			$spmv_products_table.ajax.reload();
		}).select2( $wcfm_taxonomy_select_args );
	}
	
	if( $('.dropdown_product_custom_taxonomy').length > 0 ) {
		$('.dropdown_product_custom_taxonomy').each(function() {
			$(this).on('change', function() {
				$product_taxonomy[$(this).data('taxonomy')] = $(this).val();
				$spmv_products_table.ajax.reload();
			}).select2();
		});
	}
	
	if( $('#dropdown_vendor').length > 0 ) {
		$('#dropdown_vendor').on('change', function() {
			$product_vendor = $('#dropdown_vendor').val();
			$spmv_products_table.ajax.reload();
		}).select2( $wcfm_vendor_select_args );
	}*/
	
	// Approve Product
	/*$( document.body ).on( 'updated_wcfm-products', function() {
		$('.wcfm_product_approve').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				var rconfirm = confirm(wcfm_dashboard_messages.product_approve_confirm);
				if(rconfirm) approveWCFMProduct($(this));
				return false;
			});
		});
	});
	
	function approveWCFMProduct(item) {
		jQuery('#wcfm-products_wrapper').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action : 'wcfm_product_approve',
			proid : item.data('proid')
		}	
		jQuery.ajax({
			type:		'POST',
			url: spmv_params.ajax_url,
			data: data,
			success:	function(response) {
				if($spmv_products_table) $spmv_products_table.ajax.reload();
				jQuery('#wcfm-products_wrapper').unblock();
			}
		});
	}
	
	// Reject Product
	$( document.body ).on( 'updated_wcfm-products', function() {
		$('.wcfm_product_reject').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				var rconfirm = prompt(wcfm_dashboard_messages.product_reject_confirm);
				if(rconfirm) rejectWCFMProduct($(this), rconfirm);
				return false;
			});
		});
	});
	
	function rejectWCFMProduct( item, rconfirm ) {
		jQuery('#wcfm-products_wrapper').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action : 'wcfm_product_reject',
			proid  : item.data('proid'),
			reason : rconfirm
		}	
		jQuery.ajax({
			type:		'POST',
			url: spmv_params.ajax_url,
			data: data,
			success:	function(response) {
				if($spmv_products_table) $spmv_products_table.ajax.reload();
				jQuery('#wcfm-products_wrapper').unblock();
			}
		});
	}
	
	// Archive Product
	$( document.body ).on( 'updated_wcfm-products', function() {
		$('.wcfm_product_archive').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				var rconfirm = confirm(wcfm_dashboard_messages.product_archive_confirm);
				if(rconfirm) archiveWCFMProduct($(this));
				return false;
			});
		});
	});
	
	function archiveWCFMProduct(item) {
		jQuery('#wcfm-products_wrapper').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action : 'wcfm_product_archive',
			proid : item.data('proid')
		}	
		jQuery.ajax({
			type:		'POST',
			url: spmv_params.ajax_url,
			data: data,
			success:	function(response) {
				if($spmv_products_table) $spmv_products_table.ajax.reload();
				jQuery('#wcfm-products_wrapper').unblock();
			}
		});
	}
	
	// Delete Product
	$( document.body ).on( 'updated_wcfm-products', function() {
		$('.wcfm_product_delete').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				var rconfirm = confirm(wcfm_dashboard_messages.product_delete_confirm);
				if(rconfirm) deleteWCFMProduct($(this));
				return false;
			});
		});
	});
	
	function deleteWCFMProduct(item) {
		jQuery('#wcfm-products_wrapper').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action : 'delete_wcfm_product',
			proid : item.data('proid')
		}	
		jQuery.ajax({
			type:		'POST',
			url: spmv_params.ajax_url,
			data: data,
			success:	function(response) {
				if($spmv_products_table) $spmv_products_table.ajax.reload();
				jQuery('#wcfm-products_wrapper').unblock();
			}
		});
	}
	*/
	// Dashboard FIlter
	/*if( $('.wcfm_filters_wrap').length > 0 ) {
		$('.dataTable').before( $('.wcfm_filters_wrap') );
		$('.wcfm_filters_wrap').css( 'display', 'inline-block' );
	}
	
	// Screen Manager
	$( document.body ).on( 'updated_wcfm-products', function() {
		$.each(spmv_params, function( column, column_val ) {
		  $spmv_products_table.column(column).visible( false );
		} );
	});*/
	
} );