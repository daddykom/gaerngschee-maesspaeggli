import { StartActions } from './start.actions';
import { startReducer } from './start.feature';
import { initialState } from './start.state';

describe('startReducer', () => {
  it('sets loading when an email is submitted', () => {
    const state = startReducer(initialState, StartActions.submit({ email: 'person@example.com' }));

    expect(state).toEqual({ loading: true, sent: null, sendError: false });
  });

  it('stores a successful response', () => {
    const state = startReducer(
      { loading: true, sent: null, sendError: false },
      StartActions.submitSuccess({ sent: true }),
    );

    expect(state).toEqual({ loading: false, sent: true, sendError: false });
  });

  it('stores a failed response', () => {
    const state = startReducer(
      { loading: true, sent: null, sendError: false },
      StartActions.submitFailure(),
    );

    expect(state).toEqual({ loading: false, sent: false, sendError: true });
  });
});
