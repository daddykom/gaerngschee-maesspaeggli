import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideTranslateService } from '@ngx-translate/core';
import { provideMockStore } from '@ngrx/store/testing';
import { initialState as authInitialState } from '../../../../store/auth/auth.state';
import { initialState as frontendConfigInitialState } from '../../../../store/frontend-config/frontend-config.state';
import { OrderSummaryComponent } from './order-summary.component';

describe('OrderSummaryComponent', () => {
  let fixture: ComponentFixture<OrderSummaryComponent>;

  const createComponent = (fairgateUserExists: boolean | null): void => {
    TestBed.configureTestingModule({
      imports: [OrderSummaryComponent],
      providers: [
        provideTranslateService(),
        provideMockStore({
          initialState: {
            auth: { ...authInitialState, fairgateUserExists },
            frontendConfig: { ...frontendConfigInitialState, publicConfigs: [{ variableName: 'campaign_year', value: '2026' }] },
            order: {
              status: 'loaded',
              order: null,
              form: { adultsCount: 1, childrenCount: 0, adults: ['catA'], children: [] },
            },
          },
        }),
      ],
    });
    fixture = TestBed.createComponent(OrderSummaryComponent);
    fixture.detectChanges();
  };

  it('shows a definitive status when the client exists in Fairgate', () => {
    createComponent(true);

    expect(fixture.componentInstance.status()).toBe('definitive');
  });

  it('shows a provisional status when the client does not exist in Fairgate', () => {
    createComponent(false);

    expect(fixture.componentInstance.status()).toBe('provisional');
  });
});
