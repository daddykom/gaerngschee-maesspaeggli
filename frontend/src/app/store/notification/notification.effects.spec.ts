import { NavigationEnd, NavigationStart, Router } from '@angular/router';
import { TestBed } from '@angular/core/testing';
import { firstValueFrom, Subject } from 'rxjs';
import { notificationRouteEffect } from './notification.effects';
import { NotificationActions } from './notification.actions';

describe('notificationRouteEffect', () => {
  let routerEvents$: Subject<unknown>;

  beforeEach(() => {
    routerEvents$ = new Subject<unknown>();
    TestBed.configureTestingModule({
      providers: [{ provide: Router, useValue: { events: routerEvents$ } }],
    });
  });

  it('emits a navigation completed action for NavigationEnd', async () => {
    const result = firstValueFrom(TestBed.runInInjectionContext(() => notificationRouteEffect()));

    routerEvents$.next(new NavigationEnd(1, '/login', '/admin/overview?from=login'));

    await expect(result).resolves.toEqual(
      NotificationActions.navigationCompleted({ url: '/admin/overview?from=login' }),
    );
  });

  it('ignores router events that are not NavigationEnd', () => {
    const effect$ = TestBed.runInInjectionContext(() => notificationRouteEffect());
    const next = jest.fn();
    const subscription = effect$.subscribe(next);

    routerEvents$.next(new NavigationStart(1, '/login'));

    expect(next).not.toHaveBeenCalled();
    subscription.unsubscribe();
  });
});
