export type UserGroup = 'admin' | 'user' | 'client';

export interface FrontendConfig {
  id: string;
  variableName: string;
  value: string;
  description: string;
  accessGroup: UserGroup[];
  updateGroup: UserGroup[];
  label: string;
  createdAt: Date;
  updatedAt: Date;
}
