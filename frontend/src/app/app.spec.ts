import { Component } from '@angular/core';
import { TestBed } from '@angular/core/testing';
import { Router } from '@angular/router';
import { RouterTestingModule } from '@angular/router/testing';
import { provideTranslateService } from '@ngx-translate/core';
import { MockStore, provideMockStore } from '@ngrx/store/testing';
import { App } from './app';
import { initialState as authInitialState } from './store/auth/auth.state';
import { AuthActions } from './store/auth/auth.actions';

@Component({ template: '' })
class TestPage {}

describe('App', () => {
  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [App, RouterTestingModule],
      providers: [provideTranslateService(), provideMockStore({
        initialState: {
          auth: authInitialState,
          notification: { current: null },
        },
      })],
    }).compileComponents();
  });

  it('should render router outlet', async () => {
    const fixture = TestBed.createComponent(App);
    await fixture.whenStable();
    const compiled = fixture.nativeElement as HTMLElement;
    expect(compiled.querySelector('router-outlet')).toBeTruthy();
  });

  it('shows the global notification in the info box', async () => {
    await TestBed.resetTestingModule()
      .configureTestingModule({
        imports: [App, RouterTestingModule],
        providers: [
          provideTranslateService(),
          provideMockStore({
            initialState: {
              notification: {
                current: {
                  variant: 'error',
                  titleKey: 'app.auth.loginErrorTitle',
                  messageKey: 'INVALID_CREDENTIALS',
                  params: {},
                  preserveOnRoutes: [],
                },
              },
              auth: authInitialState,
            },
          }),
        ],
      })
      .compileComponents();

    const fixture = TestBed.createComponent(App);
    await fixture.whenStable();

    expect(fixture.nativeElement.querySelector('[role="alert"]')).toBeTruthy();
    expect(fixture.nativeElement.textContent).toContain('INVALID_CREDENTIALS');
  });

  it('dispatches logout from the app shell', () => {
    const store = TestBed.inject(MockStore);
    const dispatch = jest.spyOn(store, 'dispatch');
    const fixture = TestBed.createComponent(App);

    fixture.componentInstance.logout();

    expect(dispatch).toHaveBeenCalledWith(AuthActions.logout());
  });

  it('renders route metadata and the admin menu on an admin route', async () => {
    await TestBed.resetTestingModule()
      .configureTestingModule({
        imports: [
          App,
          TestPage,
          RouterTestingModule.withRoutes([
            {
              path: 'admin/overview',
              component: TestPage,
              data: { pageTitle: 'app.admin.overview.title', pageHeaderLayout: 'wide' },
            },
          ]),
        ],
        providers: [
          provideTranslateService(),
          provideMockStore({ initialState: { auth: authInitialState, notification: { current: null } } }),
        ],
      })
      .compileComponents();

    const router = TestBed.inject(Router);
    const fixture = TestBed.createComponent(App);
    await router.navigateByUrl('/admin/overview');
    fixture.detectChanges();

    expect(fixture.componentInstance.isAdminRoute()).toBe(true);
    expect(fixture.componentInstance.pageTitleKey()).toBe('app.admin.overview.title');
    expect(fixture.componentInstance.pageHeaderLayout()).toBe('wide');
    expect(fixture.nativeElement.querySelector('.admin-menu-button')).toBeTruthy();
    expect(fixture.nativeElement.querySelector('mat-divider')).toBeTruthy();
  });
});
