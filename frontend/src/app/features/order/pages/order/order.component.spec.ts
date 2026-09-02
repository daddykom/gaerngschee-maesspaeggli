import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideTranslateService } from '@ngx-translate/core';
import { provideMockStore } from '@ngrx/store/testing';
import { initialState } from '../../../../store/auth/auth.state';
import { OrderComponent } from './order.component';

describe('OrderComponent', () => {
  let fixture: ComponentFixture<OrderComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [OrderComponent],
      providers: [
        provideTranslateService(),
        provideMockStore({
          initialState: {
            auth: {
              ...initialState,
              fairgateUserExists: true,
              childrenCount: 2,
              adultsCount: 2,
              salutation: 'Hallo',
            },
          },
        }),
      ],
    }).compileComponents();

    fixture = TestBed.createComponent(OrderComponent);
    fixture.detectChanges();
  });

  it('renders the client-specific Fairgate summary', () => {
    const text = fixture.nativeElement.textContent;

    expect(text).toContain('Hallo');
    expect(text).toContain('2');
    expect(text).toContain('app.order.intro');
    expect(text).toContain('app.order.categories.title');
  });
});
