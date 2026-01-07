package ataf.rest.helper;

import io.cucumber.datatable.DataTable;
import io.restassured.http.Header;
import io.restassured.http.Headers;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

/**
 * This class provides utility methods for managing HTTP headers in the context of API requests.
 *
 * @author Titus Pelzl (ex.pelzl), Philipp Lehmann (ex.lehmann08)
 */
public class HeaderHelper {

    private HeaderHelper() {
        // Private constructor to prevent instantiation
    }

    private static Map<String, String> headerList = new HashMap<>();

    /***
     * Adds a header to the internal list of headers. If a header with the same name already exists, its
     * value is overwritten.
     *
     * @param headerName The name of the header to be added
     * @param headerValue The value of the header to be added
     * @return The updated list of headers including the newly added header
     */
    public static Map<String, String> addHeader(String headerName, String headerValue) {
        findHeaderAndOverwrite(headerName, headerValue);
        return headerList;
    }

    /***
     * Similar to {@link #addHeader(String, String)}, but allows adding multiple headers at once.
     *
     * @param headers A map containing header names and their values
     * @return The updated list of headers including the newly added headers
     */
    public static Map<String, String> addMultipleHeaders(Map<String, String> headers) {
        for (Map.Entry<String, String> entry : headers.entrySet()) {
            addHeader(entry.getKey(), entry.getValue());
        }
        return headerList;
    }

    /***
     * Adds headers from a {@link DataTable}. Useful for defining headers through Gherkin tables.
     *
     * @param headerInformation A DataTable containing header information
     */
    public void headerInformationSetTable(DataTable headerInformation) {
        Map<String, String> dt = headerInformation.asMap();
        // Remove "Header" so it is not searched
        if (dt.get("Attribut") != null) {
            dt.remove("Attribut", "Wert");
        }
        addMultipleHeaders(dt);
    }

    /**
     * Searches for a header by name, and if found, replaces its value. If not found, adds it as a new
     * header.
     *
     * @param headerName The name of the header to find and update
     * @param headerValue The new value of the header
     */
    private static void findHeaderAndOverwrite(String headerName, String headerValue) {
        if (headerList.containsKey(headerName)) {
            // Header already exists -> replace it
            headerList.remove(headerName);
            headerList.put(headerName, headerValue);
        } else {
            // Header not found -> add new header
            headerList.put(headerName, headerValue);
        }
    }

    /**
     * Returns the current list of headers as a map.
     *
     * @return A map of header names and values
     */
    public static Map<String, String> getHeaderList() {
        return headerList;
    }

    /**
     * Returns the current headers as a list of {@link Header} objects.
     *
     * @return A list of headers
     */
    public static List<Header> getHeadersAsList() {
        List<Header> headerList = new ArrayList<>();
        for (Map.Entry<String, String> entry : HeaderHelper.headerList.entrySet()) {
            Header header = new Header(entry.getKey(), entry.getValue());
            headerList.add(header);
        }
        return headerList;
    }

    /**
     * Returns the current headers as a {@link Headers} object.
     *
     * @return A Headers object
     */
    public static Headers getHeaders() {
        return new Headers(getHeadersAsList());
    }

    /**
     * Resets the list of headers to an empty state.
     */
    public static void resetParameters() {
        headerList = new HashMap<>();
    }
}
