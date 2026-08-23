export type UserGroup = 'admin' | 'user' | 'client';

export interface FrontendConfig {
  id: string;
  variableName: string;
  value: string | string[] | null;
  description: string;
  accessGroup: UserGroup[];
  updateGroup: UserGroup[];
  label: string;
  canUpdate: boolean;
  createdAt: Date;
  updatedAt: Date;
}
