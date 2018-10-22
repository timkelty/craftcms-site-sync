;(function($, window, document, undefined)
{
  var pluginName = 'siteSyncField';
  var defaults = {};

  function Plugin(element, options)
  {
    this.element = element;
    this.$element = $(element);
    this.options = $.extend({}, defaults, options);
    this._defaults = defaults;
    this._name = pluginName;
    this.init();
  }

  Plugin.prototype = {
    init: function() {
      var instance = this;
      this.$element.find('.SiteSyncField-toggleField .lightswitch')
        .on('change', function() {
          instance.handleToggle.call(instance, this);
        })
        .trigger('change');
    },

    handleToggle: function(lightswitch) {
      var $lightswitch = $(lightswitch);
      var on = $lightswitch.data('lightswitch').on;
      var $panel = this.$element.find('.SiteSyncField-panel');
      this.$element.toggleClass('is-enabled', on);
      $panel.find(':input').prop('disabled', !on);
      $panel.add($panel.find('.lightswitch')).toggleClass('disabled', !on);
    }
  };

  // A really lightweight plugin wrapper around the constructor,
  // preventing against multiple instantiations
  $.fn[pluginName] = function ( options ) {
    return this.each(function () {
      if (!$.data(this, "plugin_" + pluginName)) {
        $.data(this, "plugin_" + pluginName,
        new Plugin( this, options ));
      }
    });
  };

})(jQuery, window, document);
