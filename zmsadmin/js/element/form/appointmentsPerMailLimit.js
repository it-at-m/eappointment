export default function appointmentsPerMailLimit(root = document) {
    // Handled inline in scope form.twig so it works without a rebuilt JS bundle.
    // Keep this no-op import target for index.js compatibility.
    if (root.querySelector('#appointmentsPerMailUnlimited')) {
        return
    }
}
