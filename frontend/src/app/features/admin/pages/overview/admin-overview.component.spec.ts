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
                definitive: { orderCount: 7, categories: [{ category: 'catA', packageCount: 12 }, { category: 'catB', packageCount: 0 }] },
                provisional: { orderCount: 3, categories: [{ category: 'catA', packageCount: 4 }] },
                recentProvisional: { orderCount: 2, categories: [{ category: 'catA', packageCount: 2 }] },
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
            definitiveTitle: 'Definitive Bestellungen',
            provisionalTitle: 'Provisorische Bestellungen',
            recentProvisionalTitle: 'Provisorische Bestellungen in den letzten {{days}} Tagen',
            orderCount: 'Anzahl Bestellungen',
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
    const rows = fixture.nativeElement.querySelectorAll('.overview-list__row');

    expect(rows).toHaveLength(6);
    expect(rows[0].textContent).toContain('Anzahl Bestellungen');
    expect(rows[0].textContent).toContain('7');
    expect(rows[1].textContent).toContain('Erwachsene ruhig');
    expect(rows[1].textContent).toContain('12');
    expect(fixture.nativeElement.textContent).toContain('Definitive Bestellungen');
    expect(fixture.nativeElement.textContent).toContain('Provisorische Bestellungen');
    expect(fixture.nativeElement.textContent).toContain('Provisorische Bestellungen in den letzten 14 Tagen');
  });
});
