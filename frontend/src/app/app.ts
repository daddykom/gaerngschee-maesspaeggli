import { Component, computed, inject } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatDividerModule } from '@angular/material/divider';
import { MatIconModule } from '@angular/material/icon';
import { MatMenuModule } from '@angular/material/menu';
import { MatToolbarModule } from '@angular/material/toolbar';
import { ActivatedRoute, NavigationEnd, Router, RouterLink, RouterLinkActive, RouterOutlet } from '@angular/router';
import { Store } from '@ngrx/store';
import { TranslatePipe } from '@ngx-translate/core';
import { toSignal } from '@angular/core/rxjs-interop';
import { filter, startWith } from 'rxjs';
import { InfoBoxComponent } from './shared/components/info-box/info-box';
import { AuthActions } from './store/auth/auth.actions';
import { selectAuthErrorCode, selectAuthGroup, selectAuthUserId } from './store/auth/auth.feature';

@Component({
  imports: [
    RouterOutlet,
    RouterLink,
    RouterLinkActive,
    MatToolbarModule,
    MatButtonModule,
    MatDividerModule,
    MatMenuModule,
    MatIconModule,
    TranslatePipe,
    InfoBoxComponent,
  ],
  selector: 'app-root',
  templateUrl: './app.html',
  styleUrl: './app.scss',
})
export class App {
  protected title = 'Gaerngschee';
  private readonly store = inject(Store);
  private readonly router = inject(Router);
  private readonly activatedRoute = inject(ActivatedRoute);

  private readonly navigation = toSignal(
    this.router.events.pipe(
      filter((event) => event instanceof NavigationEnd),
      startWith(null),
    ),
    { initialValue: null },
  );

  readonly authErrorCode = this.store.selectSignal(selectAuthErrorCode);
  readonly authGroup = this.store.selectSignal(selectAuthGroup);
  readonly authUserId = this.store.selectSignal(selectAuthUserId);
  readonly isAdmin = computed(() => this.authGroup() === 'admin');

  logout(): void {
    this.store.dispatch(AuthActions.logout());
  }

  readonly isAdminRoute = computed(() => {
    this.navigation();
    return this.router.url.startsWith('/admin');
  });

  readonly pageTitleKey = computed(() => {
    this.navigation();

    let route = this.activatedRoute;
    while (route.firstChild) {
      route = route.firstChild;
    }

    return route.snapshot.data['pageTitle'] as string | undefined;
  });

  readonly pageHeaderLayout = computed(() => {
    this.navigation();

    let route = this.activatedRoute;
    while (route.firstChild) {
      route = route.firstChild;
    }

    return route.snapshot.data['pageHeaderLayout'] as string | undefined;
  });
}
