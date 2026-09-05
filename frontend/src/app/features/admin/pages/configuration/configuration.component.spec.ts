import { ComponentFixture, TestBed } from '@angular/core/testing';
import { MockStore, provideMockStore } from '@ngrx/store/testing';
import { provideTranslateService } from '@ngx-translate/core';
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
    jest.spyOn(store, 'dispatch');
    fixture = TestBed.createComponent(ConfigurationComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
    await fixture.whenStable();
  });

  it('loads and displays scalar and array configuration values', () => {
    expect(fixture.nativeElement.textContent).toContain('SCALAR_CONFIG');
    expect(fixture.nativeElement.textContent).toContain('ARRAY_CONFIG');
    expect(component.model()[configs[0].id]).toBe('120');
    expect(component.model()[configs[1].id]).toEqual(['A', 'B']);
  });

  it('dispatches load on creation', () => {
    expect(store.dispatch).toHaveBeenCalledWith(FrontendConfigActions.load());
  });

  it('disables fields without update permission', () => {
    expect(fixture.nativeElement.querySelector('input[disabled]')).toBeTruthy();
    expect(fixture.nativeElement.querySelector('.configuration-field--readonly')).toBeTruthy();
  });

  it('adds and removes array values', () => {
    component.addValue(configs[1]);
    expect(component.values(configs[1])).toHaveLength(3);

    component.removeValue(configs[1], 1);
    expect(component.model()[configs[1].id]).toEqual(['A', '']);
  });

  it('dispatches editable scalar and array values when saving', () => {
    const dispatch = jest.spyOn(store, 'dispatch');
    component.model.update((model) => ({
      ...model,
      [configs[0].id]: '240',
      [configs[1].id]: ['C', 'B'],
    }));

    component.onSubmit();

    expect(dispatch).toHaveBeenCalledWith(
      FrontendConfigActions.save({
        configs: [
          { id: 'scalar-config', value: '240' },
          { id: 'array-config', value: ['C', 'B'] },
        ],
      }),
    );
  });

  it('does not save while a save is already in progress', () => {
    store.setState({ frontendConfig: { configs, loading: false, saving: true } });
    const dispatch = jest.spyOn(store, 'dispatch');

    component.onSubmit();

    expect(dispatch).not.toHaveBeenCalledWith(
      expect.objectContaining({ type: FrontendConfigActions.save.type }),
    );
  });

  it('shows the loading state and does not render the form fields', () => {
    store.setState({ frontendConfig: { configs: [], loading: true, saving: false } });
    fixture.detectChanges();

    expect(fixture.nativeElement.textContent).toContain('app.admin.configuration.loading');
    expect(fixture.nativeElement.querySelectorAll('fieldset')).toHaveLength(0);
    expect(fixture.nativeElement.querySelector('button[type="submit"]')).toBeNull();
  });

  it('renders the empty state and supports null scalar values', async () => {
    component.model.set({});
    store.setState({
      frontendConfig: {
        configs: [{ ...configs[0], value: null }],
        loading: false,
        saving: false,
      },
    });
    fixture.detectChanges();
    await fixture.whenStable();

    expect(component.model()[configs[0].id]).toBe('');

    store.setState({ frontendConfig: { configs: [], loading: false, saving: false } });
    fixture.detectChanges();
    expect(fixture.nativeElement.textContent).toContain('app.admin.configuration.empty');
  });

  it('removes controls for configurations no longer in the store', async () => {
    store.setState({ frontendConfig: { configs: [configs[0]], loading: false, saving: false } });
    fixture.detectChanges();
    await fixture.whenStable();

    expect(component.model()[configs[1].id]).toBeUndefined();
    expect(component.model()[configs[0].id]).toBe('120');
  });
});
