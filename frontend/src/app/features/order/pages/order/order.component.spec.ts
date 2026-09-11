import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideTranslateService } from '@ngx-translate/core';
import { MockStore, provideMockStore } from '@ngrx/store/testing';
import { initialState } from '../../../../store/auth/auth.state';
import { initialState as frontendConfigInitialState } from '../../../../store/frontend-config/frontend-config.state';
import { initialState as orderInitialState } from '../../../../store/order/order.state';
import { OrderComponent } from './order.component';

describe('OrderComponent', () => {
  let fixture: ComponentFixture<OrderComponent>;
  let store: MockStore;

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
            frontendConfig: frontendConfigInitialState,
            order: orderInitialState,
          },
        }),
      ],
    }).compileComponents();

    fixture = TestBed.createComponent(OrderComponent);
    store = TestBed.inject(MockStore);
    fixture.detectChanges();
  });

  it('renders the client-specific Fairgate summary', () => {
    const text = fixture.nativeElement.textContent;

    expect(text).toContain('Hallo');
    expect(text).toContain('2');
    expect(text).toContain('app.order.intro');
    expect(text).toContain('app.order.categories.title');
  });

  it('requires a category for every person before continuing', () => {
    const component = fixture.componentInstance;

    expect(component.form().valid()).toBe(false);

    component.onSubmit();

    expect(component.childField(0)().touched()).toBe(true);
  });

  it('accepts absolute HTTPS Fairgate URLs', () => {
    store.setState({
      auth: { ...initialState, fairgateUserExists: false },
      frontendConfig: {
        ...frontendConfigInitialState,
        publicConfigs: [{ variableName: 'fairgate_url', value: 'https://fairgate.example/login' }],
      },
      order: orderInitialState,
    });
    fixture.detectChanges();

    expect(fixture.componentInstance.fairgateUrl()).toBe('https://fairgate.example/login');
  });

  it.each(['http://fairgate.example', 'javascript:alert(1)', 'https://user:password@fairgate.example'])
    ('rejects unsafe Fairgate URL: %s', (value) => {
      store.setState({
        auth: { ...initialState, fairgateUserExists: false },
        frontendConfig: {
          ...frontendConfigInitialState,
          publicConfigs: [{ variableName: 'fairgate_url', value }],
        },
        order: orderInitialState,
      });
      fixture.detectChanges();

      expect(fixture.componentInstance.fairgateUrl()).toBeNull();
    });

  it('shows only children when the household has children', () => {
    const personGroups = fixture.nativeElement.querySelectorAll('.person-group');
    const labels = fixture.nativeElement.querySelectorAll('mat-label');

    expect(personGroups).toHaveLength(1);
    expect(personGroups[0].classList).toContain('gl-stack');
    expect(labels[0].textContent).toContain('app.order.categories.child 1');
    expect(labels[0].textContent).not.toContain('app.order.categories.category');
    expect(componentModel(fixture).adults).toEqual([]);
  });

  it('shows adults and only adult categories when there are no children', () => {
    const component = fixture.componentInstance;
    component.model.set({ adultsCount: 2, childrenCount: 0, adults: ['', ''], children: [] });
    fixture.detectChanges();

    expect(fixture.nativeElement.querySelectorAll('.person-group')).toHaveLength(1);
    expect(fixture.nativeElement.querySelectorAll('mat-label')[0].textContent)
      .toContain('app.order.categories.adult 1');
    expect(component.adultCategories).toEqual(['catA', 'catB']);
    expect(component.childCategories).toEqual(['catC', 'catD', 'catE', 'catF', 'catG']);
  });
});

function componentModel(fixture: ComponentFixture<OrderComponent>) {
  return fixture.componentInstance.model();
}
