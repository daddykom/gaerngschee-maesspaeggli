import { AbstractControl } from '@angular/forms';
import { Component, computed, effect, input, signal } from '@angular/core';
import { TranslatePipe } from '@ngx-translate/core';

@Component({
  selector: 'app-control-error',
  imports: [TranslatePipe],
  templateUrl: './control-error.html',
  styleUrl: './control-error.scss',
})
export class ControlErrorComponent {
  control = input.required<AbstractControl>();
  translationPrefix = input.required<string>();

  private readonly statusVersion = signal(0);

  readonly firstErrorKey = computed(() => {
    this.statusVersion();
    const control = this.control();

    if (!control.touched || !control.errors) {
      return null;
    }

    return Object.keys(control.errors)[0] ?? null;
  });

  readonly errorTranslationKey = computed(() => {
    const errorKey = this.firstErrorKey();
    return errorKey ? `${this.translationPrefix()}.${errorKey}` : null;
  });

  constructor() {
    effect((onCleanup) => {
      const control = this.control();
      const subscription = control.events.subscribe(() => {
        this.statusVersion.update((version) => version + 1);
      });

      onCleanup(() => subscription.unsubscribe());
    });
  }
}
