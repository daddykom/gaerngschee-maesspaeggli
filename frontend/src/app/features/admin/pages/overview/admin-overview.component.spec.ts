import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideMockStore } from '@ngrx/store/testing';
import { TranslateService, provideTranslateService } from '@ngx-translate/core';
import { AdminOverviewComponent } from './admin-overview.component';

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
                categories: [{ category: 'catA', provisional: 4, recentProvisional: 2, definitive: 12, toDeliver: 3, qrcode: 1, delivered: 0 }, { category: 'catB', provisional: 0, recentProvisional: 0, definitive: 0, toDeliver: 0, qrcode: 0, delivered: 0 }],
              },
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
            provisional: 'Provisorisch',
            recentProvisional: 'Provisorisch letzte {{days}} Tage',
            definitive: 'Definitiv',
            toDeliver: 'Auszuliefern',
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

    expect(rows).toHaveLength(1);
    expect(rows[0].textContent).toContain('Erwachsene ruhig');
    expect(rows[0].textContent).toContain('12');
    expect(fixture.nativeElement.textContent).not.toContain('catB');
  });
});
