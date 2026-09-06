import { ChangeDetectionStrategy, Component, computed, inject } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatDialog } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { TranslatePipe } from '@ngx-translate/core';
import { Store } from '@ngrx/store';
import { OrderCategory } from '../../../shared/models/order.model';
import { ConfirmDialogComponent, ConfirmDialogData } from '../../../shared/components/confirm-dialog/confirm-dialog';
import { DeliveryActions } from '../../../store/delivery/delivery.actions';
import {
  selectDeliveryAction,
  selectDeliveryChildren,
  selectDeliveryClientName,
  selectDeliveryEmail,
  selectDeliveryLoad,
  selectDeliveryOrder,
  selectDeliveryViaToken,
} from '../../../store/delivery/delivery.feature';

@Component({
  selector: 'app-delivery',
  imports: [MatButtonModule, MatFormFieldModule, MatInputModule, TranslatePipe],
  templateUrl: './delivery.html',
  styleUrl: './delivery.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class Delivery {
  private readonly store = inject(Store);
  private readonly dialog = inject(MatDialog);
  private readonly route = inject(ActivatedRoute);

  readonly email = this.store.selectSignal(selectDeliveryEmail);
  readonly order = this.store.selectSignal(selectDeliveryOrder);
  readonly viaToken = this.store.selectSignal(selectDeliveryViaToken);
  readonly clientName = this.store.selectSignal(selectDeliveryClientName);
  readonly fairgateChildren = this.store.selectSignal(selectDeliveryChildren);
  readonly load = this.store.selectSignal(selectDeliveryLoad);
  readonly action = this.store.selectSignal(selectDeliveryAction);
  readonly loading = computed(() => this.load().status === 'loading');
  readonly error = computed(() => this.load().status === 'error' || this.action().status === 'error');
  readonly changingStatus = computed(() => this.action().status === 'loading');
  readonly categories = computed(() => {
    const items = this.order()?.items ?? [];
    return (['catA', 'catB', 'catC', 'catD', 'catE', 'catF', 'catG'] as OrderCategory[])
      .map((category) => ({ category, quantity: items.filter((item) => item.category === category).reduce((sum, item) => sum + item.quantity, 0) }))
      .filter((item) => item.quantity > 0);
  });

  constructor() {
    const token = this.route.snapshot.queryParamMap.get('token');
    if (token) this.store.dispatch(DeliveryActions.loadRequested({ token }));
  }

  search(): void {
    const email = this.email().trim();
    if (email) this.store.dispatch(DeliveryActions.loadRequested({ email }));
  }

  emailChanged(email: string): void {
    this.store.dispatch(DeliveryActions.emailChanged({ email }));
  }

  changeStatus(): void {
    const order = this.order();
    if (!order) return;
    const deliver = order.status === 'qrcode';
    if (deliver && this.viaToken()) {
      this.updateStatus(order.id, 'deliver');
      return;
    }

    const data: ConfirmDialogData = deliver
      ? { title: 'app.delivery.deliverTitle', message: 'app.delivery.identityQuestion', confirmLabel: 'app.delivery.deliver', cancelLabel: 'app.delivery.cancel' }
      : { title: 'app.delivery.undoTitle', message: 'app.delivery.undoQuestion', confirmLabel: 'app.delivery.undo', cancelLabel: 'app.delivery.cancel' };
    this.dialog.open(ConfirmDialogComponent, { data }).afterClosed().subscribe((confirmed) => {
      if (confirmed === true) this.updateStatus(order.id, deliver ? 'deliver' : 'undo');
    });
  }

  private updateStatus(orderId: string, transition: 'deliver' | 'undo'): void {
    this.store.dispatch(DeliveryActions.statusChangeRequested({ orderId, transition }));
  }
}
