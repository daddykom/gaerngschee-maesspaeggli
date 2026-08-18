import { AnmeldungActions } from './anmeldung.actions';
import { anmeldungReducer } from './anmeldung.feature';
import { initialState } from './anmeldung.state';

describe('anmeldungReducer', () => {
  it('sets loading when an email is submitted', () => {
    const state = anmeldungReducer(initialState, AnmeldungActions.submit({ email: 'person@example.com' }));

    expect(state).toEqual({ loading: true, sent: null, sendError: false });
  });

  it('stores a successful response', () => {
    const state = anmeldungReducer(
      { loading: true, sent: null, sendError: false },
      AnmeldungActions.submitSuccess({ sent: true }),
    );

    expect(state).toEqual({ loading: false, sent: true, sendError: false });
  });

  it('stores a failed response', () => {
    const state = anmeldungReducer(
      { loading: true, sent: null, sendError: false },
      AnmeldungActions.submitFailure(),
    );

    expect(state).toEqual({ loading: false, sent: false, sendError: true });
  });
});
