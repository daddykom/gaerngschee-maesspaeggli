import { ComponentFixture, TestBed } from '@angular/core/testing';
import { MockStore, provideMockStore } from '@ngrx/store/testing';
import { TranslateService, provideTranslateService } from '@ngx-translate/core';
import { AdminOverviewComponent } from './admin-overview.component';
import { initialState as frontendConfigInitialState } from '../../../../store/frontend-config/frontend-config.state';

describe('AdminOverviewComponent', () => {
  let component: AdminOverviewComponent;
  let fixture: ComponentFixture<AdminOverviewComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AdminOverviewComponent],
      providers: [
        provideTranslateService(),
        provideMockStore({
          initialState: {
            adminOverview: {
              status: 'loaded',
              overview: {
                year: 2026,
                recentDays: 14,
                orders: {
                  provisional: 1,
                  recentProvisional: 1,
                  definitive: 1,
                  toDeliver: 1,
                  qrcode: 1,
                  delivered: 0,
                },
                categories: [
                  {
                    category: 'catA',
                    provisional: 4,
                    recentProvisional: 2,
                    definitive: 12,
                    toDeliver: 3,
                    qrcode: 1,
                    delivered: 0,
                  },
                  {
                    category: 'catB',
                    provisional: 0,
                    recentProvisional: 0,
                    definitive: 0,
                    toDeliver: 0,
                    qrcode: 0,
                    delivered: 0,
                  },
                ],
              },
            },
            frontendConfig: {
              ...frontendConfigInitialState,
              publicConfigs: [
                { variableName: 'campaign_start_date', value: '2999-01-01' },
                { variableName: 'campaign_end_date', value: '2999-12-31' },
              ],
            },
          },
        }),
      ],
    }).compileComponents();

    fixture = TestBed.createComponent(AdminOverviewComponent);
    component = fixture.componentInstance;
    const translate = TestBed.inject(TranslateService);
    translate.setTranslation('de', {
      app: {
        admin: {
          overview: {
            category: 'Kategorie',
            orders: 'Bestellungen',
            provisional: 'Provisorisch',
            recentProvisional: 'Provisorisch letzte {{days}} Tage',
            definitive: 'Definitiv',
            toDeliver: 'Bereit auszuliefern',
            qrcode: 'QR-Code versandt',
            delivered: 'Ausgeliefert',
            tableCaption: 'Bestellungen nach Kategorie und Status',
            empty: 'Keine Bestellungen vorhanden.',
            deliver: 'Bestellungen ausliefern',
            print: 'Drucken',
            printDate: 'Druckdatum',
            deliverTitle: 'Bestellungen ausliefern',
            deliverQuestion: 'Willst du wirklich alle definitiven Bestellungen ausliefern?',
            deliverConfirm: 'Ausliefern',
            deliverCancel: 'Abbrechen',
            legend: {
              title: 'Legende',
              provisional: 'Provisorische Bestellungen, noch nicht bei Fairgate vorhanden',
              recentProvisional: 'Provisorische Bestellungen der letzten {{ days }} Tage',
              definitive: 'Definitive Bestellungen',
              toDeliver: 'Zur Auslieferung gekennzeichnet',
              qrcode: 'QR-Code per E-Mail versandt',
              delivered: 'Ausgeliefert beziehungsweise vom Bezüger abgeholt',
            },
          },
        },
        order: { categories: { options: { catA: 'Erwachsene ruhig' } } },
      },
    });
    translate.use('de');
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('displays orders and category package counts from the store', () => {
    const rows = fixture.nativeElement.querySelectorAll('tbody tr');

    expect(rows).toHaveLength(2);
    expect(rows[0].textContent).toContain('Bestellungen');
    expect(rows[1].textContent).toContain('Erwachsene ruhig');
    expect(rows[1].textContent).toContain('12');
    expect(fixture.nativeElement.textContent).not.toContain('catB');
  });

  it('shows the delivery action outside an active campaign', () => {
    expect(fixture.nativeElement.querySelectorAll('.overview-actions button')).toHaveLength(2);
  });

  it('hides the delivery action during an active campaign', () => {
    const store = TestBed.inject(MockStore);
    store.setState({
      adminOverview: {
        status: 'loaded',
        overview: {
          year: 2026,
          recentDays: 14,
          orders: { provisional: 1, recentProvisional: 1, definitive: 1, toDeliver: 1, qrcode: 1, delivered: 0 },
          categories: [],
        },
      },
      frontendConfig: {
        ...frontendConfigInitialState,
        publicConfigs: [
          { variableName: 'campaign_start_date', value: '2000-01-01' },
          { variableName: 'campaign_end_date', value: '2999-12-31' },
        ],
      },
    });
    fixture.detectChanges();

    expect(fixture.nativeElement.querySelectorAll('.overview-actions button')).toHaveLength(1);
  });

  it('renders the column explanations below the table without tooltip attributes', () => {
    expect(fixture.nativeElement.querySelector('.overview-legend')).toBeTruthy();
    expect(fixture.nativeElement.querySelector('.overview-legend')?.textContent).toContain('Provisorische Bestellungen, noch nicht bei Fairgate vorhanden');
    expect(fixture.nativeElement.querySelectorAll('[data-tooltip]')).toHaveLength(0);
  });
});
