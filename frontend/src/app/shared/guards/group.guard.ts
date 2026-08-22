import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { Store } from '@ngrx/store';
import { UserGroup } from '../models/frontend-config.model';
import { selectAuthGroup } from '../../store/auth/auth.feature';

export const groupGuard = (allowedGroups: UserGroup[]): CanActivateFn => () => {
  const store = inject(Store);
  const router = inject(Router);
  const group = store.selectSignal(selectAuthGroup)();

  return group !== null && allowedGroups.includes(group)
    ? true
    : router.parseUrl('/login');
};
