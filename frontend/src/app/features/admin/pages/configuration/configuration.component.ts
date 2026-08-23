import { ChangeDetectionStrategy, Component, effect, inject } from '@angular/core';
import { FormArray, FormControl, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { Store } from '@ngrx/store';
import { TranslatePipe } from '@ngx-translate/core';
import { FrontendConfig } from '../../../../shared/models/frontend-config.model';
import {
  selectFrontendConfigLoading,
  selectFrontendConfigs,
  selectFrontendConfigSaving,
} from '../../../../store/frontend-config/frontend-config.feature';
import { FrontendConfigActions } from '../../../../store/frontend-config/frontend-config.actions';

@Component({
  selector: 'app-admin-configuration',
  imports: [MatButtonModule, MatFormFieldModule, MatInputModule, ReactiveFormsModule, TranslatePipe],
  templateUrl: './configuration.component.html',
  styleUrl: './configuration.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ConfigurationComponent {
  private readonly store = inject(Store);

  readonly configs = this.store.selectSignal(selectFrontendConfigs);
  readonly loading = this.store.selectSignal(selectFrontendConfigLoading);
  readonly saving = this.store.selectSignal(selectFrontendConfigSaving);
  readonly form = new FormGroup({});

  constructor() {
    this.store.dispatch(FrontendConfigActions.load());
    effect(() => this.syncForm(this.configs()));
  }

  isArray(config: FrontendConfig): boolean {
    return Array.isArray(config.value);
  }

  control(config: FrontendConfig): FormControl<string> {
    return this.form.get(config.id) as FormControl<string>;
  }

  arrayControl(config: FrontendConfig): FormArray<FormControl<string>> {
    return this.form.get(config.id) as FormArray<FormControl<string>>;
  }

  addValue(config: FrontendConfig): void {
    this.arrayControl(config).push(new FormControl('', { nonNullable: true }));
  }

  removeValue(config: FrontendConfig, index: number): void {
    this.arrayControl(config).removeAt(index);
  }

  onSubmit(): void {
    if (this.saving()) {
      return;
    }

    const values = this.configs()
      .filter((config) => config.canUpdate)
      .map((config) => ({
        id: config.id,
        value: this.isArray(config)
          ? this.arrayControl(config).getRawValue()
          : this.control(config).getRawValue(),
      }));

    this.store.dispatch(FrontendConfigActions.save({ configs: values }));
  }

  private syncForm(configs: FrontendConfig[]): void {
    const configIds = new Set(configs.map((config) => config.id));
    Object.keys(this.form.controls)
      .filter((id) => !configIds.has(id))
      .forEach((id) => this.form.removeControl(id));

    configs.forEach((config) => {
      const existing = this.form.get(config.id);
      if (existing) {
        return;
      }

      const control = Array.isArray(config.value)
        ? new FormArray(config.value.map((value) => new FormControl(value, { nonNullable: true })))
        : new FormControl(config.value ?? '', { nonNullable: true });
      if (!config.canUpdate) {
        control.disable({ emitEvent: false });
      }
      this.form.addControl(config.id, control);
    });
  }
}
