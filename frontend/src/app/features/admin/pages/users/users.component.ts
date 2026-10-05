import { Component, inject, signal } from '@angular/core';
import { email, form, FormField, maxLength, required } from '@angular/forms/signals';
import { MatButtonModule } from '@angular/material/button';
import { MatDialog } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { RouterLink } from '@angular/router';
import { Store } from '@ngrx/store';
import { TranslatePipe } from '@ngx-translate/core';
import { ConfirmDialogComponent } from '../../../../shared/components/confirm-dialog/confirm-dialog';
import {
  selectAdminClientDeletion,
  selectAdminUsersDeletingClientOrder,
  selectAdminUsers,
  selectAdminUsersLoading,
} from '../../../../store/admin-users/admin-users.feature';
import { AdminUsersActions } from '../../../../store/admin-users/admin-users.actions';
import { ControlErrorComponent } from '../../../../shared/components/control-error/control-error';
import { InfoBoxComponent } from '../../../../shared/components/info-box/info-box';
import { inputLimits } from '../../../../shared/constants/input-limits';

@Component({
  selector: 'app-admin-users',
  imports: [ControlErrorComponent, FormField, InfoBoxComponent, MatButtonModule, MatFormFieldModule, MatInputModule, RouterLink, TranslatePipe],
  templateUrl: './users.component.html',
  styleUrl: './users.component.scss',
})
export class UsersComponent {
  private readonly store = inject(Store);
  private readonly dialog = inject(MatDialog);

  readonly users = this.store.selectSignal(selectAdminUsers);
  readonly loading = this.store.selectSignal(selectAdminUsersLoading);
  readonly clientDeletion = this.store.selectSignal(selectAdminClientDeletion);
  readonly deletingClientOrder = this.store.selectSignal(selectAdminUsersDeletingClientOrder);
  readonly clientDeletionModel = signal({ email: '' });
  readonly clientDeletionForm = form(this.clientDeletionModel, (schema) => {
    required(schema.email);
    email(schema.email);
    maxLength(schema.email, inputLimits.email);
  });

  constructor() {
    this.store.dispatch(AdminUsersActions.load());
  }

  deleteUser(userId: string): void {
    const dialogRef = this.dialog.open(ConfirmDialogComponent, {
      data: {
        title: 'app.admin.users.deleteTitle',
        message: 'app.admin.users.deleteMessage',
        confirmLabel: 'app.admin.users.deleteConfirm',
        cancelLabel: 'app.admin.users.cancel',
      },
    });

    dialogRef.afterClosed().subscribe((confirmed) => {
      if (confirmed) {
        this.store.dispatch(AdminUsersActions.delete({ userId }));
      }
    });
  }

  sendPasswordReset(userId: string): void {
    this.store.dispatch(AdminUsersActions.sendPasswordReset({ userId }));
  }

  clientDeletionEmailChanged(email: string): void {
    this.clientDeletionModel.update((model) => ({ ...model, email }));
    this.store.dispatch(AdminUsersActions.clientDeletionEmailChanged({ email }));
  }

  searchClientOrder(): void {
    if (!this.clientDeletionForm().valid()) {
      this.clientDeletionForm().markAsTouched();
      return;
    }

    this.store.dispatch(AdminUsersActions.clientDeletionSearch({ email: this.clientDeletionModel().email.trim() }));
  }

  deleteClientOrder(): void {
    const lookup = this.clientDeletion();
    if (lookup.status !== 'loaded' || !lookup.result.canDelete || this.deletingClientOrder()) {
      return;
    }

    this.dialog.open(ConfirmDialogComponent, {
      data: {
        title: 'app.admin.users.clientDeletion.deleteTitle',
        message: 'app.admin.users.clientDeletion.deleteMessage',
        confirmLabel: 'app.admin.users.clientDeletion.continue',
        cancelLabel: 'app.admin.users.cancel',
      },
    }).afterClosed().subscribe((confirmed) => {
      if (!confirmed) return;

      this.dialog.open(ConfirmDialogComponent, {
        data: {
          title: 'app.admin.users.clientDeletion.finalTitle',
          message: 'app.admin.users.clientDeletion.finalMessage',
          confirmLabel: 'app.admin.users.clientDeletion.deleteConfirm',
          cancelLabel: 'app.admin.users.cancel',
        },
      }).afterClosed().subscribe((finallyConfirmed) => {
        if (finallyConfirmed) {
          this.store.dispatch(AdminUsersActions.clientDeletion({ userId: lookup.result.client.id }));
        }
      });
    });
  }
}
