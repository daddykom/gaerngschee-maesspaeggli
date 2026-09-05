import { createActionGroup, emptyProps, props } from '@ngrx/store';
import { FairgateTestResult } from '../../shared/services/fairgate-test.service';

export const FairgateTestActions = createActionGroup({
  source: 'Fairgate Test',
  events: {
    Test: emptyProps(),
    'Test Success': props<{ result: FairgateTestResult }>(),
    'Test Failure': props<{ errorCode: string }>(),
  },
});
