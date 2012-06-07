/**
 * jQuery plugin to detach and re-attach select elements
 * as required on the send page. This is a temporary hack
 * until the QFAMS selectbox widget is replaced
 * @author Dave Hulbert <dave1010@gmail.com>
 * @link https://gist.github.com/738260
 */

(function($){

    $.fn.extend({detachOptions: function(o) {
        var s = this;
        return s.each(function(){
            var d = s.data('selectOptions') || [];
            s.find(o).each(function() {
                d.push($(this).detach());
            });
            s.data('selectOptions', d);
        });
    }, attachOptions: function(o) {
        var s = this;
        return s.each(function(){
            var d = s.data('selectOptions') || [];
            for (var i in d) {
                if (d[i].is(o)) {
                    s.append(d[i]);
//                    console.log(d[i]);
                    d.splice(i, 1);
                    s.data('selectOptions', d);
                }
            }
           
        });
    }});

})(jQuery);