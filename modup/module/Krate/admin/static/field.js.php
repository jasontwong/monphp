$(document).ready(function(){
    $('input.color').ColorPicker({
        onSubmit: function(hsb, hex, rgb, el) {
		    $(el)
                .val(hex.toUpperCase())
		        .ColorPickerHide();
	    },
        onBeforeShow: function () {
		    $(this).ColorPickerSetColor(this.value);
	    }
        
    });
});
