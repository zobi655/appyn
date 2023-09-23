jpp2 = jQuery.noConflict();
jpp2(function($) {
	$('.upload_image_button').on( 'click', function() {
		formfield = $(this).prev('input');
		tb_show('', 'media-upload.php?type=file&amp;TB_iframe=true'); 
		var oldFunc = window.send_to_editor;
		window.send_to_editor = function(html) {
			if($(html).attr('src')) {
				imgurl = $(html).attr('src');
			} else if ($(html).attr('href')) {
				imgurl = $(html).attr('href');
			}
				formfield.val(imgurl);
				tb_remove();
				window.send_to_editor = oldFunc;
		};
		return false;
	});
});