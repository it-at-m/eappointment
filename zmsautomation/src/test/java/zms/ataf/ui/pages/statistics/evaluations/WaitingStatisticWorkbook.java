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
 * Waiting-statistic workbook from {@code WaitingReport}.
 * Each sheet has hour rows under the waiting-time and way-time blocks.
 * Column C is the single filtered day.
 */
final class WaitingStatisticWorkbook {

    private WaitingStatisticWorkbook() {
    }

    static Map<String, Map<String, String>> hourValues(Path xlsx) throws IOException {
        try (ZipFile zip = new ZipFile(xlsx.toFile())) {
            String[] shared = sharedStrings(zip);
            Map<String, String> sheets = sheetPaths(zip);
            Map<String, Map<String, String>> values = new HashMap<>();
            for (Map.Entry<String, String> sheet : sheets.entrySet()) {
                values.put(sheet.getKey(), hourValues(zip, shared, sheet.getValue()));
            }
            return values;
        }
    }

    private static Map<String, String> hourValues(ZipFile zip, String[] shared, String sheetPath) throws IOException {
        ZipEntry sheet = zip.getEntry(sheetPath);
        if (sheet == null) {
            throw new IOException("Workbook is missing " + sheetPath);
        }
        Document document = parse(zip.getInputStream(sheet));
        Map<Integer, Map<String, String>> byRow = new HashMap<>();
        NodeList cells = elements(document, "c");
        for (int i = 0; i < cells.getLength(); i++) {
            Element cell = (Element) cells.item(i);
            String ref = cell.getAttribute("r");
            String column = ref.replaceAll("\\d", "");
            if (!column.equals("A") && !column.equals("C")) {
                continue;
            }
            byRow.computeIfAbsent(rowNumber(ref), ignored -> new HashMap<>()).put(column, cellText(cell, shared));
        }
        Map<String, String> labeled = new HashMap<>();
        String section = "";
        for (int row = 1; row < 500; row++) {
            Map<String, String> columns = byRow.get(row);
            if (columns == null) {
                continue;
            }
            String label = columns.getOrDefault("A", "");
            if ("Zeitabschnitte".equals(label)) {
                section = columns.getOrDefault("C", "");
                continue;
            }
            if (label.endsWith(" Uhr") && !section.isBlank()) {
                labeled.put(section + "|" + label, columns.getOrDefault("C", ""));
            }
        }
        return labeled;
    }

    private static Map<String, String> sheetPaths(ZipFile zip) throws IOException {
        ZipEntry workbook = zip.getEntry("xl/workbook.xml");
        ZipEntry rels = zip.getEntry("xl/_rels/workbook.xml.rels");
        if (workbook == null || rels == null) {
            throw new IOException("Workbook has no sheet list");
        }
        Document relDocument = parse(zip.getInputStream(rels));
        Map<String, String> targets = new HashMap<>();
        NodeList relations = elements(relDocument, "Relationship");
        for (int i = 0; i < relations.getLength(); i++) {
            Element relation = (Element) relations.item(i);
            targets.put(relation.getAttribute("Id"), "xl/" + relation.getAttribute("Target").replaceFirst("^/", ""));
        }
        Document book = parse(zip.getInputStream(workbook));
        Map<String, String> sheets = new HashMap<>();
        NodeList nodes = elements(book, "sheet");
        for (int i = 0; i < nodes.getLength(); i++) {
            Element sheet = (Element) nodes.item(i);
            String id = sheet.getAttribute("r:id");
            if (id.isBlank()) {
                id = sheet.getAttributeNS("http://schemas.openxmlformats.org/officeDocument/2006/relationships", "id");
            }
            sheets.put(sheet.getAttribute("name"), targets.get(id));
        }
        return sheets;
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
            int sharedIndex = Integer.parseInt(directValue(cell));
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
        if (node instanceof Element element) {
            NodeList parts = elements(element, "t");
            for (int i = 0; i < parts.getLength(); i++) {
                text.append(parts.item(i).getTextContent());
            }
            return text.toString();
        }
        return node.getTextContent();
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
            throw new IOException("Could not read the waiting statistic workbook", exception);
        }
    }
}
