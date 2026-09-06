import { InfoBoxVariant } from '../../shared/components/info-box/info-box';

export interface Notification {
  variant: InfoBoxVariant;
  titleKey: string;
  messageKey: string;
  params: Record<string, string>;
  preserveOnRoutes: string[];
}

export interface NotificationState {
  current: Notification | null;
}

export const initialState: NotificationState = {
  current: null,
};
