import { Component, inject, OnInit, signal } from '@angular/core';
import { email, form, FormField, required } from '@angular/forms/signals';
import { MatButtonModule } from '@angular/material/button';
import { MatInputModule } from '@angular/material/input';
import { ActivatedRoute } from '@angular/router';
import { TranslatePipe } from '@ngx-translate/core';
import { Store } from '@ngrx/store';
import { AuthActions } from '../../../../store/auth/auth.actions';
import { selectAuthLoading } from '../../../../store/auth/auth.feature';
import { ControlErrorComponent } from '../../../../shared/components/control-error/control-error';

@Component({
  selector: 'app-login',
  imports: [
    MatInputModule,
    MatButtonModule,
    FormField,
    TranslatePipe,
    ControlErrorComponent,
  ],
  templateUrl: './login.component.html',
  styleUrl: './login.component.scss',
})
export class Login implements OnInit {
  private readonly route = inject(ActivatedRoute);
  private readonly store = inject(Store);

  readonly loading = this.store.selectSignal(selectAuthLoading);

  readonly loginModel = signal({ email: '', password: '' });
  readonly loginForm = form(this.loginModel, (schema) => {
    required(schema.email);
    email(schema.email);
    required(schema.password);
  });

  ngOnInit(): void {
    const email = this.route.snapshot.queryParamMap.get('email');
    if (email) {
      this.loginModel.update((model) => ({ ...model, email }));
    }
  }

  onSubmit(): void {
    if (!this.loginForm().valid()) {
      this.loginForm.email().markAsTouched();
      this.loginForm.password().markAsTouched();
      return;
    }

    const { email, password } = this.loginModel();

    this.store.dispatch(AuthActions.login({ email, password }));
  }

  onForgotPassword(): void {
    this.store.dispatch(AuthActions.forgotPassword());
  }
}
