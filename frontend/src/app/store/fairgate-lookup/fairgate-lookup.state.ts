import { FairgateTestResult } from '../../shared/services/fairgate-test.service';

export interface FairgateLookupState {
  result: FairgateTestResult | null;
  loading: boolean;
  errorCode: string | null;
}

export const initialState: FairgateLookupState = {
  result: null,
  loading: false,
  errorCode: null,
};
