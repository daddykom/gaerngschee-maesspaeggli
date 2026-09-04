import {
  ChangeDetectionStrategy,
  Component,
  computed,
  effect,
  inject,
  signal,
  untracked,
} from '@angular/core';
import { applyEach, FieldTree, form, FormField, required } from '@angular/forms/signals';
import { MatButtonModule } from '@angular/material/button';
import { MatDividerModule } from '@angular/material/divider';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { Store } from '@ngrx/store';
import { TranslatePipe } from '@ngx-translate/core';
import { InfoBoxComponent } from '../../../../shared/components/info-box/info-box';
import { ControlErrorComponent } from '../../../../shared/components/control-error/control-error';
import { ClientOrder, OrderCategory, OrderDraft } from '../../../../shared/models/order.model';
import { OrderActions } from '../../../../store/order/order.actions';
import { selectCurrentOrder, selectOrderDraft } from '../../../../store/order/order.feature';
import { NavigationActions } from '../../../../store/navigation/navigation.actions';
import { selectFrontendPublicConfigs } from '../../../../store/frontend-config/frontend-config.feature';
import {
  selectAuthAdultsCount,
  selectAuthChildrenCount,
  selectAuthFairgateUserExists,
  selectAuthSalutation,
} from '../../../../store/auth/auth.feature';

export type Categorie = OrderCategory | '';

interface OrderFormModel {
  manualAdultsCount: number;
  manualChildrenCount: number;
  adults: Categorie[];
  children: Categorie[];
}

export const categories: Categorie[] = ['catA', 'catB', 'catC', 'catD', 'catE', 'catF', 'catG'];

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
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class OrderComponent {
  private readonly store = inject(Store);

  readonly fairgateUserExists = this.store.selectSignal(selectAuthFairgateUserExists);
  readonly childrenCount = this.store.selectSignal(selectAuthChildrenCount);
  readonly adultsCount = this.store.selectSignal(selectAuthAdultsCount);
  readonly salutation = this.store.selectSignal(selectAuthSalutation);
  readonly publicConfigs = this.store.selectSignal(selectFrontendPublicConfigs);
  readonly currentOrder = this.store.selectSignal(selectCurrentOrder);
  readonly draft = this.store.selectSignal(selectOrderDraft);
  readonly categories = categories;
  readonly model = signal<OrderFormModel>({
    manualAdultsCount: 1,
    manualChildrenCount: 0,
    adults: [],
    children: [],
  });
  readonly form = form(this.model, (schema) => {
    applyEach(schema.adults, (category) => required(category));
    applyEach(schema.children, (category) => required(category));
  });
  readonly displayAdultsCount = computed(() =>
    this.fairgateUserExists() ? (this.adultsCount() ?? 0) : this.model().manualAdultsCount,
  );
  readonly displayChildrenCount = computed(() =>
    this.fairgateUserExists() ? (this.childrenCount() ?? 0) : this.model().manualChildrenCount,
  );
  readonly fairgateUrl = computed(() => {
    const config = this.publicConfigs().find(({ variableName }) => variableName === 'fairgate_url');
    return typeof config?.value === 'string' ? config.value : null;
  });

  constructor() {
    this.store.dispatch(OrderActions.loadCurrent());
    effect(() => {
      const order = this.currentOrder();
      if (order !== null) {
        untracked(() => this.applyOrder(order));
      }
    });
    effect(() => {
      const draft = this.draft();
      if (draft !== null && this.currentOrder() === null) {
        untracked(() => this.applyDraft(draft));
      }
    });
    effect(() => this.resizeCategories(this.displayAdultsCount(), this.displayChildrenCount()));
  }

  adultField(index: number): FieldTree<Categorie> {
    return (this.form.adults as unknown as FieldTree<Categorie[]>)[index] as FieldTree<Categorie>;
  }

  childField(index: number): FieldTree<Categorie> {
    return (this.form.children as unknown as FieldTree<Categorie[]>)[index] as FieldTree<Categorie>;
  }

  personId(group: 'adult' | 'child', index: number): string {
    return `${group}-${index + 1}`;
  }

  personLabelId(group: 'adult' | 'child', index: number): string {
    return `${this.personId(group, index)}-label`;
  }

  onSubmit(): void {
    if (!this.form().valid()) {
      this.form().markAsTouched();
      this.model().adults.forEach((_, index) => this.adultField(index)().markAsTouched());
      this.model().children.forEach((_, index) => this.childField(index)().markAsTouched());
      return;
    }

    this.store.dispatch(OrderActions.setDraft({ draft: this.createDraft() }));
    this.store.dispatch(NavigationActions.navigate({ target: '/order/summary' }));
  }

  private resizeCategories(adultCount: number, childCount: number): void {
    const current = untracked(this.model);
    const adults = this.resize(current.adults, adultCount);
    const children = this.resize(current.children, childCount);
    if (adults.length === current.adults.length && children.length === current.children.length) {
      return;
    }
    this.model.update((model) => ({ ...model, adults, children }));
  }

  private resize(values: Categorie[], count: number): Categorie[] {
    return Array.from({ length: Math.max(0, count) }, (_, index) => values[index] ?? '');
  }

  private applyOrder(order: ClientOrder): void {
    this.model.set({
      manualAdultsCount: order.adultsCount,
      manualChildrenCount: order.childrenCount,
      adults: this.categoriesFor(order, 'adult', order.adultsCount),
      children: this.categoriesFor(order, 'child', order.childrenCount),
    });
  }

  private applyDraft(draft: OrderDraft): void {
    this.model.set({
      manualAdultsCount: draft.adultsCount,
      manualChildrenCount: draft.childrenCount,
      adults: draft.adults,
      children: draft.children,
    });
  }

  private categoriesFor(order: ClientOrder, personType: 'adult' | 'child', count: number): Categorie[] {
    return order.items
      .filter((item) => item.personType === personType)
      .flatMap((item) => Array.from({ length: item.quantity }, () => item.category))
      .slice(0, count);
  }

  private createDraft(): OrderDraft {
    return {
      adultsCount: this.displayAdultsCount(),
      childrenCount: this.displayChildrenCount(),
      adults: this.model().adults as OrderCategory[],
      children: this.model().children as OrderCategory[],
    };
  }
}
