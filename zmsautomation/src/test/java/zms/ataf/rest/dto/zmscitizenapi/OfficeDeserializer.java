package zms.ataf.rest.dto.zmscitizenapi;

import java.io.IOException;
import java.util.ArrayList;
import java.util.Iterator;
import java.util.List;

import com.fasterxml.jackson.core.JsonParser;
import com.fasterxml.jackson.core.JsonToken;
import com.fasterxml.jackson.databind.DeserializationContext;
import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.fasterxml.jackson.databind.deser.std.StdDeserializer;

/**
 * Office schema allows {@code Office} to be either an object or an array.
 * The array form doesn't define positional meaning in the schema, but in production
 * we observed that id and name appear at fixed indices (id @ 7, name @ 8).
 *
 * We map those indices and best-effort locate other fields:
 * - address by searching the array for an address-shaped object/array
 * - booleans/strings/numbers by simple top-level type inspection
 */
public class OfficeDeserializer extends StdDeserializer<Office> {

    public OfficeDeserializer() {
        super(Office.class);
    }

    @Override
    public Office deserialize(JsonParser p, DeserializationContext ctxt) throws IOException {
        JsonToken token = p.currentToken();
        if (token == JsonToken.VALUE_NULL) {
            return null;
        }

        JsonNode node = p.readValueAsTree();
        if (node == null || node.isNull()) {
            return null;
        }

        ObjectMapper mapper = (p.getCodec() instanceof ObjectMapper)
                ? (ObjectMapper) p.getCodec()
                : new ObjectMapper();

        Office office = new Office();
        if (node.isObject()) {
            populateFromObjectNode(office, node, mapper);
            return office;
        }

        if (node.isArray()) {
            populateFromArrayNode(office, node, mapper);
            return office;
        }

        return null;
    }

    private void populateFromObjectNode(Office office, JsonNode objNode, ObjectMapper mapper) throws IOException {
        office.setId(intOrNull(objNode.get("id")));
        office.setName(textOrNull(objNode.get("name")));
        office.setShowAlternativeLocations(booleanOrNull(objNode.get("showAlternativeLocations")));

        JsonNode addressNode = objNode.get("address");
        if (addressNode != null && !addressNode.isNull()) {
            office.setAddress(mapper.treeToValue(addressNode, Office.Address.class));
        }

        office.setDisplayNameAlternatives(stringArrayOrNull(objNode.get("displayNameAlternatives")));
        office.setOrganization(textOrNull(objNode.get("organization")));
        office.setOrganizationUnit(textOrNull(objNode.get("organizationUnit")));
        office.setSlotTimeInMinutes(intOrNull(objNode.get("slotTimeInMinutes")));

        JsonNode versionNode = objNode.get("version");
        if (versionNode != null && !versionNode.isNull() && versionNode.isNumber()) {
            office.setVersion(versionNode.doubleValue());
        }

        JsonNode parentIdNode = objNode.get("parentId");
        office.setParentId(intOrNull(parentIdNode));

        JsonNode geoNode = objNode.get("geo");
        if (geoNode != null && geoNode.isObject()) {
            office.setGeo(mapper.treeToValue(geoNode, Office.GeoCoordinates.class));
        }

        JsonNode scopeNode = objNode.get("scope");
        if (scopeNode != null && !scopeNode.isNull()) {
            office.setScope(mapper.treeToValue(scopeNode, Object.class));
        }

        office.setDisabledByServices(intArrayOrNull(objNode.get("disabledByServices")));
        office.setSlotsPerAppointment(textOrNull(objNode.get("slotsPerAppointment")));
    }

    private void populateFromArrayNode(Office office, JsonNode arrayNode, ObjectMapper mapper) throws IOException {
        // Observed fixed indices in production
        office.setId(intOrNull(index(arrayNode, 7)));
        office.setName(textOrNull(index(arrayNode, 8)));

        JsonNode addressCandidate = findFirstAddressCandidate(arrayNode);
        if (addressCandidate != null) {
            office.setAddress(mapper.treeToValue(addressCandidate, Office.Address.class));
        }

        // Best-effort: only assign if still missing
        for (JsonNode element : arrayNode) {
            if (element == null || element.isNull()) {
                continue;
            }
            if (office.getShowAlternativeLocations() == null && element.isBoolean()) {
                office.setShowAlternativeLocations(element.asBoolean());
                continue;
            }
            if (office.getOrganization() == null && element.isTextual() && !element.asText().equals(office.getName())) {
                // First string besides name is usually organization (best-effort).
                office.setOrganization(element.asText(null));
                continue;
            }
            if (office.getOrganizationUnit() == null && element.isTextual()) {
                office.setOrganizationUnit(element.asText(null));
                continue;
            }
            if (office.getSlotTimeInMinutes() == null && element.isNumber()) {
                office.setSlotTimeInMinutes(element.intValue());
                continue;
            }
            if (office.getParentId() == null && element.isNumber()) {
                office.setParentId(element.intValue());
                continue;
            }
        }

        // Disabled-by services (array of ints)
        JsonNode disabledNode = findFirstIntArrayNode(arrayNode);
        if (disabledNode != null) {
            office.setDisabledByServices(intArrayOrNull(disabledNode));
        }

        // Display name alternatives (array of strings)
        JsonNode altNamesNode = findFirstStringArrayNode(arrayNode);
        if (altNamesNode != null) {
            office.setDisplayNameAlternatives(stringArrayOrNull(altNamesNode));
        }
    }

