/**
 * Adds client-side handling for integer-only configuration fields.
 *
 * The module prevents non-digit input and normalizes empty integer fields
 * according to their field configuration.
 */

const INTEGER_FIELDS = [
    {
        name: 'preferences[appointment][startInDaysDefault]',
        nullable: false
    },
    {
        name: 'preferences[appointment][endInDaysDefault]',
        nullable: false
    },
    {
        name: 'preferences[appointment][reservationDuration]',
        nullable: false
    },
    {
        name: 'preferences[appointment][activationDuration]',
        nullable: false
    },
    {
        name: 'preferences[appointment][deallocationDuration]',
        nullable: false
    },
    {
        name: 'preferences[client][appointmentsPerMail]',
        nullable: true
    },
    {
        name: 'preferences[client][slotsPerAppointment]',
        nullable: true
    },
    {
        name: 'preferences[queue][callCountMax]',
        nullable: false
    },
    {
        name: 'preferences[queue][firstNumber]',
        nullable: false
    },
    {
        name: 'preferences[queue][lastNumber]',
        nullable: false
    },
    {
        name: 'preferences[queue][maxNumberContingent]',
        nullable: false
    },
    {
        name: 'preferences[queue][processingTimeAverage]',
        nullable: false
    },
    {
        name: 'sendEmailReminderMinutesBefore',
        nullable: false
    }
];

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

const normalizeIntegerField = (input, nullable) => {
    sanitizeCurrentValue(input);

    if (!nullable && input.value === '') {
        input.value = '0';
    }
};

const bindIntegerField = (input, nullable) => {
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
        normalizeIntegerField(input, nullable);
    });
};

const bindSubmitNormalization = (form) => {
    if (form.dataset.integerSubmitNormalizationBound === '1') {
        return;
    }

    form.dataset.integerSubmitNormalizationBound = '1';

    form.addEventListener('submit', () => {
        INTEGER_FIELDS.forEach((field) => {
            const input = form.querySelector(toInputSelector(field.name));

            if (input) {
                normalizeIntegerField(input, field.nullable);
            }
        });
    });
};

export default function integerFields(form) {
    INTEGER_FIELDS.forEach((field) => {
        const input = form.querySelector(toInputSelector(field.name));

        if (input) {
            bindIntegerField(input, field.nullable);
        }
    });

    bindSubmitNormalization(form);
}