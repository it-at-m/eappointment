import { ref } from "vue";

import { useDBSLoginWebcomponentPlugin } from "@/components/DBSLoginWebcomponentPlugin";
import AuthorizationEventDetails from "@/types/AuthorizationEventDetails";

class JwtParseError extends Error {
  constructor(message: string, options?: { cause?: unknown }) {
    super(message);
    this.name = "JwtParseError";
    if (options?.cause !== undefined) {
      this.cause = options.cause;
    }
  }
}

function getJwtPayloadSegment(token: string): string {
  if (!token?.trim()) {
    throw new JwtParseError("Invalid JWT: token must be a non-empty string");
  }

  const parts = token.split(".");
  if (parts.length !== 3) {
    throw new JwtParseError(
      `Invalid JWT: expected 3 dot-separated segments, got ${parts.length}`
    );
  }

  const payloadSegment = parts[1];
  if (!payloadSegment) {
    throw new JwtParseError("Invalid JWT: payload segment is missing");
  }

  return payloadSegment;
}

function base64UrlToBase64(base64Url: string): string {
  const base64 = base64Url.replace(/-/g, "+").replace(/_/g, "/");
  const paddingLength = (4 - (base64.length % 4)) % 4;
  return base64 + "=".repeat(paddingLength);
}

function decodeJwtPayloadSegment(payloadSegment: string): string {
  const base64 = base64UrlToBase64(payloadSegment);

  let decoded: string;
  try {
    decoded = window.atob(base64);
  } catch (error) {
    throw new JwtParseError("Invalid JWT: failed to base64-decode payload", {
      cause: error,
    });
  }

  try {
    return decodeURIComponent(
      decoded
        .split("")
        .map((character) => {
          return "%" + ("00" + character.charCodeAt(0).toString(16)).slice(-2);
        })
        .join("")
    );
  } catch (error) {
    throw new JwtParseError("Invalid JWT: failed to decode payload bytes", {
      cause: error,
    });
  }
}

function parseJwt(token: string): Record<string, unknown> {
  const payloadSegment = getJwtPayloadSegment(token);
  const jsonPayload = decodeJwtPayloadSegment(payloadSegment);

  try {
    const parsed: unknown = JSON.parse(jsonPayload);
    if (
      parsed === null ||
      typeof parsed !== "object" ||
      Array.isArray(parsed)
    ) {
      throw new JwtParseError("Invalid JWT: payload must be a JSON object");
    }
    return parsed as Record<string, unknown>;
  } catch (error) {
    if (error instanceof JwtParseError) {
      throw error;
    }
    throw new JwtParseError("Invalid JWT: payload is not valid JSON", {
      cause: error,
    });
  }
}

export function getTokenData(accessToken: string): {
  email?: string;
  given_name?: string;
  family_name?: string;
} {
  return parseJwt(accessToken) as {
    email?: string;
    given_name?: string;
    family_name?: string;
  };
}

export function useLogin() {
  const accessToken = ref<string | null>(null);
  const { loggedIn, loading } = useDBSLoginWebcomponentPlugin(
    (authEventDetails: AuthorizationEventDetails) => {
      accessToken.value = authEventDetails.accessToken;
    },
    () => {
      accessToken.value = null;
    }
  );
  return {
    isLoggedIn: loggedIn,
    isLoadingAuthentication: loading,
    accessToken,
  };
}
