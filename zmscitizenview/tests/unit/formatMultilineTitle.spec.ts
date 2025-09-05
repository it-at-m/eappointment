import { describe, it, expect } from "vitest";
import { formatMultilineTitle } from "../../src/utils/formatMultilineTitle";

const singleAppointment = {
  processId: "1111",
    timestamp: 1753360200,
  authKey: "abcd",
  familyName: "Max Mustermann",
  email: "max.mustermann@testmail.com",
  officeId: "1111",
  scope: {
  id: "11",
    provider: {
    id: "1111",
      source: "source",
  },
  shortName: "1",
  },
  subRequestCounts: [],
    serviceId: "1111",
  serviceName: "Service 1",
  serviceCount: 1,
}

const subServiceAppointment = {
  processId: "2222",
    timestamp: 1754305200,
  authKey: "efgh",
  familyName: "Max Mustermann",
  email: "max.mustermann@testmail.com",
  officeId: "2222",
  scope: {
  id: "22",
    provider: {
    id: "2222",
      source: "source",
  },
  shortName: "2",
  },
  subRequestCounts: [
    {
      id: "subid_2222",
      name: "Service 3",
      count: 2,
    },
  ],
    serviceId: "id_2222",
  serviceName: "Service 2",
  serviceCount: 2,
}

const twoSubServiceAppointment = {
  processId: "3333",
  timestamp: 1754305200,
  authKey: "ijkl",
  familyName: "Max Mustermann",
  email: "max.mustermann@testmail.com",
  officeId: "333",
  scope: {
    id: "33",
    provider: {
      id: "3333",
      source: "source",
    },
    shortName: "3",
  },
  subRequestCounts: [
    {
      id: "subid_333",
      name: "Service 5",
      count: 2,
    },
    {
      id: "subid_3333",
      name: "Service 6",
      count: 3,
    },
  ],
  serviceId: "id_2222",
  serviceName: "Service 4",
  serviceCount: 1,
}

describe("calculateEstimatedDuration", () => {
  it("returns title of single appointment", () => {
    expect(formatMultilineTitle(singleAppointment)).toBe(singleAppointment.serviceCount + "x " + singleAppointment.serviceName);
  });

  it("returns title of appointment with one subservice", () => {
    const title = subServiceAppointment.serviceCount
      + "x "
      + subServiceAppointment.serviceName
      + "\n"
      + subServiceAppointment.subRequestCounts[0].count
      + "x "
      + subServiceAppointment.subRequestCounts[0].name;
    expect(formatMultilineTitle(subServiceAppointment)).toBe(title);
  });

  it("returns title of appointment with two subservice", () => {
    const title = twoSubServiceAppointment.serviceCount
      + "x "
      + twoSubServiceAppointment.serviceName
      + "\n"
      + twoSubServiceAppointment.subRequestCounts[0].count
      + "x "
      + twoSubServiceAppointment.subRequestCounts[0].name
      + "\n"
      + twoSubServiceAppointment.subRequestCounts[1].count
      + "x "
      + twoSubServiceAppointment.subRequestCounts[1].name;
    expect(formatMultilineTitle(twoSubServiceAppointment)).toBe(title);
  });
});
