import { inject } from '@angular/core';
import { ResolveFn } from '@angular/router';
import { Store } from '@ngrx/store';
import { filter, finalize, map, of, take } from 'rxjs';
import { NavigationProgressService } from '../../shared/services/navigation-progress.service';
import { FrontendConfigActions } from '../../store/frontend-config/frontend-config.actions';
import { selectFrontendPublicConfigStatus } from '../../store/frontend-config/frontend-config.feature';

export const publicConfigurationResolver: ResolveFn<boolean> = () => {
  const store = inject(Store);
  const progress = inject(NavigationProgressService);
  const status = store.selectSignal(selectFrontendPublicConfigStatus)();

  if (status === 'loaded' || status === 'error') {
    return of(true);
  }

  progress.start();
  store.dispatch(FrontendConfigActions.loadPublic());

  return store.select(selectFrontendPublicConfigStatus).pipe(
    filter((currentStatus) => currentStatus === 'loaded' || currentStatus === 'error'),
    take(1),
    map(() => true),
    finalize(() => progress.stop()),
  );
};
