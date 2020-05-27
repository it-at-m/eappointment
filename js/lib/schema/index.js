import JsonParser from "json-schema-ref-parser"
import JsonEntity from "json-schema-defaults"
import settings from "../../settings"

export const getEntity = (name) => {
    return JsonParser.dereference(`${settings.httpBaseUrl}/doc/swagger.json`).then(function (schema) {
        return JsonEntity(schema.definitions[name])
    }).catch(function (err) {
        console.error(err);
    });
    
}

