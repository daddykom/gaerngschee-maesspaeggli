import { AnmeldungActions } from './anmeldung.actions';
import { anmeldungReducer } from './anmeldung.feature';
import { initialState } from './anmeldung.state';

describe('anmeldungReducer', () => {
  it('sets loading when an email is submitted', () => {
    const state = anmeldungReducer(initialState, AnmeldungActions.submit({ email: 'person@example.com' }));

    expect(state).toEqual({ loading: true, sent: null, error: null });
  });

  it('stores a successful response', () => {
    const state = anmeldungReducer(
      { loading: true, sent: null, error: null },
      AnmeldungActions.submitSuccess({ sent: true }),
    );

    expect(state).toEqual({ loading: false, sent: true, error: null });
  });

  it('stores a failed response', () => {
    const state = anmeldungReducer(
      { loading: true, sent: null, error: null },
      AnmeldungActions.submitFailure({ error: 'Request failed' }),
    );

    expect(state).toEqual({ loading: false, sent: false, error: 'Request failed' });
  });
});
