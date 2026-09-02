import { ActivatedRoute } from '@angular/router';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideTranslateService } from '@ngx-translate/core';
import { MockStore, provideMockStore } from '@ngrx/store/testing';
import { AuthActions } from '../../../../store/auth/auth.actions';
import { initialState } from '../../../../store/auth/auth.state';
import { ClientLoginComponent } from './client-login.component';

describe('ClientLoginComponent', () => {
  let fixture: ComponentFixture<ClientLoginComponent>;
  let store: MockStore;

  function create(token: string | null): void {
    TestBed.configureTestingModule({
      imports: [ClientLoginComponent],
      providers: [
        provideTranslateService(),
        provideMockStore({ initialState: { auth: initialState } }),
        { provide: ActivatedRoute, useValue: { snapshot: { queryParamMap: { get: () => token } } } },
      ],
    });
    fixture = TestBed.createComponent(ClientLoginComponent);
    store = TestBed.inject(MockStore);
    jest.spyOn(store, 'dispatch');
    fixture.detectChanges();
  }

  it('dispatches registration login when a token is present', () => {
    create('registration-token');

    expect(store.dispatch).toHaveBeenCalledWith(
      AuthActions.registrationLogin({ token: 'registration-token' }),
    );
  });

  it('dispatches an invalid-token failure when no token is present', () => {
    create(null);

    expect(store.dispatch).toHaveBeenCalledWith(
      AuthActions.registrationLoginFailure({ errorCode: 'INVALID_REGISTRATION_TOKEN' }),
    );
  });

  it('renders the loading state without local error content', () => {
    create(null);
    store.setState({ auth: { ...initialState, registrationLoginLoading: true } });
    fixture.detectChanges();
    expect(fixture.nativeElement.textContent).toContain('app.clientLogin.loading');

    store.setState({ auth: { ...initialState, registrationLoginErrorCode: 'INVALID_REGISTRATION_TOKEN' } });
    fixture.detectChanges();
    expect(fixture.nativeElement.textContent).not.toContain('app.clientLogin.errorTitle');
  });
});
