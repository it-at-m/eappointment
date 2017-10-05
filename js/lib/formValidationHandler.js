import BaseView from './baseview'
import $ from 'jquery'

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.$main = $(element);
        this.responseJson = options.responseJson;
        this.destroy();
        this.load();
    }

    load()
    {
        $.each(this.responseJson, (index, item) => {
            if (item.failed) {
                this.$main
                    .find('[name="' + index +'"], [name="' + index +'[]"], [name="' + index +'[][id]"], [data-form-validate="'+ index +'"]')
                    .closest('div.controls')
                    .addClass('has-error')
                    .append(this.getMessageList(item.messages));
            }
        });
    }

    getMessageList(messages) {
        let list = document.createElement('ul');
        $(list).addClass('list-error');
        $.each(messages, (index, messageItem) => {
            if (messageItem.message)
                $(list).append('<li>'+ messageItem.message +'</li>')
            else
                $(list).append('<li>'+ messageItem +'</li>')
        });
        return list;
    }

    destroy()
    {
        this.$main.find('.controls').removeClass('has-error');
        this.$main.find('.list-error').remove();
    }
}

export default View
