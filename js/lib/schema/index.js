import JsonParser from "json-schema-ref-parser"
import path from "path"
import JsonEntity from "json-schema-defaults"
import Definitions from "./definitions"

export const getEntity = (name) => {
    return JsonParser.dereference(Definitions[name], { resolve: { file: jsonFileResolver, http: false } }).then(function (schema) {
        return JsonEntity(schema)
    }).catch(function (err) {
        console.error(err);
    });
}

const jsonFileResolver = {
    order: 1,
    canRead: function (file) {
        return file.url.indexOf(".json") !== -1;
    },
    read: function (file) {
        return getResolvedFile(file.url)
    }
}

const getResolvedFile = (url) => {
    var filename = path.basename(url, '.json')
    return Definitions[filename]
}

