import { inject } from '@angular/core';
import { ResolveFn } from '@angular/router';
import { Store } from '@ngrx/store';
import { filter, finalize, map, of, take } from 'rxjs';
import { NavigationProgressService } from '../../../../shared/services/navigation-progress.service';
import { AdminOverviewActions } from '../../../../store/admin-overview/admin-overview.actions';
import { selectAdminOverviewStatus } from '../../../../store/admin-overview/admin-overview.feature';

export const adminOverviewResolver: ResolveFn<boolean> = () => {
  const store = inject(Store);
  const progress = inject(NavigationProgressService);
  const status = store.selectSignal(selectAdminOverviewStatus)();

  if (status === 'loaded' || status === 'error') {
    return of(true);
  }

  progress.start();
  store.dispatch(AdminOverviewActions.load());

  return store.select(selectAdminOverviewStatus).pipe(
    filter((currentStatus) => currentStatus === 'loaded' || currentStatus === 'error'),
    take(1),
    map(() => true),
    finalize(() => progress.stop()),
  );
};
