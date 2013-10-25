define(['backbone'], function(Backbone){
    var ShipperModel = Backbone.Model.extend({
        idAttribute: 'name',
        parse: function(data){
            return _.pick(data, 'name', 'enabled');
        }
    });

    var ShipperCollection = Backbone.Collection.extend({
        model: ShipperModel,
        url: $('#website_url').val()+'api/store/shippers/name/'
    });

    var AppView = Backbone.View.extend({
        el: $('#shippers'),
        events: {
            'click #submit': 'saveShipperConfig',
            'click [role=switch]': function (e){
                var name =  $(e.currentTarget).closest('li').find('a').data('plugin'),
                    plugin = this.shippers.get(name);
                if (_.isUndefined(plugin)){
                    plugin = new ShipperModel({name: name, enabled: 0, config: null});
                    this.shippers.add(plugin);
                }
                var status = plugin.get('enabled') === 1 ? 0 : 1;
                if(status){
                    $(e.currentTarget).closest('li').removeClass('disabled').addClass('enabled');
                }else{
                    $(e.currentTarget).closest('li').removeClass('enabled').addClass('disabled');
                }
                plugin.set('enabled', status);
                plugin.save();
                $(e.currentTarget).prop('checked', plugin.toJSON().enabled ? true : false);
            }
        },
        initialize: function(){
            this.shippers = new ShipperCollection();
            this.shippers.on('reset', this.render, this);
            this.shippers.fetch();
        },
        render: function(){
            var self = this;
            $('ul.ui-tabs-nav a[data-plugin]').each(function(){
                var plugin = self.shippers.get($(this).data('plugin'));
                if (plugin) {
                    console.log(plugin.toJSON().enabled )
                    $(this).closest('li').addClass(!!plugin.get('enabled')?'enabled':'disabled').find(':checkbox').prop('checked', plugin.toJSON().enabled ? true : false);
                } else {
                    $(this).closest('li').addClass('disabled').find(':checkbox').prop('checked', false);
                }
            });
            checkboxRadio();
        },
        saveShipperConfig: function(){
            var index = this.$el.tabs( "option", "active" ),
                currentPane = $('#pane-container div.ui-tabs-panel:eq('+index+')');
            if (currentPane){
                var form = currentPane.find('form');
                $.ajax({
                    url: form.attr('action'),
                    data: form.serialize(),
                    dataType: 'json',
                    type: form.attr('method'),
                    complete: function(response){
                        form.trigger('formsave', response);
                    }
                });
            }
        }
    });

    return AppView;
});