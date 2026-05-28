/**
 * Adds client-side handling for integer-only configuration fields.
 *
 * The module prevents non-digit input, normalizes empty integer fields to "0",
 * and applies the special appointments-per-mail rule where the value may be
 * empty but must be at least 2 if provided.
 */

const INTEGER_FIELD_NAMES = [
    'preferences[appointment][startInDaysDefault]',
    'preferences[appointment][endInDaysDefault]',
    'preferences[appointment][reservationDuration]',
    'preferences[appointment][activationDuration]',
    'preferences[appointment][deallocationDuration]',
    'preferences[client][slotsPerAppointment]',
    'preferences[queue][callCountMax]',
    'preferences[queue][firstNumber]',
    'preferences[queue][lastNumber]',
    'preferences[queue][maxNumberContingent]',
    'preferences[queue][processingTimeAverage]',
    'sendEmailReminderMinutesBefore'
];

const APPOINTMENTS_PER_MAIL_NAME = 'preferences[client][appointmentsPerMail]';

const toInputSelector = (name) => `input[name="${name}"]`;

const sanitizeIntegerValue = (value) => String(value).replace(/\D/g, '');

const isControlKey = (event) => (
    event.ctrlKey ||
    event.metaKey ||
    event.altKey ||
    [
        'Backspace',
        'Delete',
        'Tab',
        'Enter',
        'Escape',
        'ArrowLeft',
        'ArrowRight',
        'ArrowUp',
        'ArrowDown',
        'Home',
        'End'
    ].includes(event.key)
);

const preventInvalidKey = (event) => {
    if (isControlKey(event)) {
        return;
    }

    if (event.key && event.key.length === 1 && !/^\d$/.test(event.key)) {
        event.preventDefault();
    }
};

const preventInvalidBeforeInput = (event) => {
    if (!event.inputType || !event.inputType.startsWith('insert')) {
        return;
    }

    if (event.data && /\D/.test(event.data)) {
        event.preventDefault();
    }
};

const preventInvalidPaste = (event) => {
    const pastedValue = event.clipboardData.getData('text');

    if (/\D/.test(pastedValue)) {
        event.preventDefault();
    }
};

const sanitizeCurrentValue = (input) => {
    const sanitizedValue = sanitizeIntegerValue(input.value);

    if (input.value !== sanitizedValue) {
        input.value = sanitizedValue;
    }
};

const normalizeDefaultIntegerField = (input) => {
    sanitizeCurrentValue(input);

    if (input.value === '') {
        input.value = '0';
    }
};

const normalizeAppointmentsPerMailField = (input) => {
    sanitizeCurrentValue(input);

    if (input.value === '') {
        return;
    }

    const numericValue = parseInt(input.value, 10);

    if (numericValue < 2) {
        input.value = '2';
    }
};

const bindIntegerField = (input, normalize) => {
    if (input.dataset.integerFieldBound === '1') {
        return;
    }

    input.dataset.integerFieldBound = '1';

    input.setAttribute('inputmode', 'numeric');
    input.setAttribute('pattern', '[0-9]*');

    input.addEventListener('keydown', preventInvalidKey);
    input.addEventListener('beforeinput', preventInvalidBeforeInput);
    input.addEventListener('paste', preventInvalidPaste);

    input.addEventListener('input', () => {
        sanitizeCurrentValue(input);
    });

    input.addEventListener('blur', () => {
        normalize(input);
    });
};

const bindSubmitNormalization = (form) => {
    if (form.dataset.integerSubmitNormalizationBound === '1') {
        return;
    }

    form.dataset.integerSubmitNormalizationBound = '1';

    form.addEventListener('submit', () => {
        INTEGER_FIELD_NAMES.forEach((name) => {
            const input = form.querySelector(toInputSelector(name));

            if (input) {
                normalizeDefaultIntegerField(input);
            }
        });

        const appointmentsPerMailInput = form.querySelector(
            toInputSelector(APPOINTMENTS_PER_MAIL_NAME)
        );

        if (appointmentsPerMailInput) {
            normalizeAppointmentsPerMailField(appointmentsPerMailInput);
        }
    });
};

export default function integerFields(form) {
    INTEGER_FIELD_NAMES.forEach((name) => {
        const input = form.querySelector(toInputSelector(name));

        if (input) {
            bindIntegerField(input, normalizeDefaultIntegerField);
        }
    });

    const appointmentsPerMailInput = form.querySelector(
        toInputSelector(APPOINTMENTS_PER_MAIL_NAME)
    );

    if (appointmentsPerMailInput) {
        bindIntegerField(appointmentsPerMailInput, normalizeAppointmentsPerMailField);
    }

    bindSubmitNormalization(form);
}
