import { describe, expect, it } from "vitest";

import { getTokenData } from "@/utils/auth";

function createJwt(payload: Record<string, unknown>): string {
  const json = JSON.stringify(payload);
  const base64 = btoa(json);
  const base64Url = base64
    .replace(/\+/g, "-")
    .replace(/\//g, "_")
    .replace(/=+$/, "");
  return `eyJhbGciOiJIUzI1NiJ9.${base64Url}.signature`;
}

describe("getTokenData / parseJwt", () => {
  it("parses a valid JWT payload", () => {
    const token = createJwt({
      email: "user@example.com",
      given_name: "Max",
      family_name: "Mustermann",
    });

    expect(getTokenData(token)).toEqual({
      email: "user@example.com",
      given_name: "Max",
      family_name: "Mustermann",
    });
  });

  it("handles base64url payloads that require padding", () => {
    const token = createJwt({ email: "a@b.co" });

    expect(getTokenData(token)).toEqual({
      email: "a@b.co",
      given_name: undefined,
      family_name: undefined,
    });
  });

  it("rejects tokens without three segments", () => {
    expect(() => getTokenData("only-one-segment")).toThrow(
      "Invalid JWT: expected 3 dot-separated segments, got 1"
    );
    expect(() => getTokenData("a.b")).toThrow(
      "Invalid JWT: expected 3 dot-separated segments, got 2"
    );
  });

  it("rejects tokens with an empty header segment", () => {
    const payload = btoa("{}")
      .replace(/\+/g, "-")
      .replace(/\//g, "_")
      .replace(/=+$/, "");
    expect(() => getTokenData(`.${payload}.signature`)).toThrow(
      "Invalid JWT: header segment is missing"
    );
  });

  it("rejects tokens with an empty payload segment", () => {
    expect(() => getTokenData("header..signature")).toThrow(
      "Invalid JWT: payload segment is missing"
    );
  });

  it("rejects incorrectly typed supported claims", () => {
    expect(() => getTokenData(createJwt({ email: { nested: true } }))).toThrow(
      "Invalid JWT: email must be a string"
    );
    expect(() => getTokenData(createJwt({ given_name: 42 }))).toThrow(
      "Invalid JWT: given_name must be a string"
    );
    expect(() => getTokenData(createJwt({ family_name: ["a"] }))).toThrow(
      "Invalid JWT: family_name must be a string"
    );
  });

  it("rejects empty tokens", () => {
    expect(() => getTokenData("")).toThrow(
      "Invalid JWT: token must be a non-empty string"
    );
  });

  it("rejects invalid base64 payload", () => {
    expect(() => getTokenData("a.!!!.c")).toThrow(
      "Invalid JWT: failed to base64-decode payload"
    );
  });

  it("rejects non-JSON payload", () => {
    const notJson = btoa("not-json").replace(/\+/g, "-").replace(/\//g, "_");
    expect(() => getTokenData(`a.${notJson}.c`)).toThrow(
      "Invalid JWT: payload is not valid JSON"
    );
  });

  it("rejects JSON payload that is not an object", () => {
    const arrayPayload = btoa("[1,2,3]")
      .replace(/\+/g, "-")
      .replace(/\//g, "_");
    expect(() => getTokenData(`a.${arrayPayload}.c`)).toThrow(
      "Invalid JWT: payload must be a JSON object"
    );
  });
});
