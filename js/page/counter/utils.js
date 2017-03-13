import $ from 'jquery'
const loaderHtml = '<div class="loader"></div>'

export const loadInto = (url, container, view, viewOptions = {}) => {
    container.find('.body').html(loaderHtml);

    return new Promise((resolve, reject) => {
        $.ajax(url, {
            method: 'GET'
        }).done(data => {
            container.empty();
            container.html(data);
            if (view) {
                new view(container, viewOptions);
            }
            resolve(container);
        }).fail(err => {
            console.log('XHR error', url, err)
            reject(err);
        })
    })
}
