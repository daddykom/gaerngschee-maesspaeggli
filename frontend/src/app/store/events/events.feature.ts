import { createFeature, createReducer, on } from '@ngrx/store';
import { EventActions } from './events.actions';
import { EventsState, initialState } from './events.state';

export const eventsFeature = createFeature({
  name: 'events',
  reducer: createReducer<EventsState>(
    initialState,
    on(EventActions.received, (state, { key, value }) => ({
      values: {
        ...state.values,
        [key]: Math.max(state.values[key] ?? 0, value),
      },
    })),
  ),
});

export const {
  name: eventsFeatureName,
  reducer: eventsReducer,
  selectValues: selectEventValues,
} = eventsFeature;
