# Reduce JSON-Export for source DLDB to qualify for the used license in this repository

.data[] 
    |= {
        id,
        name,
        relation: ( .relation
            |{
                root_topic
            }
        ),
        meta: ( .meta
            |{
                url,
                locale,
                lastupdate
            }
        ),
        locations:( .locations
            |arrays
            |.[] 
            |= {
                location,
                appointment:( .appointment
                    | {
                        slots,
                        allowed
                    }
                )
            }
        )
    }