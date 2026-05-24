package zms.ataf.ui.base.services.pojo;

import java.util.List;
import java.util.Map;

public class Service {
    private String id;
    private String name;
    private int maxQuantity;
    private Map<String, List<String>> combinable;

    public String getId() {
        return id;
    }

    public String getName() {
        return name;
    }

    public int getMaxQuantity() {
        return maxQuantity;
    }

    public Map<String, List<String>> getCombinable() {
        return combinable;
    }

}
