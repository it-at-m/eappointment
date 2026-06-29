import { parseJsonText } from '../../lib/utils';

export function readJsonPayload($root, scriptSelector, fallbackDomAttributeName, parseContextLabel) {
    const $script = $root.find(scriptSelector).first();
    if ($script.length) {
        const jsonPayload = parseJsonText($script.text(), parseContextLabel);
        if (jsonPayload !== null) {
            $script.remove();
            return jsonPayload;
        }
    }

    if (!fallbackDomAttributeName) {
        return null;
    }

    const $legacyPayloadHost = $root.find(`[${fallbackDomAttributeName}]`).first();
    if (!$legacyPayloadHost.length) {
        return null;
    }

    const jsonPayload = parseJsonText(
        $legacyPayloadHost.attr(fallbackDomAttributeName),
        parseContextLabel
    );
    if (jsonPayload !== null) {
        $legacyPayloadHost.attr(fallbackDomAttributeName, '');
    }

    return jsonPayload;
}

export function getRowValue(row, info) {
    if (row == null || info == null) {
        return undefined;
    }
    if (typeof row === 'object') {
        if (info.variable != null && row[info.variable] !== undefined) {
            return row[info.variable];
        }
    }
    return row[info.position];
}

export function getCapacityTableRowValue(row, variable, position) {
    if (row == null) {
        return undefined;
    }

    if (typeof row === 'object' && !Array.isArray(row) && row[variable] !== undefined) {
        return row[variable];
    }

    return row[position];
}

export function reduceToField(info) {
    return (list, items) => {
        list.push(getRowValue(items, info));
        return list;
    };
}

export function getLabelInfo(dictionary, label) {
    for (const info of dictionary) {
        if (info.variable == label) {
            return info;
        }
    }
    throw 'CapacityReport: Label ' + label + ' not found';
}

export function getListByLabel(data, label) {
    const info = getLabelInfo(data.dictionary, label);
    return data.data.reduce(reduceToField(info), []);
}
