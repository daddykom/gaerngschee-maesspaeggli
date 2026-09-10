import { Component, computed, inject } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { Store } from '@ngrx/store';
import { TranslatePipe } from '@ngx-translate/core';
import { CategorySelection, orderCategories, OrderCategory } from '../../../../shared/models/order.model';
import { NavigationActions } from '../../../../store/navigation/navigation.actions';
import { selectAuthFairgateUserExists } from '../../../../store/auth/auth.feature';
import { selectFrontendPublicConfigs } from '../../../../store/frontend-config/frontend-config.feature';
import { OrderActions } from '../../../../store/order/order.actions';
import {
  selectCurrentOrder,
   selectOrderForm,
} from '../../../../store/order/order.feature';

interface CategoryQuantity {
  category: OrderCategory;
  quantity: number;
}

@Component({
  selector: 'app-order-summary',
  standalone: true,
  imports: [MatButtonModule, TranslatePipe],
  templateUrl: './order-summary.component.html',
  styleUrl: './order-summary.component.scss',
})
export class OrderSummaryComponent {
  private readonly store = inject(Store);

  readonly form = this.store.selectSignal(selectOrderForm);
  readonly savedOrder = this.store.selectSignal(selectCurrentOrder);
  readonly fairgateUserExists = this.store.selectSignal(selectAuthFairgateUserExists);
  readonly publicConfigs = this.store.selectSignal(selectFrontendPublicConfigs);
  readonly campaignYear = computed(() => this.configValue('campaign_year'));
  readonly orderYear = computed(() => this.savedOrder()?.year ?? this.campaignYear() ?? '');
  readonly adults = computed(() => this.countCategories(this.form()?.adults ?? []));
  readonly children = computed(() => this.countCategories(this.form()?.children ?? []));
  readonly status = computed(() => this.fairgateUserExists() === true ? 'definitive' : 'provisional');
  onBack(): void {
    this.store.dispatch(NavigationActions.navigate({ target: 'back' }));
  }

  onOrder(): void {
    if (this.form() !== null) {
      this.store.dispatch(OrderActions.orderSaveRequested());
    }
  }

  private countCategories(categories: CategorySelection[]): CategoryQuantity[] {
    return orderCategories
      .map((category) => ({
        category,
        quantity: categories.filter((value) => value === category).length,
      }))
      .filter(({ quantity }) => quantity > 0);
  }

  private configValue(variableName: string): string | null {
    const value = this.publicConfigs().find((config) => config.variableName === variableName)?.value;
    return typeof value === 'string' && value.trim() !== '' ? value.trim() : null;
  }
}
