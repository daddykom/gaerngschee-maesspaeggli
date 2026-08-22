import { ChangeDetectionStrategy, Component, computed, inject } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { Store } from '@ngrx/store';
import { ActivatedRoute } from '@angular/router';
import { TranslatePipe } from '@ngx-translate/core';
import { toSignal } from '@angular/core/rxjs-interop';
import { map, of, switchMap } from 'rxjs';
import { ControlErrorComponent } from '../../../../shared/components/control-error/control-error';
import { AdminUsersService } from '../../../../shared/services/admin-users.service';
import { selectAuthGroup } from '../../../../store/auth/auth.feature';
import { AdminUsersActions } from '../../../../store/admin-users/admin-users.actions';
import { selectAdminUsersErrorCode, selectAdminUsersSaving } from '../../../../store/admin-users/admin-users.feature';
import { NavigationActions } from '../../../../store/navigation/navigation.actions';

@Component({
  selector: 'app-admin-user-edit',
  imports: [
    MatButtonModule,
    MatCheckboxModule,
    MatInputModule,
    MatSelectModule,
    ReactiveFormsModule,
    TranslatePipe,
    ControlErrorComponent,
  ],
  templateUrl: './user-edit.component.html',
  styleUrl: './user-edit.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class UserEditComponent {
  private readonly route = inject(ActivatedRoute);
  private readonly store = inject(Store);
  private readonly service = inject(AdminUsersService);

  private readonly authGroup = this.store.selectSignal(selectAuthGroup);
  readonly isAdmin = computed(() => this.authGroup() === 'admin');
  readonly saving = this.store.selectSignal(selectAdminUsersSaving);
  readonly errorCode = this.store.selectSignal(selectAdminUsersErrorCode);
  readonly userId = this.route.snapshot.paramMap.get('userId');
  readonly isNew = this.route.snapshot.routeConfig?.path === 'users/new';
  readonly loading = toSignal(
    this.isNew || this.userId === null
      ? of(false)
      : this.service.get(this.userId).pipe(
          map(({ user }) => {
            this.form.patchValue({
              email: user.email,
              group: user.group,
              requiredPasswordReset: user.required_password_reset,
            });
            return false;
          }),
          switchMap(() => of(false)),
        ),
    { initialValue: !this.isNew },
  );

  readonly form = new FormGroup({
    email: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.email] }),
    group: new FormControl<'admin' | 'user' | 'client'>('user', { nonNullable: true, validators: [Validators.required] }),
    requiredPasswordReset: new FormControl(false, { nonNullable: true }),
  });

  onSubmit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    const value = this.form.getRawValue();
    if (this.isNew) {
      this.store.dispatch(AdminUsersActions.create({ email: value.email, group: value.group }));
      return;
    }

    if (this.userId === null) {
      return;
    }

    const changes = this.isAdmin()
      ? {
          email: value.email,
          group: value.group,
          required_password_reset: value.requiredPasswordReset,
        }
      : { email: value.email };

    this.store.dispatch(AdminUsersActions.update({
      userId: this.userId,
      changes,
    }));
  }

  cancel(): void {
    this.store.dispatch(NavigationActions.navigate({ target: 'back' }));
  }
}
