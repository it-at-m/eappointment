# Reduce JSON-Export for source DLDB to qualify for the used license in this repository

.data[] 
    |= {
        id,
        name,
        path,
        relation: ( .relation
            |{
                navi,
                services,
                childs
            }
        )
    }
