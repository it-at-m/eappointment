# CI/CD (GitHub to OpenShift)

eAppointment follows the City of Munich it@M pattern: **public GitHub** builds container images, an **internal GitLab** pipeline releases them to **OpenShift**. The diagram is from [`lhm_actions`](https://github.com/it-at-m/lhm_actions); `foo` in the picture stands in for this repository.

<img alt="it@M CI/CD big picture: public GitHub builds images to ghcr.io, Quay mirrors them internally, GitLab Helm-releases to OpenShift" src="../../img/ci_cd_github_big_picture_public.drawio.png" />

Source: [`ci_cd_github_big_picture_public.drawio.png`](https://github.com/it-at-m/lhm_actions/blob/main/docs/images/ci_cd_github_big_picture_public.drawio.png) in [it-at-m/lhm_actions](https://github.com/it-at-m/lhm_actions).

## How to read it for eAppointment

| In the diagram                                           | For eAppointment                                                                                                                                                                                                                             |
| -------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Public GitHub app repo `it-at-m/foo`                     | [it-at-m/eappointment](https://github.com/it-at-m/eappointment)                                                                                                                                                                              |
| GitHub Actions build / release → GHCR                    | Module images via [`php-build-images.yaml`](https://github.com/it-at-m/eappointment/blob/next/.github/workflows/php-build-images.yaml) and [PHP base images](../setup-and-development/php-base-images.md) to `ghcr.io/it-at-m/eappointment/` |
| Public Helm repo `it-at-m/helm-charts` + GitHub Pages    | **Not yet.** Charts still live in internal GitLab (`zms-deployment`). See below.                                                                                                                                                             |
| GitLab `foo-infrastructure` (`values.yaml`, deploy jobs) | Internal GitLab: environment values and `helm install` / `helm upgrade` jobs                                                                                                                                                                 |
| Quay.io                                                  | Internal registry mirrors GHCR; OpenShift pulls from Quay                                                                                                                                                                                    |
| OpenShift namespace `foo` (dev / test)                   | Helm release into the ZMS namespaces                                                                                                                                                                                                         |

## Helm charts stay internal for now

The right-hand public box in the diagram packages Helm charts to GitHub Pages ([it-at-m/helm-charts](https://github.com/it-at-m/helm-charts)). eAppointment still keeps charts in **internal GitLab** (`zms-deployment`). GitLab CI uses those charts plus per-environment `values.yaml` to release to OpenShift.

Publishing them to `it-at-m/helm-charts` is planned ([roadmap: `zmsdeployment` open source](../on-the-future/refarch-roadmap/product-oriented-refarch-roadmap.md)). There is no date yet.

## Related

- [PHP base images](../setup-and-development/php-base-images.md) — GHCR tags for `zmsbase` and module images
- [Zero-downtime deployments and migrations](../on-the-future/database-refactor/zero-downtime-deployments-and-migrations.md) — expand / provision / contract order inside a Helm release
- [Monitoring and status](./monitoring-and-status.md)
