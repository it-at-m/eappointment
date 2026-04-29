# Current Cucumber Tests in zmsautomation

This page is the entry point for documenting currently active Cucumber scenarios in `zmsautomation`.

## What to track here

- feature files currently executed in CI/local runs
- scenario groups by business flow
- known flaky scenarios and mitigation notes
- ownership and update cadence

## Suggested structure

Use this structure when extending the page:

```md
## <feature area>
- Feature: <path/to/file.feature>
- Covered flows: <short list>
- Tags: <@smoke @regression ...>
- Notes: <risks, dependencies, data preconditions>
```

Add concrete scenario inventory in follow-up updates once the current feature list is finalized.
