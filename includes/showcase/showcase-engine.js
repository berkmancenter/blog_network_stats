jQuery(document).ready(function (){
    jQuery('.showcase_section').masonry({
        itemSelector: '.showcase_item',
        isResizable: true,
        animationOptions: {
            duration: 200
        }
    });
});

function showcase_open(selector, link){
    jQuery(".current_showcase_section_link").removeClass('current_showcase_section_link');
    jQuery(link).addClass('current_showcase_section_link');
    jQuery(".showcase_section").hide();
    jQuery(selector)
        .show()
        .masonry('reload');
    return false;
}