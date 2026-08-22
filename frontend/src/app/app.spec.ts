import { TestBed } from '@angular/core/testing';
import { RouterTestingModule } from '@angular/router/testing';
import { provideTranslateService } from '@ngx-translate/core';
import { provideMockStore } from '@ngrx/store/testing';
import { App } from './app';
import { initialState as authInitialState } from './store/auth/auth.state';

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
});
