import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideMockStore } from '@ngrx/store/testing';
import { AdminOverviewComponent } from './admin-overview.component';

describe('AdminOverviewComponent', () => {
  let component: AdminOverviewComponent;
  let fixture: ComponentFixture<AdminOverviewComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AdminOverviewComponent],
      providers: [
        provideMockStore({
          initialState: {
            adminOverview: {
              numbOrders: 7,
              kategories: [{ kategoryId: 'standard', numbPackages: 12 }],
            },
          },
        }),
      ],
    }).compileComponents();

    fixture = TestBed.createComponent(AdminOverviewComponent);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('displays orders and category package counts from the store', () => {
    const rows = fixture.nativeElement.querySelectorAll('.overview-list__row');

    expect(rows).toHaveLength(2);
    expect(rows[0].textContent).toContain('Anzahl Bestellungen');
    expect(rows[0].textContent).toContain('7');
    expect(rows[1].textContent).toContain('Kategorie standard');
    expect(rows[1].textContent).toContain('12');
  });
});
