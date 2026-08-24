import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideTranslateService } from '@ngx-translate/core';
import { MockStore, provideMockStore } from '@ngrx/store/testing';
import { FrontendConfig } from '../../../../shared/models/frontend-config.model';
import { FrontendConfigActions } from '../../../../store/frontend-config/frontend-config.actions';
import { ConfigurationComponent } from './configuration.component';

describe('ConfigurationComponent', () => {
  let component: ConfigurationComponent;
  let fixture: ComponentFixture<ConfigurationComponent>;
  let store: MockStore;

  const configs: FrontendConfig[] = [
    {
      id: 'scalar-config',
      variableName: 'SCALAR_CONFIG',
      value: '120',
      description: 'Einzelwert',
      accessGroup: ['admin'],
      updateGroup: ['admin'],
      label: 'Einzelwert',
      canUpdate: true,
      createdAt: new Date(),
      updatedAt: new Date(),
    },
    {
      id: 'array-config',
      variableName: 'ARRAY_CONFIG',
      value: ['A', 'B'],
      description: 'Mehrere Werte',
      accessGroup: ['admin'],
      updateGroup: ['admin'],
      label: 'Mehrere Werte',
      canUpdate: true,
      createdAt: new Date(),
      updatedAt: new Date(),
    },
    {
      id: 'readonly-config',
      variableName: 'READONLY_CONFIG',
      value: 'nur lesen',
      description: '',
      accessGroup: ['admin'],
      updateGroup: [],
      label: 'Nur lesen',
      canUpdate: false,
      createdAt: new Date(),
      updatedAt: new Date(),
    },
  ];

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ConfigurationComponent],
      providers: [
        provideTranslateService(),
        provideMockStore({
          initialState: {
            frontendConfig: {
              configs,
              loading: false,
              saving: false,
            },
          },
        }),
      ],
    }).compileComponents();

    store = TestBed.inject(MockStore);
    fixture = TestBed.createComponent(ConfigurationComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
    await fixture.whenStable();
  });

  it('loads and displays scalar and array configuration values', () => {
    expect(fixture.nativeElement.textContent).toContain('SCALAR_CONFIG');
    expect(fixture.nativeElement.textContent).toContain('ARRAY_CONFIG');
    expect(component.control(configs[0]).value).toBe('120');
    expect(component.arrayControl(configs[1]).getRawValue()).toEqual(['A', 'B']);
  });

  it('disables fields without update permission', () => {
    expect(component.control(configs[2]).disabled).toBe(true);
    expect(fixture.nativeElement.querySelector('.configuration-field--readonly')).toBeTruthy();
  });

  it('adds and removes array values', () => {
    component.addValue(configs[1]);
    expect(component.arrayControl(configs[1]).length).toBe(3);

    component.removeValue(configs[1], 1);
    expect(component.arrayControl(configs[1]).getRawValue()).toEqual(['A', '']);
  });

  it('dispatches editable scalar and array values when saving', () => {
    const dispatch = jest.spyOn(store, 'dispatch');
    component.control(configs[0]).setValue('240');
    component.arrayControl(configs[1]).at(0).setValue('C');

    component.onSubmit();

    expect(dispatch).toHaveBeenCalledWith(FrontendConfigActions.save({
      configs: [
        { id: 'scalar-config', value: '240' },
        { id: 'array-config', value: ['C', 'B'] },
      ],
    }));
  });
});
