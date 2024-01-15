import $ from "jquery";

const preventFormResubmit = (element) => {
    const $form = $(element)

    $form.on('submit', (ev) => {
        if ($form.data('submitted') === true) {
          // Previously submitted - don't submit again
          ev.stopPropagation();
          ev.preventDefault();
        } else {
          // Mark it so that the next submit can be ignored
          $form.data('submitted', true);
        }

    })
}

export default preventFormResubmit
