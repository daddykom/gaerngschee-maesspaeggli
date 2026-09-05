import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatDialog } from '@angular/material/dialog';
import { Store } from '@ngrx/store';
import { TranslatePipe } from '@ngx-translate/core';
import { ConfirmDialogComponent, ConfirmDialogData } from '../../../../shared/components/confirm-dialog/confirm-dialog';
import { AdminOverviewCategory } from '../../../../shared/models/admin-overview.model';
import { AdminOverviewActions } from '../../../../store/admin-overview/admin-overview.actions';
import { selectAdminOverview } from '../../../../store/admin-overview/admin-overview.feature';

@Component({
  selector: 'app-admin-overview',
  imports: [MatButtonModule, TranslatePipe],
  templateUrl: './admin-overview.component.html',
  styleUrl: './admin-overview.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AdminOverviewComponent {
  private readonly store = inject(Store);
  private readonly dialog = inject(MatDialog);

  readonly overview = this.store.selectSignal(selectAdminOverview);
  readonly printDate = new Intl.DateTimeFormat('de-CH', { dateStyle: 'long' }).format(new Date());

  categories(categories: AdminOverviewCategory[]): AdminOverviewCategory[] {
    return categories.filter((category) => Object.values(category)
      .filter((value) => typeof value === 'number')
      .some((value) => value > 0));
  }

  deliver(): void {
    const data: ConfirmDialogData = {
      title: 'app.admin.overview.deliverTitle',
      message: 'app.admin.overview.deliverQuestion',
      confirmLabel: 'app.admin.overview.deliverConfirm',
      cancelLabel: 'app.admin.overview.deliverCancel',
    };
    this.dialog.open(ConfirmDialogComponent, { data }).afterClosed().subscribe((confirmed) => {
      if (confirmed === true) {
        this.store.dispatch(AdminOverviewActions.deliver());
      }
    });
  }

  print(): void {
    window.print();
  }
}
