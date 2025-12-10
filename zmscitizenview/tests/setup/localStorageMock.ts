/**
 * Ensures tests always have a browser-like localStorage even when
 * NODE_OPTIONS or custom loaders replace jsdom's default implementation.
 */
class LocalStorageMock implements Storage {
  private store = new Map<string, string>();

  clear(): void {
    this.store.clear();
  }

  getItem(key: string): string | null {
    return this.store.has(key) ? this.store.get(key)! : null;
  }

  key(index: number): string | null {
    return Array.from(this.store.keys())[index] ?? null;
  }

  get length(): number {
    return this.store.size;
  }

  removeItem(key: string): void {
    this.store.delete(key);
  }

  setItem(key: string, value: string): void {
    this.store.set(key, String(value));
  }
}

const isValidLocalStorage =
  typeof globalThis.localStorage === "object" &&
  typeof globalThis.localStorage?.getItem === "function";

if (!isValidLocalStorage) {
  const mock = new LocalStorageMock();
  globalThis.localStorage = mock;
}


