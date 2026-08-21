export interface StartState {
  loading: boolean;
  sent: boolean | null;
  sendError: boolean;
}

export const initialState: StartState = {
  loading: false,
  sent: null,
  sendError: false,
};
