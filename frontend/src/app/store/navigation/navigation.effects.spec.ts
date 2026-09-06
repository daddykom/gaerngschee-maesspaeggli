import { Location } from '@angular/common';
import { TestBed } from '@angular/core/testing';
import { provideMockActions } from '@ngrx/effects/testing';
import { Action } from '@ngrx/store';
import { Subject } from 'rxjs';
import { NavigationActions } from './navigation.actions';
import { navigationEffect } from './navigation.effects';
import { Router } from '@angular/router';

describe('navigationEffect', () => {
  let actions$: Subject<Action>;
  let router: { navigateByUrl: jest.Mock };
  let location: { back: jest.Mock };

  beforeEach(() => {
    actions$ = new Subject<Action>();
    router = { navigateByUrl: jest.fn() };
    location = { back: jest.fn() };
    TestBed.configureTestingModule({
      providers: [
        provideMockActions(() => actions$),
        { provide: Router, useValue: router },
        { provide: Location, useValue: location },
      ],
    });
  });

  it('navigates to the requested URL', () => {
    const effect$ = TestBed.runInInjectionContext(() => navigationEffect());
    const subscription = effect$.subscribe();

    actions$.next(NavigationActions.navigate({ target: '/admin/overview' }));

    expect(router.navigateByUrl).toHaveBeenCalledWith('/admin/overview');
    expect(location.back).not.toHaveBeenCalled();
    subscription.unsubscribe();
  });

  it('goes back in browser history for the back target', () => {
    const effect$ = TestBed.runInInjectionContext(() => navigationEffect());
    const subscription = effect$.subscribe();

    actions$.next(NavigationActions.navigate({ target: 'back' }));

    expect(location.back).toHaveBeenCalledTimes(1);
    expect(router.navigateByUrl).not.toHaveBeenCalled();
    subscription.unsubscribe();
  });
});
