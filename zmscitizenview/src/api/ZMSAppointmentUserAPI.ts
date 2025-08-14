import { AppointmentDTO } from "@/api/models/AppointmentDTO";
import { getAPIBaseURL } from "@/utils/Constants";

export function getAppointments(
  userId: string,
  baseUrl?: string
): Promise<AppointmentDTO[]> {
  console.debug(getAPIBaseURL(baseUrl) + userId);
  return new Promise((resolve) => setTimeout(() => resolve(DUMMYDATA), 1000));
}

export function getAppointmentDetails(
  processId: string,
  baseUrl?: string
): Promise<AppointmentDTO> {
  console.debug(getAPIBaseURL(baseUrl) + processId);
  return new Promise((resolve) =>
    setTimeout(() => resolve(DUMMYDATA[0]), 1000)
  );
}

const DUMMYDATA: AppointmentDTO[] = [
  {
    processId: "1111",
    timestamp: 1753360200,
    authKey: "abcd",
    familyName: "Max Mustermann",
    email: "max.mustermann@testmail.com",
    officeId: "10489",
    scope: {
      id: "36",
      provider: {
        id: "10489",
        source: "source",
      },
      shortName: "1",
    },
    subRequestCounts: [
      {
        id: "1063453",
        name: "Reisepass",
        count: 1,
      },
    ],
    serviceId: "1063441",
    serviceName: "Personalausweis",
    serviceCount: 2,
  },
  {
    processId: "2222",
    timestamp: 1754305200,
    authKey: "efgh",
    familyName: "Max Mustermann",
    email: "max.mustermann@testmail.com",
    officeId: "10546",
    scope: {
      id: "36",
      provider: {
        id: "10546",
        source: "source",
      },
      shortName: "2",
    },
    subRequestCounts: [],
    serviceId: "id_2222",
    serviceName: "Gewerbe anmelden",
    serviceCount: 1,
  },
];
