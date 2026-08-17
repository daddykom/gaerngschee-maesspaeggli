export type UserGroup = 'admin' | 'user' | 'client';

export interface FrontendConfig {
  id: string;
  variableName: string;
  accessGroup: UserGroup[];
  updateGroup: UserGroup[];
  label: string;
  createdAt: Date;
  updatedAt: Date;
}
