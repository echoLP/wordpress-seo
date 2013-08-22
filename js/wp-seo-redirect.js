jQuery(document).ready( function($) {
    // $('.meta-box-sortables').sortable({
    //     disabled: true
    // });

    $('.postbox .hndle').css('cursor', 'pointer');
});

function redirectEditOverride() {
    var $ = jQuery;

    var _init = inlineEditPost.init;
    inlineEditPost.init = function() {
    	var args = [].slice.call(arguments);
        _init.apply(this, args);

        var qeRow = $('#inline-edit');
        $('a.delete', qeRow).click(function(){
			return inlineEditPost.delete();
		});

		$('button.cancel', qeRow).click(function(){
			return inlineEditPost.revert();
		});

		$('button.save', qeRow).click(function(){
			return inlineEditPost.save(this);
		});

		// clear inputs
		$('button.clear').click( function() {
			var inputs = $('button.clear').parents('fieldset').find('input[type=text]');
			inputs.each( function() {
				$(this).val('');
			});
		});

    };

    inlineEditPost.delete = function(id) {

	}

    var _edit = inlineEditPost.edit;
    inlineEditPost.edit = function(id) {
        var args = [].slice.call(arguments);
        _edit.apply(this, args);

        if (typeof(id) == 'object') {
            id = this.getId(id);
        }

        if (this.type == 'post') {
            var
            // editRow is the quick-edit row, containing the inputs that need to be updated
            editRow = $('#edit-' + id),
            // postRow is the row shown when a book isn't being edited, which also holds the existing values.
            postRow = $('#post-'+id);

            // get the existing values
            var old_url = $('.column-old_url', postRow).text();
            var new_url = $('.column-new_url', postRow).text();

            // set the values in the quick-editor
            $(':input[name="old_url"]', editRow).val(old_url);
            $(':input[name="new_url"]', editRow).val(new_url);
        }
    };


    inlineEditPost.save = function(id) {
		var params, fields, page = $('.post_status_page').val() || '';

		if ( typeof(id) == 'object' )
			id = this.getId(id);

		$('table.widefat .spinner').show();

		params = {
			action: 'redirect_save',
			post_ID: id,			
		};

		fields = $('#edit-'+id+' :input').serialize();
		params = fields + '&' + $.param(params);

		// make ajax request
		$.post( ajaxurl, params,
			function(r) {
				$('table.widefat .spinner').hide();

				if (r) {
					if ( -1 != r.indexOf('<tr') ) {
						$(inlineEditPost.what+id).remove();
						$('#edit-'+id).before(r).remove();
						$(inlineEditPost.what+id).hide().fadeIn();
					} else {
						r = r.replace( /<.[^<>]*?>/g, '' );
						$('#edit-'+id+' .inline-edit-save .error').html(r).show();
					}
				} else {
					$('#edit-'+id+' .inline-edit-save .error').html(inlineEditL10n.error).show();
				}
			}
		, 'html');
		return false;
	};
}

// Another way of ensuring inlineEditPost.edit isn't patched until it's defined
if (inlineEditPost) {
    redirectEditOverride();
} else {
    jQuery(redirectEditOverride);
}