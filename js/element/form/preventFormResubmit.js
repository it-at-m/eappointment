import $ from "jquery";

const preventFormResubmit = (element) => {
    const $form = $(element)

    $form.on('submit', (ev) => {
        if ($form.attr('data-submitting')) {
            ev.stopPropagation();
            ev.preventDefault();
        }

        $form.attr('data-submitting', true)
    })
}

export default preventFormResubmit
