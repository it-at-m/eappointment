const templates = {
    link: {
        id: 0,
        name: '',
        url: '',
        target: 0
    },
    dayoff: {},
    provider: {
        contact: {
            city: '',
            country: '',
            lat: '',
            lon: '',
            name: '',
            postalCode: '',
            region: '',
            street: '',
            streetNumber: '',
            email: '',
            telephone: ''
        },
        id: '',
        link: '',
        data: {},
        name: '',
        source: ''
    },
    request: {
        id: '',
        link: '',
        data: {},
        group: '',
        name: '',
        source: '',
        timeSlotCount: 1,
        parent_id: null,
        root_parent_id: null,
        variant_id: null
    },
    requestrelation: {
        request: {
            id: '',
            link: '',
            data: {},
            group: '',
            name: '',
            source: '',
            timeSlotCount: 1
        },
        provider: {
            id: '',
            link: '',
            data: {},
            name: '',
            source: ''
        },
        slots: 1,
        source: ''
    }
}

export const getEntity = (name) => {
    const template = templates[name]
    if (!template) {
        console.error(new Error(`Unknown entity "${name}"`))
        return Promise.resolve()
    }
    return Promise.resolve(structuredClone(template))
}
