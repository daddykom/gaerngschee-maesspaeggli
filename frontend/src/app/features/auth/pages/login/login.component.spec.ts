import { provideRouter } from '@angular/router';
import { provideTranslateService } from '@ngx-translate/core';
import { MockStore, provideMockStore } from '@ngrx/store/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { Login } from './login.component';

describe('Login', () => {
  let component: Login;
  let fixture: ComponentFixture<Login>;
  let store: MockStore;

  const initialState = {
    auth: {
      token: null,
      group: null,
      loading: false,
      errorCode: null,
    },
  };

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Login],
      providers: [
        provideRouter([]),
        provideTranslateService(),
        provideMockStore({ initialState }),
      ],
    }).compileComponents();

    fixture = TestBed.createComponent(Login);
    component = fixture.componentInstance;
    store = TestBed.inject(MockStore);
    jest.spyOn(store, 'dispatch');
    fixture.detectChanges();
  });

  it('dispatches login credentials for a valid form', () => {
    component.loginModel.set({ email: 'user@example.com', password: 'secret' });

    component.onSubmit();

    expect(store.dispatch).toHaveBeenCalledWith({
      type: '[Auth] Login',
      email: 'user@example.com',
      password: 'secret',
    });
  });

  it('does not dispatch for an invalid form', () => {
    component.loginModel.set({ email: 'invalid-email', password: '' });

    component.onSubmit();

    expect(store.dispatch).not.toHaveBeenCalled();
    expect(component.loginForm.email().touched()).toBe(true);
    expect(component.loginForm.password().touched()).toBe(true);
  });

  it('shows validation errors after submitting an invalid form', () => {
    component.onSubmit();
    fixture.detectChanges();

    expect(fixture.nativeElement.querySelectorAll('[role="alert"]')).toHaveLength(2);
  });

  it('disables the submit button while loading', () => {
    store.setState({ auth: { ...initialState.auth, loading: true } });
    fixture.detectChanges();

    expect(fixture.nativeElement.querySelector('button[type="submit"]').disabled).toBe(true);
  });

  it('dispatches forgot password when the link is clicked', () => {
    const forgotPassword = fixture.nativeElement.querySelector('.forgot-password-link');

    forgotPassword.click();

    expect(store.dispatch).toHaveBeenCalledWith({
      type: '[Auth] Forgot Password',
    });
  });
});
