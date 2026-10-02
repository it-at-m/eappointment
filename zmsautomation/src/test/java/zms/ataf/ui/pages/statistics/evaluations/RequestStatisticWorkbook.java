package zms.ataf.ui.pages.statistics.evaluations;

import java.io.IOException;
import java.io.InputStream;
import java.nio.file.Path;
import java.util.HashMap;
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
                int row = Integer.parseInt(ref.replaceAll("\\D", ""));
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

    private static String cellText(Element cell, String[] shared) {
        String type = cell.getAttribute("t");
        if ("s".equals(type)) {
            String index = directValue(cell);
            int sharedIndex = Integer.parseInt(index);
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
