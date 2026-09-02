import { ChangeDetectionStrategy, Component, inject, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { TranslatePipe } from '@ngx-translate/core';
import { Store } from '@ngrx/store';
import {
  selectAuthRegistrationLoginLoading,
} from '../../../../store/auth/auth.feature';
import { AuthActions } from '../../../../store/auth/auth.actions';

@Component({
  selector: 'app-client-login',
  standalone: true,
  imports: [TranslatePipe],
  templateUrl: './client-login.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ClientLoginComponent implements OnInit {
  private readonly route = inject(ActivatedRoute);
  private readonly store = inject(Store);

  readonly loading = this.store.selectSignal(selectAuthRegistrationLoginLoading);
  ngOnInit(): void {
    const token = this.route.snapshot.queryParamMap.get('token');
    if (token) {
      this.store.dispatch(AuthActions.registrationLogin({ token }));
    } else {
      this.store.dispatch(AuthActions.registrationLoginFailure({ errorCode: 'INVALID_REGISTRATION_TOKEN' }));
    }
  }
}
