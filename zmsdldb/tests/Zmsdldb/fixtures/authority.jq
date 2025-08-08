# Reduce JSON-Export for source DLDB to qualify for the used license in this repository

.data[] 
    |= {
        id,
        name,
        contact: ( .contact
            |{
                webinfo: "http://example.com"
            }
        ),
        meta,
        relation,
        locations
    }
