package zms.ataf.ui.base.services;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.Objects;
import java.util.Optional;
import java.util.stream.Collectors;

import org.testng.Assert;

import com.google.gson.Gson;
import com.google.gson.JsonSyntaxException;

import ataf.core.clients.HttpClient;
import ataf.core.logging.ScenarioLogManager;
import zms.ataf.ui.base.services.pojo.Office;
import zms.ataf.ui.base.services.pojo.OfficesAndServices;
import zms.ataf.ui.base.services.pojo.Relation;
import zms.ataf.ui.base.services.pojo.Service;

/**
 * Author: Mohamad Daaeboul
 */
public class ServicesUtil {

    private static final String TARGET_URL = "https://zms-test.muenchen.de/buergeransicht/api/backend/offices-and-services/";
    private static final Gson GSON = new Gson();
    private OfficesAndServices officesAndServices;

    public ServicesUtil() {
        try (HttpClient httpClient = new HttpClient()) {
            String jsonResult = httpClient.executeHttpGetRequest(TARGET_URL, HttpClient.AuthenticationMethod.None);
            officesAndServices = mapToOfficesAndServices(jsonResult);
            Assert.assertNotNull(officesAndServices, "Failed to fetch and map offices and services data.");
        } catch (Exception e) {
            ScenarioLogManager.getLogger().error("Error fetching or mapping data: {}", e.getMessage(), e);
            Assert.fail("Exception occurred during data fetching or mapping.");
        }
    }

    private String fetchJsonResult() {
        try (HttpClient httpClient = new HttpClient()) {
            String jsonResult = httpClient.executeHttpGetRequest(TARGET_URL, HttpClient.AuthenticationMethod.None);
            Assert.assertNotNull(jsonResult, "Fetched JSON result should not be null.");
            return jsonResult;
        } catch (Exception e) {
            ScenarioLogManager.getLogger().error("Error fetching JSON result: {}", e.getMessage(), e);
            return null;
        }
    }

    public OfficesAndServices mapToOfficesAndServices(String jsonResult) {
        try {
            return GSON.fromJson(jsonResult, OfficesAndServices.class);
        } catch (JsonSyntaxException e) {
            ScenarioLogManager.getLogger().error("Error mapping JSON to OfficesAndServices: {}", e.getMessage(), e);
            return null;
        }
    }

    public Service getServiceById(String serviceId) {
        if (officesAndServices != null) {
            Optional<Service> serviceOptional = officesAndServices.getServices().stream()
                    .filter(service -> service.getId().equals(serviceId))
                    .findFirst();
            return serviceOptional.orElse(null);
        }
        return null;
    }

    public Service getServiceByName(String serviceName) {
        if (officesAndServices != null) {
            Optional<Service> serviceOptional = officesAndServices.getServices().stream()
                    .filter(service -> service.getName().equals(serviceName))
                    .findFirst();
            return serviceOptional.orElse(null);
        }
        return null;
    }

    public Office getOfficeById(String officeId) {
        if (officesAndServices != null) {
            Optional<Office> officeOptional = officesAndServices.getOffices().stream()
                    .filter(office -> office.getId().equals(officeId))
                    .findFirst();
            return officeOptional.orElse(null);
        }
        return null;
    }

    public Office getOfficeByName(String officeName) {
        if (officesAndServices != null) {
            Optional<Office> officeOptional = officesAndServices.getOffices().stream()
                    .filter(office -> office.getName().equals(officeName))
                    .findFirst();
            return officeOptional.orElse(null);
        }
        return null;
    }

    public List<Office> getOfficesByServiceId(String serviceId) {
        List<Office> result = new ArrayList<>();
        if (officesAndServices != null) {
            for (Relation relation : officesAndServices.getRelations()) {
                if (relation.getServiceId().equals(serviceId)) {
                    String officeId = relation.getOfficeId();
                    result.addAll(officesAndServices.getOffices().stream()
                            .filter(office -> office.getId().equals(officeId))
                            .toList());
                }
            }
        }
        return result;
    }

    public List<Service> getServicesByOfficeId(String officeId) {
        List<Service> result = new ArrayList<>();
        if (officesAndServices != null) {
            for (Relation relation : officesAndServices.getRelations()) {
                if (relation.getOfficeId().equals(officeId)) {
                    String serviceId = relation.getServiceId();
                    officesAndServices.getServices().stream()
                            .filter(service -> service.getId().equals(serviceId))
                            .findFirst()
                            .ifPresent(result::add);
                }
            }
        }
        return result;
    }

    public Map<Service, List<String>> getCombinableServices(String serviceId) {
        Map<Service, List<String>> combinableServices = new HashMap<>();

        if (officesAndServices != null) {
            Service service = getServiceById(serviceId);
            if (service != null && service.getCombinable() != null) {
                Map<String, List<String>> combinableMap = service.getCombinable();
                combinableServices = combinableMap.entrySet().stream()
                        .filter(entry -> !entry.getValue().isEmpty())
                        .collect(Collectors.toMap(
                                entry -> getServiceById(entry.getKey()),
                                Map.Entry::getValue
                        ));
            }
        }
        return combinableServices;
    }

    public List<Service> getCombinableServices(String serviceId, String officeId) {
        List<Service> combinableServices = new ArrayList<>();

        if (officesAndServices != null) {
            Optional<Service> serviceOptional = officesAndServices.getServices().stream()
                    .filter(service -> service.getId().equals(serviceId))
                    .findFirst();

            if (serviceOptional.isPresent()) {
                Service service = serviceOptional.get();
                Map<String, List<String>> combinableMap = service.getCombinable();

                if (combinableMap != null) {
                    combinableServices = combinableMap.keySet().stream()
                            .map(this::getServiceById)
                            .filter(Objects::nonNull)
                            .filter(combService -> combinableMap.get(combService.getId()).contains(officeId))
                            .collect(Collectors.toList());
                }
            }
        }
        return combinableServices;
    }

    public Map<Service, Integer> getSlotsForServiceAndCombinable(String serviceId, String officeId) {
        Map<Service, Integer> serviceSlots = new HashMap<>();

        serviceSlots.put(getServiceById(serviceId), getSlotsForService(serviceId, officeId));

        List<Service> combinableServices = getCombinableServices(serviceId, officeId);

        for (Service combinableService : combinableServices) {
            serviceSlots.put(combinableService, getSlotsForService(combinableService.getId(), officeId));
        }

        return serviceSlots;
    }

    private int getSlotsForService(String serviceId, String officeId) {
        if (officesAndServices != null) {
            return officesAndServices.getRelations().stream()
                    .filter(r -> r.getServiceId().equals(serviceId) && r.getOfficeId().equals(officeId))
                    .findFirst()
                    .map(Relation::getSlots)
                    .orElse(0);
        }
        return 0;
    }

}