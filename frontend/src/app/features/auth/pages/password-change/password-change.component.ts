import { Component, inject } from '@angular/core';
import {
  AbstractControl,
  FormControl,
  FormGroup,
  ReactiveFormsModule,
  ValidationErrors,
  ValidatorFn,
  Validators,
} from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatInputModule } from '@angular/material/input';
import { TranslatePipe } from '@ngx-translate/core';
import { AuthActions } from '../../../../store/auth/auth.actions';
import { Store } from '@ngrx/store';
import {
  selectAuthPasswordChangeErrorCode,
  selectAuthPasswordChangeLoading,
} from '../../../../store/auth/auth.feature';
import { ControlErrorComponent } from '../../../../shared/components/control-error/control-error';

const matchingPasswordsValidator: ValidatorFn = (
  control: AbstractControl,
): ValidationErrors | null => {
  const newPassword = control.get('newPassword')?.value;
  const passwordConfirmation = control.get('passwordConfirmation')?.value;

  return newPassword === passwordConfirmation ? null : { passwordsDoNotMatch: true };
};

@Component({
  selector: 'app-password-change',
  imports: [
    MatInputModule,
    MatButtonModule,
    ReactiveFormsModule,
    TranslatePipe,
    ControlErrorComponent,
  ],
  templateUrl: './password-change.component.html',
  styleUrl: './password-change.component.scss',
})
export class PasswordChange {
  private readonly store = inject(Store);
  readonly submitting = this.store.selectSignal(selectAuthPasswordChangeLoading);
  readonly errorCode = this.store.selectSignal(selectAuthPasswordChangeErrorCode);

  readonly passwordChangeForm = new FormGroup(
    {
      newPassword: new FormControl('', [Validators.required]),
      passwordConfirmation: new FormControl('', [Validators.required]),
    },
    { validators: matchingPasswordsValidator },
  );

  onSubmit(): void {
    if (this.passwordChangeForm.invalid) {
      this.passwordChangeForm.markAllAsTouched();
      return;
    }

    const password = this.passwordChangeForm.controls.newPassword.value;
    if (password !== null) {
      this.store.dispatch(AuthActions.passwordChange({ password }));
    }
  }
}
