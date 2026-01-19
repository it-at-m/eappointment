package ataf.rest.helper;

import io.restassured.specification.MultiPartSpecification;

import java.nio.charset.StandardCharsets;

/**
 * This class handles the body content and multi-part specifications for HTTP requests.
 *
 * @author Titus Pelzl (ex.pelzl), Philipp Lehmann (ex.lehmann08)
 */
public class BodyHelper {

    private static String bodyString = "";

    /**
     * Returns the current body content as a string.
     *
     * @return The body content
     */
    public static String getBodyString() {
        return bodyString;
    }

    /***
     * Replaces the string in the internal buffer with a new raw string.
     * If UTF-8 encoding is required, use {@link #setBodyStringAndFormatUTF8(String)}.
     *
     * @param newBodyString The new body string to set
     */
    public static void setBodyString(String newBodyString) {
        bodyString = newBodyString;
    }

    private static MultiPartSpecification multiPartSpecification = null;

    /**
     * Returns the current multi-part specification.
     *
     * @return The multi-part specification
     */
    public static MultiPartSpecification getMultiPartSpecification() {
        return multiPartSpecification;
    }

    /***
     * Adds a multi-part specification to the internal body buffer.
     * If no multi-part specification is defined and the value is null,
     * no multi-part will be included in the request.
     *
     * @param multiPartSpecification The multi-part specification to set
     */
    public static void setMultiPartParameters(MultiPartSpecification multiPartSpecification) {
        RequestHelper.getRequestSpecification().multiPart(multiPartSpecification);
    }

    /**
     * Sets the body content as a UTF-8 encoded string.
     *
     * @param newBodyString The new body string to set, encoded in UTF-8
     */
    public static void setBodyStringAndFormatUTF8(String newBodyString) {
        bodyString = encodeStringInUTF8(newBodyString);
    }

    /**
     * Encodes the provided string in UTF-8.
     *
     * @param string The string to encode
     * @return The UTF-8 encoded string
     */
    private static String encodeStringInUTF8(String string) {
        // Encode the message in UTF-8
        byte[] bytes = string.getBytes(StandardCharsets.UTF_8);
        return new String(bytes, StandardCharsets.UTF_8);
    }

    /**
     * Resets the body content and multi-part specification to their default values.
     */
    public static void resetParameters() {
        bodyString = "";
        multiPartSpecification = null;
    }

}
