export class ApiException extends Error {
  constructor(
    public readonly message: string,
    public readonly code: string = 'HTTP_ERROR',
    public readonly details?: Record<string, string[]> | null
  ) {
    super(message);
    this.name = 'ApiException';
    Object.setPrototypeOf(this, ApiException.prototype);
  }
}
