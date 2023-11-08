import $ from "jquery";

const preventFormResubmit = (element) => {
    const $form = $(element)

    $form.on('submit', (ev) => {
        if ($form.attr('data-submitting')) {
            ev.stopPropagation();
            ev.preventDefault();
        }

        $form.attr('data-submitting', true)
        setTimeout(() => {
            $form.off('submit').attr('data-submitting', false)
        }, 3000)
    })
}

export default preventFormResubmit