    private JsonNode index(JsonNode arrayNode, int idx) {
        if (arrayNode == null || !arrayNode.isArray()) {
            return null;
        }
        if (idx < 0 || idx >= arrayNode.size()) {
            return null;
        }
        return arrayNode.get(idx);
    }

    private JsonNode findFirstAddressCandidate(JsonNode arrayNode) {
        if (arrayNode == null || !arrayNode.isArray()) {
            return null;
        }
        for (JsonNode element : arrayNode) {
            if (element == null || element.isNull()) {
                continue;
            }
            if (element.isObject() && looksLikeAddressObject(element)) {
                return element;
            }
            if (element.isArray() && containsAddressObjectRecursively(element)) {
                return element;
            }
        }
        return null;
    }

    private boolean looksLikeAddressObject(JsonNode objNode) {
        return objNode.has("house_number") || objNode.has("city") || objNode.has("postal_code") || objNode.has("street")
                || objNode.has("hint");
    }

    private boolean containsAddressObjectRecursively(JsonNode node) {
        if (node == null) {
            return false;
        }
        if (node.isObject()) {
            return looksLikeAddressObject(node);
        }
        if (node.isArray()) {
            for (JsonNode child : node) {
                if (containsAddressObjectRecursively(child)) {
                    return true;
                }
            }
        }
        return false;
    }

    private JsonNode findFirstIntArrayNode(JsonNode arrayNode) {
        for (JsonNode element : arrayNode) {
            if (element != null && element.isArray()) {
                boolean allInts = true;
                for (JsonNode item : element) {
                    if (item == null || item.isNull() || !item.isNumber()) {
                        allInts = false;
                        break;
                    }
                }
                if (allInts) {
                    return element;
                }
            }
        }
        return null;
    }

    private JsonNode findFirstStringArrayNode(JsonNode arrayNode) {
        for (JsonNode element : arrayNode) {
            if (element != null && element.isArray()) {
                boolean allStrings = true;
                for (JsonNode item : element) {
                    if (item == null || item.isNull() || !item.isTextual()) {
                        allStrings = false;
                        break;
                    }
                }
                if (allStrings) {
                    return element;
                }
            }
        }
        return null;
    }

    private Integer intOrNull(JsonNode node) {
        if (node == null || node.isNull()) {
            return null;
        }
        if (node.isInt() || node.isIntegralNumber()) {
            return node.intValue();
        }
        if (node.isTextual()) {
            try {
                return Integer.parseInt(node.asText());
            } catch (NumberFormatException ignored) {
                return null;
            }
        }
        return null;
    }

    private Boolean booleanOrNull(JsonNode node) {
        if (node == null || node.isNull()) {
            return null;
        }
        if (node.isBoolean()) {
            return node.asBoolean();
        }
        return null;
    }

    private String textOrNull(JsonNode node) {
        if (node == null || node.isNull()) {
            return null;
        }
        return node.isTextual() ? node.asText(null) : node.asText(null);
    }

    private List<String> stringArrayOrNull(JsonNode node) {
        if (node == null || node.isNull() || !node.isArray()) {
            return null;
        }
        List<String> out = new ArrayList<>();
        Iterator<JsonNode> it = node.elements();
        while (it.hasNext()) {
            JsonNode item = it.next();
            if (item != null && !item.isNull()) {
                out.add(item.asText());
            }
        }
        return out;
    }

    private List<Integer> intArrayOrNull(JsonNode node) {
        if (node == null || node.isNull() || !node.isArray()) {
            return null;
        }
        List<Integer> out = new ArrayList<>();
        for (JsonNode item : node) {
            Integer v = intOrNull(item);
            if (v != null) {
                out.add(v);
            }
        }
        return out;
    }
}
