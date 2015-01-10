/**
 * Website: http://steamep.com
 *
 * @author Swordbeta
 * @author Elinea
 * @version 2.0
 */

function cardSelectClick() {
    $(this).off("click");
    if($(this).hasClass("need")){
        $(this).prepend("<div class='card-option'>\n\
                        <div class='have'>Have</div>\n\
                        <div class='remove'>Remove</div>\n\
                        <div class='cancel'>Cancel</div>\n\
                        </div>")
    }
    else if($(this).hasClass("have")){
        $(this).prepend("<div class='card-option'>\n\
                        <div class='need'>Need</div>\n\
                        <div class='remove'>Remove</div>\n\
                        <div class='cancel'>Cancel</div>\n\
                        </div>")
    }
    else{
        $(this).prepend("<div class='card-option'>\n\
                        <div class='have'>Have</div>\n\
                        <div class='need'>Need</div>\n\
                        <div class='cancel'>Cancel</div>\n\
                        </div>")
    }
}
function cancelCard(){
    $(this).parent().parent().click(cardSelectClick);
    $(this).parent().parent().children(".card-option").remove();
}
function editCard() {
    var type = $(this).attr('class');
    $(this).parent().parent().click(cardSelectClick);
    var parent = $(this).parent().parent()

    var allSameItems = $('.item[data-item=' + parent.data("item") + ']');
    allSameItems.removeClass('have need');

    $(this).parent().html("<div class='loading'></div>");

    if (type == "need") {
        allSameItems.addClass('need');
        type = "n";
    }
    else if (type == "have") {
        allSameItems.addClass('have');
        type = "h";
    }
    else {
        type = "r";
    }

    $.ajax({url: pvar.editlist + '/' + type + '/' + parent.data("item"), success: function() {
        parent.children(".card-option").remove();
    }});
}
function selectAll(event) {
    var row = $(this).parent().parent().parent();
    row.children(".game-items").children().each(function(){
        $.ajax({url: pvar.editlist + '/' + event.data.type + '/' + $(this).data("item"), success: function() {
            row.children(".game-items").children().removeClass("have need");
            row.children(".game-items").children().addClass(event.data.class);
        }});
    });
}
function showOptions(){
    $(this).append("<div class='options'>\n\
                   <div class='select-all-need'><i class='icon-plus-sign-alt'></i><span>Need All</span></div>\n\
                   <div class='select-all-have'><i class='icon-plus-sign-alt'></i><span>Have All</span></div>\n\
                   <div class='select-all-none'><i class='icon-minus-sign-alt'></i><span>Remove All</span></div>\n\
                   </div>");
                   $(this).mouseleave(removeOptions);
}
function removeOptions(){
    $(this).children(".options").remove();
}
function showTooltip(){
	$(this).parent().parent().prepend("<div class='b_tooltip'>Click to select status</div>");
}
function hideTooltip(){
	$(this).parent().parent().find(".b_tooltip").remove();
}
$(document).ready(function() {
    if($('.selectpicker').length){
        $('.selectpicker').selectpicker();
        $('.selectpicker').change(function(){
            $('#selectForm').submit();
        });
    };
    $('body').on("click", ".card-option .have", editCard);
    $('body').on("click", ".card-option .need", editCard);
    $('body').on("click", ".card-option .remove", editCard);
    $('body').on("click", ".card-option .cancel", cancelCard);
    $('.game-banner').on("click", '.select-all-need', {type: 'n', class: 'need'}, selectAll);
    $('.game-banner').on("click", '.select-all-have', {type: 'h', class: 'have'}, selectAll);
    $('.game-banner').on("click", '.select-all-none', {type: 'r', class: 'remove'}, selectAll);
    $('.game-banner').mouseenter(showOptions);
	$(".item").hover(showTooltip,hideTooltip);
    $(".item").click(cardSelectClick);
});

