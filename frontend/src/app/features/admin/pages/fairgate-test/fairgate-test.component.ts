import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { JsonPipe } from '@angular/common';
import { Store } from '@ngrx/store';
import { TranslatePipe } from '@ngx-translate/core';
import {
  selectFairgateTestErrorCode,
  selectFairgateTestLoading,
  selectFairgateTestResult,
} from '../../../../store/fairgate-test/fairgate-test.feature';
import { FairgateTestActions } from '../../../../store/fairgate-test/fairgate-test.actions';

@Component({
  selector: 'app-admin-fairgate-test',
  imports: [JsonPipe, MatButtonModule, TranslatePipe],
  templateUrl: './fairgate-test.component.html',
  styleUrl: './fairgate-test.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class FairgateTestComponent {
  private readonly store = inject(Store);

  readonly result = this.store.selectSignal(selectFairgateTestResult);
  readonly loading = this.store.selectSignal(selectFairgateTestLoading);
  readonly errorCode = this.store.selectSignal(selectFairgateTestErrorCode);

  runTest(): void {
    this.store.dispatch(FairgateTestActions.test());
  }
}
