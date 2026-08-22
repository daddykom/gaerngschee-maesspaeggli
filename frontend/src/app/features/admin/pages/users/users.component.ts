import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatDialog } from '@angular/material/dialog';
import { RouterLink } from '@angular/router';
import { Store } from '@ngrx/store';
import { TranslatePipe } from '@ngx-translate/core';
import { InfoBoxComponent } from '../../../../shared/components/info-box/info-box';
import { ConfirmDialogComponent } from '../../../../shared/components/confirm-dialog/confirm-dialog';
import {
  selectAdminUsers,
  selectAdminUsersEmailSentTo,
  selectAdminUsersErrorCode,
  selectAdminUsersLoading,
  selectAdminUsersSuccess,
} from '../../../../store/admin-users/admin-users.feature';
import { AdminUsersActions } from '../../../../store/admin-users/admin-users.actions';

@Component({
  selector: 'app-admin-users',
  imports: [MatButtonModule, RouterLink, TranslatePipe, InfoBoxComponent],
  templateUrl: './users.component.html',
  styleUrl: './users.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class UsersComponent {
  private readonly store = inject(Store);
  private readonly dialog = inject(MatDialog);

  readonly users = this.store.selectSignal(selectAdminUsers);
  readonly loading = this.store.selectSignal(selectAdminUsersLoading);
  readonly errorCode = this.store.selectSignal(selectAdminUsersErrorCode);
  readonly success = this.store.selectSignal(selectAdminUsersSuccess);
  readonly emailSentTo = this.store.selectSignal(selectAdminUsersEmailSentTo);

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
}
