(function( $ ) {
	'use strict';
	
		$.fn.wplheEditNote= function(){
			var entarget = $(this).closest('.column-wplhe_note_column').find('.wplhe_note_container');
			if(!entarget.hasClass('extended_note')) {
				entarget.addClass('extended_note');
				var editorId = entarget.find('.wplhe_editor_container > div').attr('id').replace('wp-','').replace('-wrap','');
				var editor = tinymce.get(editorId);
				//if tinymce (visual editor) is opened
				if(editor !== null){
					editor.off('NodeChange keyup').on('NodeChange keyup',function(){
						entarget.find('.wplhe_note').html(tinymce.editors[editorId].getContent());
					});
					entarget.find('.wplhe_editor_container .wp-switch-editor.switch-html').on('click',function(){
						setTimeout(function(){
							$('#'+editorId).off('keyup').on('keyup',function(){
								entarget.find('.wplhe_note').html($(this).val());
							});
						},100);
					});
				}
				//if texteditor is opened
				else{
					$('#'+editorId).off('keyup').on('keyup',function(){
						entarget.find('.wplhe_note').html($(this).val());
					});
					entarget.find('.wplhe_editor_container .wp-switch-editor.switch-tmce').on('click',function(){
						setTimeout(function(){
							var editor = tinymce.get(editorId);
							editor.off('NodeChange keyup').on('NodeChange keyup',function(){
								entarget.find('.wplhe_note').html(tinymce.editors[editorId].getContent());
							});
						},100);
					});
				}
			}
			else entarget.removeClass('extended_note');
		};
		$.fn.wplheSaveNote = function(active=true){
			var snel = $(this);
			var sntarget = snel.closest('.wplhe_plugin_note_form');
			var sntarget2 = snel.closest('.wplhe_note_container');
			var file = sntarget.find('input[name="plugin_file"]').val();
			var type = sntarget.find('select[name="wplhe_plugin_note_type"]').val();
			var nonce = sntarget.find('input[name="_wpnonce"]').val();
			
			var editorId = sntarget.find('.wplhe_editor_container > div').attr('id').replace('wp-','').replace('-wrap','');
			
			var note = tinymce.editors[editorId].getContent();
			
			var data = {
				'action': 'wplhe_save_note',
				'plugin_file': file,
				'plugin_note': note,
				'plugin_type': type,
				'_wpnonce': nonce,
			};
			
			jQuery.post(ajaxurl, data, function(response) {
				sntarget.find('.spinner.wplhe_update_note').removeClass('active');
				sntarget2.removeClass('extended_note');
				if(!sntarget2.hasClass('active_note') && active) snel.closest('.wplhe_note_container').addClass('active_note');
				sntarget2.closest('.column-wplhe_note_column').find('.wplhe_add_note').addClass('wplhehide');
				sntarget2.closest('.column-wplhe_note_column').find('.wplhe_edit_note').removeClass('wplhehide');
				sntarget2.closest('.column-wplhe_note_column').find('.wplhe_remove_note').removeClass('wplhehide');
			});
		};
		$.fn.wplheRemoveNote = function(){
			var rnel = $(this);
			var rntarget = rnel.closest('.column-wplhe_note_column').find('.wplhe_note_container');
			var file = rnel.closest('.column-wplhe_note_column').find('.wplhe_plugin_note_form input[name="plugin_file"]').val();
			var editorId = rntarget.find('.wplhe_editor_container > div').attr('id').replace('wp-','').replace('-wrap','');
			var nonce = rntarget.find('input[name="_wpnonce"]').val();
			
			var data = {
				'action': 'wplhe_remove_note',
				'plugin_file': file,
				'_wpnonce': nonce,
			};
			
			jQuery.post(ajaxurl, data, function(response) {
				rntarget.closest('.column-wplhe_note_column').find('.spinner.wplhe_update_note2').removeClass('active');
				rntarget.removeClass('extended_note active_note');
				rntarget.find('.wplhe_note').html('');
				tinymce.editors[editorId].setContent('');
				rntarget.closest('.column-wplhe_note_column').find('.wplhe_add_note').removeClass('wplhehide');
				rntarget.closest('.column-wplhe_note_column').find('.wplhe_edit_note, .wplhe_remove_note').addClass('wplhehide');
			});
		};
		$.fn.wplheCancelNote = function(){
			var cnel = $(this);
			var cntarget = cnel.closest('.wplhe_plugin_note_form');
			var container = cnel.closest('.wplhe_note_container');
			var cntarget2 = container.find('.wplhe_note');
			var editorId = cntarget.find('.wplhe_editor_container > div').attr('id').replace('wp-','').replace('-wrap','');
			
			tinymce.editors[editorId].setContent('');
			cntarget2.html('');
			if(container.hasClass('active_note')) {
				if(typeof cntarget2.attr('data-oldcontent') !== 'undefined'){
					var oldC = cntarget2.attr('data-oldcontent');
					tinymce.editors[editorId].setContent(oldC);
					cntarget2.html(oldC);
				}
			}
			else container.removeClass('active_note');
			container.removeClass('extended_note');
			
			
		};
		
		$(document).on('click','.wplhe_add_note, .wplhe_edit_note',function(e){
			e.preventDefault();
			$(this).wplheEditNote();
		});
		$(document).on('click','.wplhe_save_note',function(e){
			e.preventDefault();
			$(this).closest('.column-wplhe_note_column').find('.spinner.wplhe_update_note').addClass('active');
			$(this).wplheSaveNote();
		});
		$(document).on('click','.wplhe_remove_note',function(e){
			e.preventDefault();
			$(this).closest('.wplhe_plugin_notes_links').find('.spinner.wplhe_update_note2').addClass('active');
			$(this).wplheRemoveNote();
		});
		$(document).on('click','.wplhe_cancel',function(e){
			e.preventDefault();
			$(this).wplheCancelNote();
		});
		$(document).on('change','select[name="wplhe_plugin_note_type"]',function(e){
			e.preventDefault();
			var target = $(this).closest('.wplhe_note_container');
			target.removeClass('wplhe_alert wplhe_info wplhe_success');
			target.addClass($(this).val());
		});

})( jQuery );
