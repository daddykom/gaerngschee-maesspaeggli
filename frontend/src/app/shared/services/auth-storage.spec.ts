import {
  clearPersistedAuthState,
  loadPersistedAuthState,
  persistAuthState,
} from './auth-storage';

describe('auth storage', () => {
  beforeEach(() => {
    localStorage.clear();
  });

  afterEach(() => {
    jest.restoreAllMocks();
  });

  it('restores a valid persisted client session', () => {
    const state = {
      token: tokenWithExpiry(Math.floor(Date.now() / 1000) + 3600),
      userId: 'client-123',
      group: 'client' as const,
      fairgateUserExists: true,
      childrenCount: 2,
      adultsCount: 2,
      salutation: 'Hallo',
    };

    persistAuthState(state);

    expect(loadPersistedAuthState()).toEqual(state);
  });

  it('clears an expired session', () => {
    persistAuthState({
      token: tokenWithExpiry(Math.floor(Date.now() / 1000) - 1),
      userId: 'client-123',
      group: 'client',
      fairgateUserExists: true,
      childrenCount: 2,
      adultsCount: 2,
      salutation: 'Hallo',
    });

    expect(loadPersistedAuthState()).toEqual({});
    expect(localStorage.getItem('gaerngschee.auth')).toBeNull();
  });

  it('clears the persisted session on logout', () => {
    persistAuthState({
      token: tokenWithExpiry(Math.floor(Date.now() / 1000) + 3600),
      userId: 'client-123',
      group: 'client',
      fairgateUserExists: null,
      childrenCount: null,
      adultsCount: null,
      salutation: null,
    });

    clearPersistedAuthState();

    expect(loadPersistedAuthState()).toEqual({});
  });

  it.each([
    'not-json',
    JSON.stringify({ token: 'token' }),
    JSON.stringify({ token: 'token', userId: 'user-1', group: 'owner' }),
    JSON.stringify({ token: 'token', userId: 'user-1', group: 'client', childrenCount: '2' }),
  ])('clears invalid persisted state: %s', (raw) => {
    localStorage.setItem('gaerngschee.auth', raw);

    expect(loadPersistedAuthState()).toEqual({});
    expect(localStorage.getItem('gaerngschee.auth')).toBeNull();
  });

  it.each([
    'token-without-payload',
    'header.invalid-json.signature',
    'header.' + btoa(JSON.stringify({})).replace(/=/g, '') + '.signature',
  ])('clears a session with an invalid JWT: %s', (token) => {
    localStorage.setItem('gaerngschee.auth', JSON.stringify({
      token,
      userId: 'client-123',
      group: 'client',
      fairgateUserExists: null,
      childrenCount: null,
      adultsCount: null,
      salutation: null,
    }));

    expect(loadPersistedAuthState()).toEqual({});
  });

  it('returns an empty state when reading storage fails', () => {
    jest.spyOn(Storage.prototype, 'getItem').mockImplementation(() => {
      throw new Error('storage unavailable');
    });

    expect(loadPersistedAuthState()).toEqual({});
  });

  it('ignores errors while persisting state', () => {
    jest.spyOn(Storage.prototype, 'setItem').mockImplementation(() => {
      throw new Error('storage unavailable');
    });

    expect(() => persistAuthState({
      token: tokenWithExpiry(Math.floor(Date.now() / 1000) + 3600),
      userId: 'client-123',
      group: 'client',
      fairgateUserExists: null,
      childrenCount: null,
      adultsCount: null,
      salutation: null,
    })).not.toThrow();
  });

  it('ignores errors while clearing state', () => {
    jest.spyOn(Storage.prototype, 'removeItem').mockImplementation(() => {
      throw new Error('storage unavailable');
    });

    expect(() => clearPersistedAuthState()).not.toThrow();
  });
});

function tokenWithExpiry(exp: number): string {
  const encode = (value: object) => btoa(JSON.stringify(value))
    .replace(/=/g, '')
    .replace(/\+/g, '-')
    .replace(/\//g, '_');

  return `${encode({ alg: 'none', typ: 'JWT' })}.${encode({ exp })}.signature`;
}
