package zms.ataf.ui.pages.statistics.evaluations;

import java.io.IOException;
import java.io.InputStream;
import java.nio.file.Path;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.regex.Pattern;
import java.util.zip.ZipEntry;
import java.util.zip.ZipFile;

import javax.xml.XMLConstants;
import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;

import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.NodeList;

/**
 * Reads day dates and column headers from a Kundenstatistik workbook ({@code ClientReport}).
 * Day rows store the calendar date in column B as {@code dd.MM.yy}.
 * Header row places {@code Datum} in column B, then customer totals in C and D.
 */
final class ClientStatisticWorkbook {

    private static final Pattern DAY_DATE = Pattern.compile("\\d{2}\\.\\d{2}\\.\\d{2}");

    private ClientStatisticWorkbook() {
    }

    static List<String> dayDates(Path xlsx) throws IOException {
        try (ZipFile zip = new ZipFile(xlsx.toFile())) {
            String[] shared = sharedStrings(zip);
            ZipEntry sheet = zip.getEntry("xl/worksheets/sheet1.xml");
            if (sheet == null) {
                throw new IOException("Workbook has no sheet1: " + xlsx);
            }
            Document document = parse(zip.getInputStream(sheet));
            List<String> dates = new ArrayList<>();
            NodeList cells = elements(document, "c");
            for (int i = 0; i < cells.getLength(); i++) {
                Element cell = (Element) cells.item(i);
                String ref = cell.getAttribute("r");
                if (!ref.replaceAll("\\d", "").equals("B")) {
                    continue;
                }
                String text = cellText(cell, shared).trim();
                if (DAY_DATE.matcher(text).matches()) {
                    dates.add(text);
                }
            }
            return dates;
        }
    }

    /**
     * Header cells of the row that contains {@code Datum} (column B), left to right.
     * Empty leading cells are kept so Excel column letters match indices (A=0, B=1, …).
     */
    static List<String> columnHeaders(Path xlsx) throws IOException {
        try (ZipFile zip = new ZipFile(xlsx.toFile())) {
            String[] shared = sharedStrings(zip);
            ZipEntry sheet = zip.getEntry("xl/worksheets/sheet1.xml");
            if (sheet == null) {
                throw new IOException("Workbook has no sheet1: " + xlsx);
            }
            Document document = parse(zip.getInputStream(sheet));
            Map<Integer, Map<Integer, String>> byRow = new HashMap<>();
            NodeList cells = elements(document, "c");
            for (int i = 0; i < cells.getLength(); i++) {
                Element cell = (Element) cells.item(i);
                String ref = cell.getAttribute("r");
                byRow.computeIfAbsent(rowNumber(ref), ignored -> new HashMap<>())
                        .put(columnIndex(ref), cellText(cell, shared).trim());
            }
            for (Map<Integer, String> columns : byRow.values()) {
                if ("Datum".equals(columns.get(1))) {
                    int last = columns.keySet().stream().mapToInt(Integer::intValue).max().orElse(0);
                    List<String> headers = new ArrayList<>();
                    for (int column = 0; column <= last; column++) {
                        headers.add(columns.getOrDefault(column, ""));
                    }
                    return headers;
                }
            }
            throw new IOException("Workbook has no Datum header row: " + xlsx);
        }
    }

    private static int columnIndex(String ref) {
        int index = 0;
        for (int i = 0; i < ref.length(); i++) {
            char letter = ref.charAt(i);
            if (letter < 'A' || letter > 'Z') {
                break;
            }
            index = index * 26 + (letter - 'A' + 1);
        }
        return index - 1;
    }

    private static int rowNumber(String ref) throws IOException {
        try {
            return Integer.parseInt(ref.replaceAll("\\D", ""));
        } catch (NumberFormatException exception) {
            throw new IOException("Workbook cell has no row number: " + ref, exception);
        }
    }

    private static String[] sharedStrings(ZipFile zip) throws IOException {
        ZipEntry entry = zip.getEntry("xl/sharedStrings.xml");
        if (entry == null) {
            return new String[0];
        }
        Document document = parse(zip.getInputStream(entry));
        NodeList items = elements(document, "si");
        String[] shared = new String[items.getLength()];
        for (int i = 0; i < items.getLength(); i++) {
            shared[i] = textOf(items.item(i)).trim();
        }
        return shared;
    }

    private static String cellText(Element cell, String[] shared) throws IOException {
        String type = cell.getAttribute("t");
        if ("s".equals(type)) {
            String index = directValue(cell);
            int sharedIndex;
            try {
                sharedIndex = Integer.parseInt(index);
            } catch (NumberFormatException exception) {
                throw new IOException("Workbook shared string index is not a number: " + index, exception);
            }
            return sharedIndex >= 0 && sharedIndex < shared.length ? shared[sharedIndex] : "";
        }
        if ("inlineStr".equals(type)) {
            return textOf(cell).trim();
        }
        return directValue(cell);
    }

    private static String directValue(Element cell) {
        NodeList values = elements(cell, "v");
        return values.getLength() == 0 ? "" : values.item(0).getTextContent().trim();
    }

    private static String textOf(org.w3c.dom.Node node) {
        StringBuilder text = new StringBuilder();
        NodeList parts = node instanceof Element element ? elements(element, "t") : node.getChildNodes();
        if (node instanceof Element) {
            for (int i = 0; i < parts.getLength(); i++) {
                text.append(parts.item(i).getTextContent());
            }
            return text.toString();
        }
        for (int i = 0; i < parts.getLength(); i++) {
            text.append(parts.item(i).getTextContent());
        }
        return text.toString();
    }

    private static NodeList elements(Document document, String name) {
        return elements(document.getDocumentElement(), name);
    }

    private static NodeList elements(Element element, String name) {
        NodeList namespaced = element.getElementsByTagNameNS("*", name);
        if (namespaced.getLength() > 0) {
            return namespaced;
        }
        return element.getElementsByTagName(name);
    }

    private static Document parse(InputStream input) throws IOException {
        try {
            DocumentBuilderFactory factory = DocumentBuilderFactory.newInstance();
            factory.setNamespaceAware(true);
            factory.setFeature(XMLConstants.FEATURE_SECURE_PROCESSING, true);
            factory.setFeature("http://apache.org/xml/features/disallow-doctype-decl", true);
            DocumentBuilder builder = factory.newDocumentBuilder();
            return builder.parse(input);
        } catch (IOException exception) {
            throw exception;
        } catch (Exception exception) {
            throw new IOException("Could not read the citizen statistic workbook", exception);
        }
    }
}
