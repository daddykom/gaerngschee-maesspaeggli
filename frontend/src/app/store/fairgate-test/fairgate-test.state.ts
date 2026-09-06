import { FairgateTestResult } from '../../shared/services/fairgate-test.service';

export interface FairgateTestState {
  result: FairgateTestResult | null;
  loading: boolean;
  errorCode: string | null;
}

export const initialState: FairgateTestState = {
  result: null,
  loading: false,
  errorCode: null,
};
