export interface StartState {
  loading: boolean;
  sent: boolean | null;
}

export const initialState: StartState = {
  loading: false,
  sent: null,
};
