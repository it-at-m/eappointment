import $ from "jquery";

const focusFirstErrorElement = (element) => {
    const $form = $(element)
    $form.find(".has-error").first().find(".form-control").trigger("focus");
}

export default focusFirstErrorElement
