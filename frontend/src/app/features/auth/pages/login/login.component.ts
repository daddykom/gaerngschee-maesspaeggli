import { Component, inject, OnInit } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatInputModule } from '@angular/material/input';
import { ActivatedRoute } from '@angular/router';
import { TranslatePipe } from '@ngx-translate/core';
import { Store } from '@ngrx/store';
import { AuthActions } from '../../../../store/auth/auth.actions';
import { selectAuthLoading } from '../../../../store/auth/auth.feature';

@Component({
  selector: 'app-login',
  imports: [MatCardModule, MatInputModule, MatButtonModule, ReactiveFormsModule, TranslatePipe],
  templateUrl: './login.component.html',
  styleUrl: './login.component.scss',
})
export class Login implements OnInit {
  private readonly route = inject(ActivatedRoute);
  private readonly store = inject(Store);

  readonly loading = this.store.selectSignal(selectAuthLoading);

  loginForm = new FormGroup({
    email: new FormControl('', [Validators.required, Validators.email]),
    password: new FormControl('', [Validators.required]),
  });

  ngOnInit(): void {
    const email = this.route.snapshot.queryParamMap.get('email');
    if (email) {
      this.loginForm.patchValue({ email });
    }
  }

  onSubmit(): void {
    if (!this.loginForm.valid) {
      this.loginForm.markAllAsTouched();
      return;
    }

    const { email, password } = this.loginForm.getRawValue();
    if (email === null || password === null) {
      return;
    }

    this.store.dispatch(AuthActions.login({ email, password }));
  }
}
