const validate = data => {
    let valid = true
    const errors = {}

    if (!data.type) {
        errors.type = 'Typ erforderlich'
        valid = false
    }

    return {
        valid,
        errors
    }
}

export default validate
