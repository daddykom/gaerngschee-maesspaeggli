import { createActionGroup, emptyProps, props } from '@ngrx/store';
import { FrontendConfig } from '../../shared/models/frontend-config.model';

export const FrontendConfigActions = createActionGroup({
  source: 'Frontend Configuration',
  events: {
    Load: emptyProps(),
    'Load Success': props<{ configs: FrontendConfig[] }>(),
    'Load Failure': props<{ errorCode: string }>(),
    Save: props<{ configs: Array<{ id: string; value: string | string[] }> }>(),
    'Save Success': props<{ configs: FrontendConfig[] }>(),
    'Save Failure': props<{ errorCode: string }>(),
  },
});
