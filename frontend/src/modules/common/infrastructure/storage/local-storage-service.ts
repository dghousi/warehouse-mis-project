export class LocalStorageService {
  clearAll(): void {
    if (typeof globalThis === 'undefined' || !('localStorage' in globalThis)) {
      return;
    }

    const cookies = document.cookie.split(';');
    for (const cookie of cookies) {
      const [name] = cookie.split('=');
      const trimmedName = name.trim();

      document.cookie = `${trimmedName}=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;`;
      document.cookie = `${trimmedName}=; path=/; domain=${globalThis.location.hostname}; expires=Thu, 01 Jan 1970 00:00:00 UTC;`;
    }

    globalThis.localStorage.clear();
    globalThis.sessionStorage.clear();
  }
}
