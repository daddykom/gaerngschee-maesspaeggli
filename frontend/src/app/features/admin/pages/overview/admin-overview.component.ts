import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { Store } from '@ngrx/store';
import { TranslatePipe } from '@ngx-translate/core';
import { AdminOverviewSection } from '../../../../shared/models/admin-overview.model';
import { selectAdminOverview } from '../../../../store/admin-overview/admin-overview.feature';

@Component({
  selector: 'app-admin-overview',
  imports: [TranslatePipe],
  templateUrl: './admin-overview.component.html',
  styleUrl: './admin-overview.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AdminOverviewComponent {
  private readonly store = inject(Store);

  readonly overview = this.store.selectSignal(selectAdminOverview);

  sectionRows(section: AdminOverviewSection): Array<{ label: string; count: number }> {
    return [
      { label: 'app.admin.overview.orderCount', count: section.orderCount },
      ...section.categories.filter((category) => category.packageCount > 0).map((category) => ({
        label: `app.order.categories.options.${category.category}`,
        count: category.packageCount,
      })),
    ];
  }
}
