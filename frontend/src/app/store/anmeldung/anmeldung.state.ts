export interface AnmeldungState {
  loading: boolean;
  sent: boolean | null;
  error: string | null;
}

export const initialState: AnmeldungState = {
  loading: false,
  sent: null,
  error: null,
};
