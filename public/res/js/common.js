(function ($) {

$.fn.waitUntilExists    = function (handler, shouldRunHandlerOnce, isChild) {
    var found       = 'found';
    var $this       = $(this.selector);
    var $elements   = $this.not(function () { return $(this).data(found); }).each(handler).data(found, true);

    if (!isChild)
    {
        (window.waitUntilExists_Intervals = window.waitUntilExists_Intervals || {})[this.selector] =
            window.setInterval(function () { $this.waitUntilExists(handler, shouldRunHandlerOnce, true); }, 500)
        ;
    }
    else if (shouldRunHandlerOnce && $elements.length)
    {
        window.clearInterval(window.waitUntilExists_Intervals[this.selector]);
    }

    return $this;
}

}(jQuery));
var filter = new Array();
function filterInventory(){
	if($.inArray($(this).data("filter"), filter)==-1){
		filter.push($(this).data("filter"));
	}else{
		filter.splice($.inArray($(this).data("filter"), filter), 1);
	}
	$('.filterInventory').each(function(){
		if($.inArray($(this).data("filter"), filter)==-1){
			if($(this).data("filter")=="hasDuplicate"){
				$('.item').show();
				$('.list-row').show();
			}else
				$('.'+$(this).data("filter")).hide();
		}else{
			if($(this).data("filter")=="hasDuplicate"){
	            $('.item').hide();
				$('.hasDuplicate').show();
				$('.game-items').each(function(){
					if($(this).children('.hasDuplicate').size()==0)
						$(this).parent().hide();
				});
			}else if($.inArray("hasDuplicate", filter)==-1)
				$('.'+$(this).data("filter")).show();
			else{
				$('.'+$(this).data("filter")).each(function(){
					if($(this).children('.game-items').children('.hasDuplicate').size()>0)
						$(this).show();
				});
			}
		}
	});
}
$(document).ready(function() {
	$('.filterInventory').each(function(){
		if($(this).data("default")!="remove")
	    	filter.push($(this).data("filter"));
    });
	$(".filterInventory").click(filterInventory);
});

