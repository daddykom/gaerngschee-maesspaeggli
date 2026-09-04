import { createActionGroup, emptyProps, props } from '@ngrx/store';
import { AdminOverview } from '../../shared/models/admin-overview.model';

export const AdminOverviewActions = createActionGroup({
  source: 'Admin Overview',
  events: {
    Load: emptyProps(),
    'Load Success': props<{ overview: AdminOverview }>(),
    'Load Failure': props<{ errorCode: string }>(),
  },
});
