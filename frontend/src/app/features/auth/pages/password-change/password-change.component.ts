import { Component } from '@angular/core';
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
    }
  }
}
