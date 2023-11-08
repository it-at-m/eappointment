# Reduce JSON-Export for source DLDB to qualify for the used license in this repository

.data[] 
    |= {
        id,
        name,
        address: ( .address
            |{
                city,
                postal_code,
                street,
                house_number
            }
        ),
        office,
        authority: ( .authority
            |{
                id
            }
        ),
        geo: {lat:53.45, lon: 13.45},
        meta: ( .meta
            |{
                url,
                locale,
                lastupdate
            }
        ),
        appointment: ( .appointment
            |{
                multiple
            }
        ),
        services:( .services
            |arrays
            |.[] 
            |= {
                service,
                appointment:( .appointment
                    | {
                        slots,
                        external,
                        allowed
                    }
                )
            }
        )
    }
