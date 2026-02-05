package helpers.zmscitizenapi;

import java.util.ArrayList;
import java.util.List;

import dto.zmscitizenapi.Office;
import dto.zmscitizenapi.OfficeServiceRelation;
import dto.zmscitizenapi.Service;
import dto.zmscitizenapi.collections.OfficesAndServicesResponse;

/**
 * Builder for OfficesAndServicesResponse test data.
 */
public class OfficesAndServicesResponseBuilder {
    private List<Office> offices = new ArrayList<>();
    private List<Service> services = new ArrayList<>();
    private List<OfficeServiceRelation> relations = new ArrayList<>();

    public OfficesAndServicesResponseBuilder withOffice(Integer id, String name) {
        Office office = new Office();
        office.setId(id);
        office.setName(name);
        this.offices.add(office);
        return this;
    }

    public OfficesAndServicesResponseBuilder withOffice(Office office) {
        this.offices.add(office);
        return this;
    }

    public OfficesAndServicesResponseBuilder withService(Integer id, String name) {
        Service service = new Service();
        service.setId(id);
        service.setName(name);
        this.services.add(service);
        return this;
    }

    public OfficesAndServicesResponseBuilder withService(Service service) {
        this.services.add(service);
        return this;
    }

    public OfficesAndServicesResponseBuilder withRelation(Integer officeId, Integer serviceId, Integer slots) {
        OfficeServiceRelation relation = new OfficeServiceRelation();
        relation.setOfficeId(officeId);
        relation.setServiceId(serviceId);
        relation.setSlots(slots);
        this.relations.add(relation);
        return this;
    }

    public OfficesAndServicesResponseBuilder withRelation(OfficeServiceRelation relation) {
        this.relations.add(relation);
        return this;
    }

    public OfficesAndServicesResponse build() {
        OfficesAndServicesResponse response = new OfficesAndServicesResponse();
        response.setOffices(offices);
        response.setServices(services);
        response.setRelations(relations);
        return response;
    }
}
