# Ruppertstraße booking variants (ZMSKVR-1046)

Citizen booking at Bürgerbüro Ruppertstraße uses three different models. They look similar in the UI (often one Ort checkbox) but differ in **which OfficeIDs are queried** and **how free slots are chosen**.

| Variant                     | OfficeIDs         | Ort UI  | Calendar `officeIds`      | Slot pick                                                                                                |
| --------------------------- | ----------------- | ------- | ------------------------- | -------------------------------------------------------------------------------------------------------- |
| Multi-scope (one office)    | `10489`           | One Ort | That OfficeID only        | Backend RR across **scopes** of the same provider                                                        |
| Shared booking (Ausbildung) | `10489` + `10503` | One Ort | **Both** peer OfficeIDs   | Backend RR across scopes of **all peers** when timestamps overlap (`sharedBookingOfficeIds`)             |
| Pass exclusive/mixed        | `10489` + `10502` | One Ort | **One** survivor OfficeID | FE picks exclusive (`10502`) vs mixed (`10489`) via `allowDisabledServicesMix` — **not** pooled capacity |

Configuration lives in [`zmsdldb/.../Munich.php`](https://github.com/it-at-m/eappointment/blob/main/zmsdldb/src/Zmsdldb/Transformers/Munich.php):

- `DONT_SHOW_LOCATION_BY_SERVICES` → `disabledByServices` (Pass services hidden on Haupt `10489`)
- `LOCATIONS_ALLOW_DISABLED_MIX` → `allowDisabledServicesMix` (`[10489, 10502]`)
- `LOCATIONS_SHARED_BOOKING` → `sharedBookingOfficeIds` (`[10489, 10503]`)

Local test data: Flyway `V19` (Ruppertstraße opening hours), `V24` (Ausbildung scope `372` / OfficeID `10503`), SADB overwrite for `10503`.

---

## Overview

```mermaid
flowchart TB
  subgraph multiScope [MultiScope_oneOffice]
    MS_Ort[Ort_10489]
    MS_Cal[available-calendar_officeIds_10489]
    MS_RR[ProcessStatusFree_RR_by_providerId]
    MS_Ort --> MS_Cal --> MS_RR
  end

  subgraph sharedBooking [SharedBooking_twoOffices]
    SB_Ort[Ort_display_10489]
    SB_Cal[available-calendar_officeIds_10489_and_10503]
    SB_RR[ProcessStatusFree_RR_by_sharedBookingOfficeIds]
    SB_UI[FE_merge_peer_buckets_for_display]
    SB_Ort --> SB_Cal --> SB_RR --> SB_UI
  end

  subgraph passMix [PassExclusiveMixed]
    PM_Ort[Ort_one_survivor]
    PM_Pick[FE_exclusive_10502_or_mixed_10489]
    PM_Cal[available-calendar_one_officeId]
    PM_Ort --> PM_Pick --> PM_Cal
  end
```

---

## 1. Multi-scope at one office (`10489`)

Haupt office `10489` has multiple scopes (e.g. WB04 `160`, WB03 `181`). The citizen still selects **one** Ort. The calendar requests that OfficeID. Free processes from several scopes that share the same wall-clock time are reduced to **one** process by backend round-robin keyed by **`providerId + timestamp`**.

```mermaid
flowchart LR
  Ort[Bürgerbüro_Ruppertstraße_10489]
  Cal[GET_available-calendar_officeIds_10489]
  S160[scope_160]
  S181[scope_181]
  RR[deduplicateWithRoundRobin_key_providerId]
  Slot[one_process_per_timestamp]

  Ort --> Cal
  Cal --> S160
  Cal --> S181
  S160 --> RR
  S181 --> RR
  RR --> Slot
```

**Fairness:** RR is per **scope** (sorted by scope id). More scopes under the same office → that office’s scopes appear more often in the rotation.

---

## 2. Shared booking / shared calendar (`10489` + `10503`)

Ausbildung OfficeID `10503` (local test Standort `372`) overlaps Haupt services (e.g. Wohnsitzanmeldung). Product goal: **one Ort**, **pooled capacity**.

1. DLDB sets `sharedBookingOfficeIds: [10489, 10503]` on both providers.
2. Bürgeransicht collapses peers to one checkbox (display office = lowest id, `10489`).
3. Checking that Ort expands **both** IDs into `available-calendar`.
4. Backend RR keys by sorted peer ids (`10489,10503`) + timestamp when the flag is present on provider data — overlapping times keep **one** winner office/scope.
5. Non-overlapping times still appear under different `offices[]` buckets; the UI merges those buckets into one timeslot list for display. Booking uses the officeId that owns the chosen timestamp.

```mermaid
flowchart TB
  DLDB["Munich_LOCATIONS_SHARED_BOOKING_10489_10503"]
  FE_Collapse[FE_one_Ort_checkbox_display_10489]
  FE_Expand[FE_officeIds_10489_10503]
  API[available-calendar]
  RR["ProcessStatusFree_groupKey_10489_10503"]
  JSON[offices_buckets_by_winner_officeId]
  FE_Merge[FE_aggregate_peer_sections_for_UI]

  DLDB --> FE_Collapse
  FE_Collapse --> FE_Expand --> API --> RR --> JSON --> FE_Merge
```

**Not the same as Pass mix:** both OfficeIDs stay in the calendar request; capacity is pooled, not routed exclusively.

---

## 3. Pass calendar exclusive vs mixed (`10489` + `10502`)

Passkalender `10502` and Haupt `10489` share the Ort label but use **`allowDisabledServicesMix`**. Pass-only services are on `disabledByServices` for `10489`. The UI keeps mix peers, then picks **one** survivor:

- **Exclusive** (only Pass-disabled services selected) → `10502`
- **Mixed** (anything else / harmless combo) → `10489`

Calendar and reserve use **that one** OfficeID. The other office does not contribute slots for that booking.

```mermaid
flowchart TB
  Services[Selected_services]
  Mix[allowDisabledServicesMix_10489_10502]
  Decision{All_selected_in_10489_disabledByServices?}
  Exclusive[Survivor_10502_Passkalender]
  Mixed[Survivor_10489_Haupt]
  Cal[available-calendar_single_officeId]

  Services --> Mix --> Decision
  Decision -->|yes_exclusive| Exclusive --> Cal
  Decision -->|no_mixed| Mixed --> Cal
```

```mermaid
flowchart LR
  subgraph exclusive [Pass_only]
    E1[services_e.g._Reisepass]
    E2[officeIds_10502_only]
    E1 --> E2
  end

  subgraph mixed [Pass_plus_other_or_non_Pass]
    M1[services_mixed_or_harmless]
    M2[officeIds_10489_only]
    M1 --> M2
  end
```

---

## Comparison: what the citizen sees vs what is queried

```mermaid
flowchart TB
  subgraph ui [Bürgeransicht_Ort]
    OneBox[Often_one_checkbox_Ruppertstraße]
  end

  subgraph behind [Behind_the_checkbox]
    A[MultiScope_query_10489_RR_scopes]
    B[SharedBooking_query_10489_and_10503_RR_peers]
    C[PassMix_query_either_10489_or_10502]
  end

  OneBox --> A
  OneBox --> B
  OneBox --> C
```

| Question                                | Multi-scope               | Shared booking                 | Pass mix                      |
| --------------------------------------- | ------------------------- | ------------------------------ | ----------------------------- |
| Same wall-clock on two scopes/offices?  | RR keeps one scope        | RR keeps one peer office/scope | N/A — only one office queried |
| Both OfficeIDs in `available-calendar`? | No                        | Yes                            | No                            |
| FE merges `offices[]` for display?      | Usually one office bucket | Yes (peer buckets)             | No need                       |

---

## Related code

- DLDB: `zmsdldb/src/Zmsdldb/Transformers/Munich.php`
- Backend RR: `zmsbackend/.../ProcessStatusFree.php` (`resolveRoundRobinGroupKey`, `deduplicateWithRoundRobin`)
- Bürgeransicht Ort / expand / merge: `zmscitizenview/.../AppointmentSelection.vue`
- Mapping: `zmscitizenapi/.../MapperService.php`
- Contract: [DLDB Interface Documentation](./dldb-interface-documentation.md)
