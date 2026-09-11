import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { form, FormField, maxLength, required, validate } from '@angular/forms/signals';
import { MatButtonModule } from '@angular/material/button';
import { MatInputModule } from '@angular/material/input';
import { Store } from '@ngrx/store';
import { TranslatePipe } from '@ngx-translate/core';
import { ControlErrorComponent } from '../../../../shared/components/control-error/control-error';
import { AuthActions } from '../../../../store/auth/auth.actions';
import { selectPasswordResetLoading } from '../../../../store/password-reset/password-reset.feature';
import { inputLimits } from '../../../../shared/constants/input-limits';

@Component({
  selector: 'app-password-reset',
  imports: [MatInputModule, MatButtonModule, FormField, TranslatePipe, ControlErrorComponent],
  templateUrl: './password-reset.component.html',
  styleUrl: './password-reset.component.scss',
  changeDetection: ChangeDetectionStrategy.Eager,
})
export class PasswordResetComponent {
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  private readonly store = inject(Store);
  readonly token = this.route.snapshot.queryParamMap.get('token') ?? '';
  readonly loading = this.store.selectSignal(selectPasswordResetLoading);
  readonly model = signal({ email: '', newPassword: '', passwordConfirmation: '' });
  readonly passwordResetForm = form(this.model, (schema) => {
    required(schema.email);
    maxLength(schema.email, inputLimits.email);
    required(schema.newPassword);
    maxLength(schema.newPassword, inputLimits.password);
    required(schema.passwordConfirmation);
    maxLength(schema.passwordConfirmation, inputLimits.password);
    validate(schema.passwordConfirmation, ({ valueOf }) =>
      valueOf(schema.newPassword) === valueOf(schema.passwordConfirmation)
        ? undefined
        : { kind: 'passwordsDoNotMatch' },
    );
  });

  constructor() {
    if (this.token) {
      void this.router.navigate([], {
        relativeTo: this.route,
        queryParams: { token: null },
        queryParamsHandling: 'merge',
        replaceUrl: true,
      });
    }
  }

  onSubmit(): void {
    if (!this.token || !this.passwordResetForm().valid()) {
      this.passwordResetForm().markAsTouched();
      return;
    }

    this.store.dispatch(AuthActions.passwordReset({ token: this.token, password: this.model().newPassword }));
  }
}
