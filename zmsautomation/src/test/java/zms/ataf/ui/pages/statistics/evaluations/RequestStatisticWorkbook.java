package zms.ataf.ui.pages.statistics.evaluations;

import java.io.IOException;
import java.io.InputStream;
import java.nio.file.Path;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.zip.ZipEntry;
import java.util.zip.ZipFile;

import javax.xml.XMLConstants;
import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;

import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

/**
 * Reads the Dienstleistungsstatistik workbook written by PhpSpreadsheet.
 * Column A is the service name, B the duration, C the count.
 */
final class RequestStatisticWorkbook {

    private RequestStatisticWorkbook() {
    }

    static Map<String, String[]> rows(Path xlsx) throws IOException {
        try (ZipFile zip = new ZipFile(xlsx.toFile())) {
            String[] shared = sharedStrings(zip);
            ZipEntry sheet = zip.getEntry("xl/worksheets/sheet1.xml");
            if (sheet == null) {
                throw new IOException("Workbook has no sheet1: " + xlsx);
            }
            Document document = parse(zip.getInputStream(sheet));
            Map<Integer, Map<String, String>> byRow = new HashMap<>();
            NodeList cells = elements(document, "c");
            for (int i = 0; i < cells.getLength(); i++) {
                Element cell = (Element) cells.item(i);
                String ref = cell.getAttribute("r");
                String column = ref.replaceAll("\\d", "");
                int row = rowNumber(ref);
                if (!column.equals("A") && !column.equals("B") && !column.equals("C")) {
                    continue;
                }
                byRow.computeIfAbsent(row, ignored -> new HashMap<>()).put(column, cellText(cell, shared));
            }
            Map<String, String[]> labeled = new HashMap<>();
            for (Map<String, String> columns : byRow.values()) {
                String label = columns.get("A");
                if (label != null && !label.isBlank()) {
                    labeled.put(label.trim(), new String[] {columns.getOrDefault("B", ""), columns.getOrDefault("C", "")});
                }
            }
            return labeled;
        }
    }

    /**
     * Cells of the column header that starts with Dienstleistung.
     * A date stored as an Excel serial is returned as dd.MM.yyyy.
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
                int row = rowNumber(ref);
                byRow.computeIfAbsent(row, ignored -> new HashMap<>())
                        .put(columnIndex(ref), display(cellText(cell, shared)));
            }
            for (Map<Integer, String> columns : byRow.values()) {
                if ("Dienstleistung".equals(columns.get(0))) {
                    int last = columns.keySet().stream().mapToInt(Integer::intValue).max().orElse(0);
                    List<String> headers = new ArrayList<>();
                    for (int column = 0; column <= last; column++) {
                        headers.add(columns.getOrDefault(column, ""));
                    }
                    return headers;
                }
            }
            throw new IOException("Workbook has no Dienstleistung header: " + xlsx);
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

    private static String display(String raw) {
        if (!raw.matches("\\d+(\\.\\d+)?")) {
            return raw;
        }
        double serial = Double.parseDouble(raw);
        long days = Math.round(serial);
        if (days < 20000 || days > 80000) {
            return raw;
        }
        return LocalDate.of(1899, 12, 30).plusDays(days).format(DateTimeFormatter.ofPattern("dd.MM.yyyy"));
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

    private static int rowNumber(String ref) throws IOException {
        try {
            return Integer.parseInt(ref.replaceAll("\\D", ""));
        } catch (NumberFormatException exception) {
            throw new IOException("Workbook cell has no row number: " + ref, exception);
        }
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

    private static String textOf(Node node) {
        StringBuilder text = new StringBuilder();
        NodeList texts = node instanceof Element element ? elements(element, "t") : node.getChildNodes();
        if (node instanceof Element element) {
            NodeList parts = elements(element, "t");
            for (int i = 0; i < parts.getLength(); i++) {
                text.append(parts.item(i).getTextContent());
            }
            return text.toString();
        }
        for (int i = 0; i < texts.getLength(); i++) {
            text.append(texts.item(i).getTextContent());
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
            throw new IOException("Could not read the statistic workbook", exception);
        }
    }
}
