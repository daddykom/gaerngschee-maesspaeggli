import { Component, inject, signal } from '@angular/core';
import { email, form, FormField, maxLength, required } from '@angular/forms/signals';
import { MatButtonModule } from '@angular/material/button';
import { MatDialog } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { Store } from '@ngrx/store';
import { TranslatePipe } from '@ngx-translate/core';
import { ConfirmDialogComponent } from '../../../../shared/components/confirm-dialog/confirm-dialog';
import { ControlErrorComponent } from '../../../../shared/components/control-error/control-error';
import { InfoBoxComponent } from '../../../../shared/components/info-box/info-box';
import { inputLimits } from '../../../../shared/constants/input-limits';
import { AdminUsersActions } from '../../../../store/admin-users/admin-users.actions';
import {
  selectAdminClientDeletion,
  selectAdminUsersDeletingClientOrder,
} from '../../../../store/admin-users/admin-users.feature';

@Component({
  selector: 'app-client-deletion',
  imports: [ControlErrorComponent, FormField, InfoBoxComponent, MatButtonModule, MatFormFieldModule, MatInputModule, TranslatePipe],
  templateUrl: './client-deletion.html',
  styleUrl: './client-deletion.scss',
})
export class ClientDeletion {
  private readonly store = inject(Store);
  private readonly dialog = inject(MatDialog);

  readonly clientDeletion = this.store.selectSignal(selectAdminClientDeletion);
  readonly deletingClientOrder = this.store.selectSignal(selectAdminUsersDeletingClientOrder);
  readonly clientDeletionModel = signal({ email: '' });
  readonly clientDeletionForm = form(this.clientDeletionModel, (schema) => {
    required(schema.email);
    email(schema.email);
    maxLength(schema.email, inputLimits.email);
  });

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
