import { ChangeDetectionStrategy, Component, computed, inject, signal } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatDialog } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { TranslatePipe } from '@ngx-translate/core';
import { ClientOrder, OrderCategory } from '../../../shared/models/order.model';
import { DeliveryService } from '../../../shared/services/delivery.service';
import { ConfirmDialogComponent, ConfirmDialogData } from '../../../shared/components/confirm-dialog/confirm-dialog';

@Component({
  selector: 'app-delivery',
  imports: [MatButtonModule, MatFormFieldModule, MatInputModule, TranslatePipe],
  templateUrl: './delivery.html',
  styleUrl: './delivery.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class Delivery {
  private readonly service = inject(DeliveryService);
  private readonly dialog = inject(MatDialog);
  private readonly route = inject(ActivatedRoute);

  readonly email = signal('');
  readonly order = signal<ClientOrder | null>(null);
  readonly viaToken = signal(false);
  readonly loading = signal(false);
  readonly error = signal(false);
  readonly categories = computed(() => {
    const items = this.order()?.items ?? [];
    return (['catA', 'catB', 'catC', 'catD', 'catE', 'catF', 'catG'] as OrderCategory[])
      .map((category) => ({ category, quantity: items.filter((item) => item.category === category).reduce((sum, item) => sum + item.quantity, 0) }))
      .filter((item) => item.quantity > 0);
  });

  constructor() {
    const token = this.route.snapshot.queryParamMap.get('token');
    if (token) this.load({ token });
  }

  search(): void {
    const email = this.email().trim();
    if (email) this.load({ email });
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

  private load(search: { email?: string; token?: string }): void {
    this.loading.set(true);
    this.error.set(false);
    this.service.getOrder(search).subscribe({
      next: (response) => { this.order.set(response.order); this.viaToken.set(response.viaToken); this.loading.set(false); },
      error: () => { this.order.set(null); this.loading.set(false); this.error.set(true); },
    });
  }

  private updateStatus(orderId: string, transition: 'deliver' | 'undo'): void {
    const request = transition === 'deliver' ? this.service.deliver(orderId) : this.service.undo(orderId);
    request.subscribe({
      next: () => this.load(this.viaToken() ? { token: this.route.snapshot.queryParamMap.get('token') ?? undefined } : { email: this.email().trim() }),
      error: () => this.error.set(true),
    });
  }
}
