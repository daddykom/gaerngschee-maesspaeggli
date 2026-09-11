import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideTranslateService } from '@ngx-translate/core';
import { MockStore, provideMockStore } from '@ngrx/store/testing';
import { RouterTestingModule } from '@angular/router/testing';
import { HomeComponent } from './home.component';

describe('HomeComponent', () => {
  let fixture: ComponentFixture<HomeComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [HomeComponent, RouterTestingModule],
      providers: [
        provideTranslateService(),
        provideMockStore({
          initialState: {
            frontendConfig: {
              publicConfigs: [
                { variableName: 'campaign_year', value: '2026' },
                { variableName: 'donation_url', value: 'https://donate.example/maesspaeggli' },
                { variableName: 'campaign_start_date', value: '2000-01-01' },
                { variableName: 'campaign_end_date', value: '2999-01-01' },
              ],
            },
          },
        }),
      ],
    }).compileComponents();

    fixture = TestBed.createComponent(HomeComponent);
    fixture.detectChanges();
  });

  it('reads the campaign year and donation URL from public configuration', () => {
    const component = fixture.componentInstance;

    expect(component.campaignYear()).toBe('2026');
    expect(component.donationUrl()).toBe('https://donate.example/maesspaeggli');
  });

  it('does not expose an insecure donation URL', () => {
    const store = TestBed.inject(MockStore);
    store.setState({ frontendConfig: { publicConfigs: [{ variableName: 'campaign_year', value: '2026' }, { variableName: 'donation_url', value: 'http://donate.example' }] } });
    fixture.detectChanges();

    expect(fixture.componentInstance.donationUrl()).toBeNull();
  });

  it('does not start the campaign before the configured start date', () => {
    const store = TestBed.inject(MockStore);
    store.setState({ frontendConfig: { publicConfigs: [
      { variableName: 'campaign_year', value: '2026' },
      { variableName: 'campaign_start_date', value: '2999-01-01' },
      { variableName: 'campaign_end_date', value: '2999-12-31' },
    ] } });
    fixture.detectChanges();

    expect(fixture.componentInstance.campaignStatus()).toBe('not_started');
  });
});
