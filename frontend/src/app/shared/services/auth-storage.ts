import { UserGroup } from '../models/frontend-config.model';

const STORAGE_KEY = 'gaerngschee.auth';

export interface PersistedAuthState {
  token: string;
  userId: string;
  group: UserGroup;
  fairgateUserExists: boolean | null;
  childrenCount: number | null;
  adultsCount: number | null;
  salutation: string | null;
}

export function loadPersistedAuthState(): Partial<PersistedAuthState> {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) {
      return {};
    }

    const state = JSON.parse(raw) as Partial<PersistedAuthState>;
    if (!isValidPersistedState(state) || isExpired(state.token)) {
      clearPersistedAuthState();
      return {};
    }

    return state;
  } catch {
    clearPersistedAuthState();
    return {};
  }
}

export function persistAuthState(state: PersistedAuthState): void {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
  } catch {
    // Storage can be unavailable in private browsing contexts.
  }
}

export function clearPersistedAuthState(): void {
  try {
    localStorage.removeItem(STORAGE_KEY);
  } catch {
    // Storage can be unavailable in private browsing contexts.
  }
}

function isValidPersistedState(state: Partial<PersistedAuthState>): state is PersistedAuthState {
  return typeof state.token === 'string'
    && typeof state.userId === 'string'
    && ['admin', 'user', 'client'].includes(state.group ?? '')
    && (state.fairgateUserExists === null || typeof state.fairgateUserExists === 'boolean')
    && (state.childrenCount === null || typeof state.childrenCount === 'number')
    && (state.adultsCount === null || typeof state.adultsCount === 'number')
    && (state.salutation === null || typeof state.salutation === 'string');
}

function isExpired(token: string): boolean {
  const payload = token.split('.')[1];
  if (!payload) {
    return true;
  }

  try {
    const decoded = JSON.parse(atob(payload.replace(/-/g, '+').replace(/_/g, '/'))) as { exp?: number };
    return typeof decoded.exp !== 'number' || decoded.exp <= Math.floor(Date.now() / 1000);
  } catch {
    return true;
  }
}
