export interface AnmeldungState {
  loading: boolean;
  sent: boolean | null;
  sendError: boolean;
}

export const initialState: AnmeldungState = {
  loading: false,
  sent: null,
  sendError: false,
};
