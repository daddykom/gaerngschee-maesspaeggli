import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { TranslatePipe } from '@ngx-translate/core';
import { Store } from '@ngrx/store';
import {
  selectAuthAdultsCount,
  selectAuthChildrenCount,
  selectAuthFairgateUserExists,
  selectAuthSalutation,
} from '../../../../store/auth/auth.feature';

@Component({
  selector: 'app-order',
  standalone: true,
  imports: [TranslatePipe],
  templateUrl: './order.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class OrderComponent {
  private readonly store = inject(Store);

  readonly fairgateUserExists = this.store.selectSignal(selectAuthFairgateUserExists);
  readonly childrenCount = this.store.selectSignal(selectAuthChildrenCount);
  readonly adultsCount = this.store.selectSignal(selectAuthAdultsCount);
  readonly salutation = this.store.selectSignal(selectAuthSalutation);
}
