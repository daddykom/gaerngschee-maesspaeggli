import {
  Component,
  computed,
  effect,
  inject,
  signal,
  untracked,
} from '@angular/core';
import { applyEach, disabled, FieldTree, form, FormField, required } from '@angular/forms/signals';
import { MatButtonModule } from '@angular/material/button';
import { MatDividerModule } from '@angular/material/divider';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { Store } from '@ngrx/store';
import { TranslatePipe } from '@ngx-translate/core';
import { InfoBoxComponent } from '../../../../shared/components/info-box/info-box';
import { ControlErrorComponent } from '../../../../shared/components/control-error/control-error';
import {
  adultCategories,
  CategorySelection,
  childCategories,
  OrderForm,
} from '../../../../shared/models/order.model';
import { OrderActions } from '../../../../store/order/order.actions';
import { selectOrderForm } from '../../../../store/order/order.feature';
import { NavigationActions } from '../../../../store/navigation/navigation.actions';
import { selectFrontendPublicConfigs } from '../../../../store/frontend-config/frontend-config.feature';
import {
  selectAuthAdultsCount,
  selectAuthChildrenCount,
  selectAuthFairgateUserExists,
  selectAuthSalutation,
} from '../../../../store/auth/auth.feature';
import { selectCurrentOrder } from '../../../../store/order/order.feature';

@Component({
  selector: 'app-order',
  standalone: true,
  imports: [
    FormField,
    ControlErrorComponent,
    InfoBoxComponent,
    MatButtonModule,
    MatDividerModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    TranslatePipe,
  ],
  templateUrl: './order.component.html',
})
export class OrderComponent {
  private readonly store = inject(Store);

  readonly fairgateUserExists = this.store.selectSignal(selectAuthFairgateUserExists);
  readonly childrenCount = this.store.selectSignal(selectAuthChildrenCount);
  readonly adultsCount = this.store.selectSignal(selectAuthAdultsCount);
  readonly salutation = this.store.selectSignal(selectAuthSalutation);
  readonly publicConfigs = this.store.selectSignal(selectFrontendPublicConfigs);
  readonly orderForm = this.store.selectSignal(selectOrderForm);
  readonly currentOrder = this.store.selectSignal(selectCurrentOrder);
  readonly adultCategories = adultCategories;
  readonly childCategories = childCategories;
  readonly model = signal<OrderForm>({
    adultsCount: this.adultsCount() ?? 0,
    childrenCount: this.childrenCount() ?? 0,
    adults: [],
    children: [],
  });
  readonly form = form(this.model, (schema) => {
    applyEach(schema.adults, (category) => {
      required(category);
      disabled(category, () => this.orderLocked());
    });
    applyEach(schema.children, (category) => {
      required(category);
      disabled(category, () => this.orderLocked());
    });
  });
  readonly displayAdultsCount = computed(() => this.model().adultsCount);
  readonly displayChildrenCount = computed(() => this.model().childrenCount);
  readonly fairgateUrl = computed(() => {
    const config = this.publicConfigs().find(({ variableName }) => variableName === 'fairgate_url');
    if (typeof config?.value !== 'string') {
      return null;
    }
    try {
      const url = new URL(config.value);
      return url.protocol === 'https:' && !url.username && !url.password ? url.href : null;
    } catch {
      return null;
    }
  });
  readonly orderLocked = computed(() => ['toDeliver', 'qrcode', 'delivered'].includes(this.currentOrder()?.status ?? ''));

  constructor() {
    effect(() => {
      const orderForm = this.orderForm();
      if (orderForm !== null) {
        untracked(() => this.model.set(orderForm));
      }
    });
    effect(() => this.resizeCategories(this.displayAdultsCount(), this.displayChildrenCount()));
  }

  adultField(index: number): FieldTree<CategorySelection> {
    return (this.form.adults as unknown as FieldTree<CategorySelection[]>)[index] as FieldTree<CategorySelection>;
  }

  childField(index: number): FieldTree<CategorySelection> {
    return (this.form.children as unknown as FieldTree<CategorySelection[]>)[index] as FieldTree<CategorySelection>;
  }

  personId(group: 'adult' | 'child', index: number): string {
    return `${group}-${index + 1}`;
  }

  onSubmit(): void {
    if (!this.form().valid()) {
      this.form().markAsTouched();
      this.model().adults.forEach((_, index) => this.adultField(index)().markAsTouched());
      this.model().children.forEach((_, index) => this.childField(index)().markAsTouched());
      return;
    }

    this.store.dispatch(NavigationActions.navigate({ target: '/order/summary' }));
  }

  onCountChange(field: 'adultsCount' | 'childrenCount', value: string): void {
    const count = Number(value);
    this.model.update((model) => ({ ...model, [field]: count }));
    this.store.dispatch(OrderActions.orderFormUpdated({ form: { [field]: count } }));
  }

  onCategoryChange(field: 'adults' | 'children', index: number, value: CategorySelection): void {
    const values = [...this.model()[field]];
    values[index] = value;
    this.store.dispatch(OrderActions.orderFormUpdated({ form: { [field]: values } }));
  }

  private resizeCategories(adultCount: number, childCount: number): void {
    const current = untracked(this.model);
    const adults = this.resize(current.adults, childCount > 0 ? 0 : adultCount);
    const children = this.resize(current.children, childCount);
    if (adults.length === current.adults.length && children.length === current.children.length) {
      return;
    }
    this.model.update((model) => ({ ...model, adults, children }));
    if (adults.length !== current.adults.length) {
      this.store.dispatch(OrderActions.orderFormUpdated({ form: { adults } }));
    }
    if (children.length !== current.children.length) {
      this.store.dispatch(OrderActions.orderFormUpdated({ form: { children } }));
    }
  }

  private resize(values: CategorySelection[], count: number): CategorySelection[] {
    return Array.from({ length: Math.max(0, count) }, (_, index) => values[index] ?? '');
  }

}
