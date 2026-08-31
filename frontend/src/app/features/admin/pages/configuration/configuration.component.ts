import {
  ChangeDetectionStrategy,
  Component,
  effect,
  inject,
  signal,
  untracked,
} from '@angular/core';
import { FieldTree, form, FormField } from '@angular/forms/signals';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { Store } from '@ngrx/store';
import { TranslatePipe } from '@ngx-translate/core';
import { FrontendConfig } from '../../../../shared/models/frontend-config.model';
import { FrontendConfigActions } from '../../../../store/frontend-config/frontend-config.actions';
import {
  selectFrontendConfigLoading,
  selectFrontendConfigs,
  selectFrontendConfigSaving,
} from '../../../../store/frontend-config/frontend-config.feature';

@Component({
  selector: 'app-admin-configuration',
  imports: [MatButtonModule, MatFormFieldModule, MatInputModule, FormField, TranslatePipe],
  templateUrl: './configuration.component.html',
  styleUrl: './configuration.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ConfigurationComponent {
  private readonly store = inject(Store);

  readonly configs = this.store.selectSignal(selectFrontendConfigs);
  readonly loading = this.store.selectSignal(selectFrontendConfigLoading);
  readonly saving = this.store.selectSignal(selectFrontendConfigSaving);
  readonly model = signal<Record<string, string | string[]>>({});
  readonly form = form(this.model);

  constructor() {
    this.store.dispatch(FrontendConfigActions.load());
    effect(() => this.syncForm(this.configs()));
  }

  isArray(config: FrontendConfig): boolean {
    return Array.isArray(config.value);
  }

  values(config: FrontendConfig): string[] {
    return this.model()[config.id] as string[];
  }

  field(config: FrontendConfig): FieldTree<string> {
    return this.form[config.id] as unknown as FieldTree<string>;
  }

  arrayField(config: FrontendConfig, index: number): FieldTree<string> {
    return (this.form[config.id] as unknown as FieldTree<string[]>)[index] as FieldTree<string>;
  }

  addValue(config: FrontendConfig): void {
    this.model.update((model) => ({
      ...model,
      [config.id]: [...(model[config.id] as string[]), ''],
    }));
  }

  removeValue(config: FrontendConfig, index: number): void {
    this.model.update((model) => ({
      ...model,
      [config.id]: (model[config.id] as string[]).filter((_, valueIndex) => valueIndex !== index),
    }));
  }

  onSubmit(): void {
    if (this.saving()) {
      return;
    }

    const values = this.configs()
      .filter((config) => config.canUpdate)
      .map((config) => ({
        id: config.id,
        value: this.model()[config.id],
      }));

    this.store.dispatch(FrontendConfigActions.save({ configs: values }));
  }

  private syncForm(configs: FrontendConfig[]): void {
    const currentModel = untracked(this.model);
    const values = configs.reduce<Record<string, string | string[]>>(
      (model, config) => ({
        ...model,
        [config.id]:
          currentModel[config.id] ??
          (Array.isArray(config.value) ? [...config.value] : (config.value ?? '')),
      }),
      {},
    );
    this.model.set(values);
  }
}
