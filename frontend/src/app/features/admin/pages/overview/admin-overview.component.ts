import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { MatCardModule } from '@angular/material/card';
import { Store } from '@ngrx/store';
import {
  selectAdminOverviewKategories,
  selectAdminOverviewNumbOrders,
} from '../../../../store/admin-overview/admin-overview.feature';

@Component({
  selector: 'app-admin-overview',
  imports: [MatCardModule],
  templateUrl: './admin-overview.component.html',
  styleUrl: './admin-overview.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AdminOverviewComponent {
  private readonly store = inject(Store);

  readonly numbOrders = this.store.selectSignal(selectAdminOverviewNumbOrders);
  readonly kategories = this.store.selectSignal(selectAdminOverviewKategories);
}
