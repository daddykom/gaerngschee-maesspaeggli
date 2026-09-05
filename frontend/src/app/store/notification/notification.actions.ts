import { createActionGroup, emptyProps, props } from '@ngrx/store';
import { InfoBoxVariant } from '../../shared/components/info-box/info-box';

export const NotificationActions = createActionGroup({
  source: 'Notification',
  events: {
    Show: props<{
      variant: InfoBoxVariant;
      titleKey: string;
      messageKey: string;
      params?: Record<string, string>;
      preserveOnRoutes?: string[];
    }>(),
    'Navigation Completed': props<{ url: string }>(),
    Clear: emptyProps(),
  },
});
