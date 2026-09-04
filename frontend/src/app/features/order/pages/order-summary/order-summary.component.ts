import { ChangeDetectionStrategy, Component, computed, inject } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { Store } from '@ngrx/store';
import { TranslatePipe } from '@ngx-translate/core';
import { OrderCategory } from '../../../../shared/models/order.model';
import { NavigationActions } from '../../../../store/navigation/navigation.actions';
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
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class OrderSummaryComponent {
  private readonly store = inject(Store);

  readonly form = this.store.selectSignal(selectOrderForm);
  readonly savedOrder = this.store.selectSignal(selectCurrentOrder);
  readonly adults = computed(() => this.countCategories(this.form()?.adults ?? []));
  readonly children = computed(() => this.countCategories(this.form()?.children ?? []));
  readonly status = computed(() => this.savedOrder()?.status ?? 'provisional');
  onBack(): void {
    this.store.dispatch(NavigationActions.navigate({ target: 'back' }));
  }

  onOrder(): void {
    if (this.form() !== null) {
      this.store.dispatch(OrderActions.orderSaveRequested());
    }
  }

  private countCategories(categories: (OrderCategory | '')[]): CategoryQuantity[] {
    return (['catA', 'catB', 'catC', 'catD', 'catE', 'catF', 'catG'] as OrderCategory[])
      .map((category) => ({
        category,
        quantity: categories.filter((value) => value === category).length,
      }))
      .filter(({ quantity }) => quantity > 0);
  }
}
