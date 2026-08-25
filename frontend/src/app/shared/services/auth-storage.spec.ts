import {
  clearPersistedAuthState,
  loadPersistedAuthState,
  persistAuthState,
} from './auth-storage';

describe('auth storage', () => {
  beforeEach(() => {
    localStorage.clear();
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
});

function tokenWithExpiry(exp: number): string {
  const encode = (value: object) => btoa(JSON.stringify(value))
    .replace(/=/g, '')
    .replace(/\+/g, '-')
    .replace(/\//g, '_');

  return `${encode({ alg: 'none', typ: 'JWT' })}.${encode({ exp })}.signature`;
}
