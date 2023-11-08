import JsonParser from "json-schema-ref-parser"
import JsonEntity from "json-schema-defaults"
import Definitions from "./definitions"

export const getEntity = (name) => {
    return JsonParser.parse(Definitions[name]).then(function (schema) {
        return JsonEntity(schema)
    }).catch(function (err) {
        console.error(err);
    });
}

