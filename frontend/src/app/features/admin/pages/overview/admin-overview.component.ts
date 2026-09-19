import { Component, computed, inject } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatDialog } from '@angular/material/dialog';
import { Store } from '@ngrx/store';
import { TranslatePipe } from '@ngx-translate/core';
import { ConfirmDialogComponent, ConfirmDialogData } from '../../../../shared/components/confirm-dialog/confirm-dialog';
import { AdminOverviewCategory } from '../../../../shared/models/admin-overview.model';
import { AdminOverviewActions } from '../../../../store/admin-overview/admin-overview.actions';
import { selectAdminOverview } from '../../../../store/admin-overview/admin-overview.feature';
import { selectFrontendPublicConfigs } from '../../../../store/frontend-config/frontend-config.feature';

@Component({
  selector: 'app-admin-overview',
  imports: [MatButtonModule, TranslatePipe],
  templateUrl: './admin-overview.component.html',
  styleUrl: './admin-overview.component.scss',
})
export class AdminOverviewComponent {
  private readonly store = inject(Store);
  private readonly dialog = inject(MatDialog);

  readonly overview = this.store.selectSignal(selectAdminOverview);
  readonly publicConfigs = this.store.selectSignal(selectFrontendPublicConfigs);
  readonly printDate = new Intl.DateTimeFormat('de-CH', { dateStyle: 'long' }).format(new Date());
  readonly canDeliver = computed(() => {
    const endDate = this.configValue('campaign_end_date');
    const processingYear = this.overview()?.year;
    if (endDate === null || processingYear === undefined || !/^\d{4}-\d{2}-\d{2}$/.test(endDate)) {
      return false;
    }

    const currentDate = new Intl.DateTimeFormat('en-CA', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      timeZone: 'Europe/Zurich',
    }).format(new Date());

    return currentDate.slice(0, 4) === String(processingYear) && currentDate > endDate;
  });

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

  private configValue(variableName: string): string | null {
    const value = this.publicConfigs().find((config) => config.variableName === variableName)?.value;
    return typeof value === 'string' && value.trim() !== '' ? value.trim() : null;
  }
}
