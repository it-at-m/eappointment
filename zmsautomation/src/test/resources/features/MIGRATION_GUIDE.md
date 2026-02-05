# Migration Guide: Converting JUnit Tests to Cucumber Features

This guide provides examples and best practices for converting existing JUnit tests to Cucumber feature files as part of Phase 6 migration.

## Example Conversions

### Simple GET Request Test

**Before (JUnit):**
```java
@Test
void statusEndpointShouldBeOk() {
    Response response = givenRequest()
        .when()
            .get("/status/")
        .then()
            .statusCode(200)
            .extract()
            .response();
    // Assertions...
}
```

**After (Cucumber):**
```gherkin
Scenario: GET /status/ returns 200 and JSON body
  When I request the status endpoint
  Then the response status code should be 200
  And the response should contain status information
```

### Test with Parameters

**Before (JUnit):**
```java
@Test
void testAvailabilityForScope(int scopeId) {
    Response response = givenRequest()
        .pathParam("scopeId", scopeId)
        .when()
            .get("/scope/{scopeId}/availability/");
    // Assertions...
}
```

**After (Cucumber):**
```gherkin
Scenario: Get availability for a valid scope
  When I request available appointments for scope 141
  Then the response status code should be 200
  And the response should contain available slots
```

### Data-Driven Testing

**Before (JUnit):**
```java
@ParameterizedTest
@ValueSource(ints = {141, 142, 143})
void testMultipleScopes(int scopeId) {
    // Test logic...
}
```

**After (Cucumber):**
```gherkin
Scenario Outline: Test multiple scopes
  When I request available appointments for scope <scopeId>
  Then the response status code should be 200
  
  Examples:
    | scopeId |
    | 141     |
    | 142     |
    | 143     |
```

## Step Definition Patterns

### Common Given Steps
- `Given the ZMS API is available`
- `Given the Citizen API is available`
- `Given I have a valid appointment confirmation number`
- `Given I have selected a valid service and location`

### Common When Steps
- `When I request the status endpoint`
- `When I request available appointments for scope <id>`
- `When I submit a booking request with valid data`
- `When I submit a cancellation request`

### Common Then Steps
- `Then the response status code should be <code>`
- `Then the response should contain <content>`
- `Then I should receive a confirmation number`
- `Then the response should contain an error message`

## Best Practices

1. **Use Background** for common setup steps
2. **Tag scenarios** appropriately (`@smoke`, `@zmsapi`, `@citizenapi`)
3. **Use Scenario Outlines** for data-driven testing
4. **Keep steps reusable** across multiple scenarios
5. **Use descriptive step names** that read like natural language
6. **Group related scenarios** in the same feature file

## File Organization

```
features/
├── zmsapi/
│   ├── status.feature
│   ├── availability.feature
│   ├── appointments.feature
│   ├── scopes.feature
│   └── error-handling.feature
└── zmscitizenapi/
    ├── offices-and-services.feature
    ├── booking.feature
    └── cancellation.feature
```

## Migration Checklist

- [ ] Identify test class to convert
- [ ] Extract test scenarios
- [ ] Create feature file with appropriate tags
- [ ] Write Gherkin scenarios
- [ ] Check if step definitions exist, create if needed
- [ ] Test locally with `mvn test -Pataf`
- [ ] Verify both JUnit and Cucumber tests pass
- [ ] Update documentation if needed
