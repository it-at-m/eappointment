package zms.ataf.rest.dto.zmscitizenapi;

import java.io.IOException;

import com.fasterxml.jackson.core.JsonParser;
import com.fasterxml.jackson.core.JsonToken;
import com.fasterxml.jackson.databind.DeserializationContext;
import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.deser.std.StdDeserializer;

/**
 * Office schema allows {@code address} to be either an object or an array.
 * In practice we sometimes receive nested arrays; we try to locate the first object
 * containing the address keys and map it onto {@link Office.Address}.
 */
public class AddressDeserializer extends StdDeserializer<Office.Address> {

    public AddressDeserializer() {
        super(Office.Address.class);
    }

    @Override
    public Office.Address deserialize(JsonParser p, DeserializationContext ctxt) throws IOException {
        JsonToken token = p.currentToken();
        if (token == JsonToken.VALUE_NULL) {
            return null;
        }

        JsonNode node = p.readValueAsTree();
        if (node == null || node.isNull()) {
            return null;
        }

        if (node.isObject()) {
            return toAddressFromObject(node);
        }

        if (node.isArray()) {
            JsonNode addressObjectNode = findFirstAddressObject(node);
            if (addressObjectNode != null && addressObjectNode.isObject()) {
                return toAddressFromObject(addressObjectNode);
            }
        }

        // Unknown structure: don't fail the whole Office deserialization.
        return null;
    }

    private Office.Address toAddressFromObject(JsonNode objNode) {
        Office.Address address = new Office.Address();
        address.setHouseNumber(textOrNull(objNode.get("house_number")));
        address.setCity(textOrNull(objNode.get("city")));
        address.setPostalCode(textOrNull(objNode.get("postal_code")));
        address.setStreet(textOrNull(objNode.get("street")));

        JsonNode hintNode = objNode.get("hint");
        address.setHint(hintNode != null && hintNode.isNull() ? null : booleanOrNull(hintNode));
        return address;
    }

    private JsonNode findFirstAddressObject(JsonNode arrayNode) {
        for (JsonNode element : arrayNode) {
            if (element == null || element.isNull()) {
                continue;
            }
            if (element.isObject() && looksLikeAddressObject(element)) {
                return element;
            }
            if (element.isArray()) {
                JsonNode nested = findFirstAddressObject(element);
                if (nested != null) {
                    return nested;
                }
            }
        }
        return null;
    }

    private boolean looksLikeAddressObject(JsonNode objNode) {
        return objNode.has("house_number") || objNode.has("city") || objNode.has("postal_code") || objNode.has("street")
                || objNode.has("hint");
    }

    private String textOrNull(JsonNode node) {
        return node == null || node.isNull() ? null : node.asText(null);
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
}
