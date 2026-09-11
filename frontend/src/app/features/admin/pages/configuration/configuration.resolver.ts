import { inject } from '@angular/core';
import { ResolveFn } from '@angular/router';
import { Store } from '@ngrx/store';
import { filter, finalize, map, take } from 'rxjs';
import { NavigationProgressService } from '../../../../shared/services/navigation-progress.service';
import { FrontendConfigActions } from '../../../../store/frontend-config/frontend-config.actions';
import { selectFrontendConfigLoading } from '../../../../store/frontend-config/frontend-config.feature';

export const configurationResolver: ResolveFn<boolean> = () => {
  const store = inject(Store);
  const progress = inject(NavigationProgressService);

  progress.start();
  store.dispatch(FrontendConfigActions.load());

  return store.select(selectFrontendConfigLoading).pipe(
    filter((loading) => !loading),
    take(1),
    map(() => true),
    finalize(() => progress.stop()),
  );
};
