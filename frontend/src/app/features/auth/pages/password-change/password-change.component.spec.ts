import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { provideMockStore } from '@ngrx/store/testing';
import { provideTranslateService } from '@ngx-translate/core';
import { initialState as authInitialState } from '../../../../store/auth/auth.state';
import { PasswordChange } from './password-change.component';

describe('PasswordChange', () => {
  let component: PasswordChange;
  let fixture: ComponentFixture<PasswordChange>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [PasswordChange],
      providers: [
        provideRouter([]),
        provideTranslateService(),
        provideMockStore({ initialState: { auth: authInitialState } }),
      ],
    }).compileComponents();

    fixture = TestBed.createComponent(PasswordChange);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('creates a form with two password fields', () => {
    expect(component.passwordChangeForm.newPassword).toBeDefined();
    expect(component.passwordChangeForm.passwordConfirmation).toBeDefined();
    expect(fixture.nativeElement.querySelectorAll('input[type="password"]')).toHaveLength(2);
  });

  it('requires both password fields', () => {
    component.onSubmit();
    fixture.detectChanges();

    expect(component.passwordChangeForm().valid()).toBe(false);
    expect(component.passwordChangeForm.newPassword().touched()).toBe(true);
    expect(component.passwordChangeForm.passwordConfirmation().touched()).toBe(true);
    expect(fixture.nativeElement.querySelectorAll('.control-error')).toHaveLength(2);
  });

  it('rejects different passwords', () => {
    component.passwordChangeModel.set({
      newPassword: 'new-secret',
      passwordConfirmation: 'different-secret',
    });

    expect(component.passwordChangeForm().errors()).toEqual(
      expect.arrayContaining([expect.objectContaining({ kind: 'passwordsDoNotMatch' })]),
    );
    expect(component.passwordChangeForm().valid()).toBe(false);
  });

  it('accepts matching passwords', () => {
    component.passwordChangeModel.set({
      newPassword: 'new-secret',
      passwordConfirmation: 'new-secret',
    });

    expect(component.passwordChangeForm().valid()).toBe(true);
  });
});
