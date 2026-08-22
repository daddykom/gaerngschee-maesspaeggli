import { provideRouter } from '@angular/router';
import { provideTranslateService } from '@ngx-translate/core';
import { provideStore } from '@ngrx/store';
import { AuthService } from '../../../../shared/services/auth.service';
import { ComponentFixture, TestBed } from '@angular/core/testing';
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
          provideStore(),
          { provide: AuthService, useValue: { changePassword: jest.fn() } },
        ],
    }).compileComponents();

    fixture = TestBed.createComponent(PasswordChange);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('creates a form with two password fields', () => {
    expect(component.passwordChangeForm.controls.newPassword).toBeDefined();
    expect(component.passwordChangeForm.controls.passwordConfirmation).toBeDefined();
    expect(fixture.nativeElement.querySelectorAll('input[type="password"]')).toHaveLength(2);
  });

  it('requires both password fields', () => {
    component.onSubmit();

    expect(component.passwordChangeForm.invalid).toBe(true);
    expect(component.passwordChangeForm.controls.newPassword.touched).toBe(true);
    expect(component.passwordChangeForm.controls.passwordConfirmation.touched).toBe(true);
  });

  it('rejects different passwords', () => {
    component.passwordChangeForm.setValue({
      newPassword: 'new-secret',
      passwordConfirmation: 'different-secret',
    });

    expect(component.passwordChangeForm.hasError('passwordsDoNotMatch')).toBe(true);
    expect(component.passwordChangeForm.invalid).toBe(true);
  });

  it('accepts matching passwords', () => {
    component.passwordChangeForm.setValue({
      newPassword: 'new-secret',
      passwordConfirmation: 'new-secret',
    });

    expect(component.passwordChangeForm.valid).toBe(true);
  });
});
