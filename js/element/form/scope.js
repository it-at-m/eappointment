import $ from "jquery";

const scopeChangeProvider = (element) => {
    const $form = $(element)

    $form.on('change', () => {
        let contact = $form.find('#provider__id option:selected').data('contact');
        $form.find('input[name="contact[name]"]').val(contact.name);
        $form.find('input[name="contact[street]"]').val(contact.street + " " + contact.streetNumber);
    })
}

export default scopeChangeProvider
