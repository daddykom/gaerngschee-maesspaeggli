import { Component, inject, signal } from '@angular/core';
import { form, FormField, required, validate } from '@angular/forms/signals';
import { MatButtonModule } from '@angular/material/button';
import { MatInputModule } from '@angular/material/input';
import { Store } from '@ngrx/store';
import { TranslatePipe } from '@ngx-translate/core';
import { ControlErrorComponent } from '../../../../shared/components/control-error/control-error';
import { AuthActions } from '../../../../store/auth/auth.actions';
import { selectAuthPasswordChangeLoading } from '../../../../store/auth/auth.feature';

@Component({
  selector: 'app-password-change',
  imports: [MatInputModule, MatButtonModule, FormField, TranslatePipe, ControlErrorComponent],
  templateUrl: './password-change.component.html',
  styleUrl: './password-change.component.scss',
})
export class PasswordChange {
  private readonly store = inject(Store);
  readonly submitting = this.store.selectSignal(selectAuthPasswordChangeLoading);

  readonly passwordChangeModel = signal({ newPassword: '', passwordConfirmation: '' });
  readonly passwordChangeForm = form(this.passwordChangeModel, (schema) => {
    required(schema.newPassword);
    required(schema.passwordConfirmation);
    validate(schema.passwordConfirmation, ({ valueOf }) =>
      valueOf(schema.newPassword) === valueOf(schema.passwordConfirmation)
        ? undefined
        : { kind: 'passwordsDoNotMatch' },
    );
  });
  onSubmit(): void {
    if (!this.passwordChangeForm().valid()) {
      this.passwordChangeForm().markAsTouched();
      this.passwordChangeForm.newPassword().markAsTouched();
      this.passwordChangeForm.passwordConfirmation().markAsTouched();
      return;
    }

    this.store.dispatch(
      AuthActions.passwordChange({ password: this.passwordChangeModel().newPassword }),
    );
  }
}
