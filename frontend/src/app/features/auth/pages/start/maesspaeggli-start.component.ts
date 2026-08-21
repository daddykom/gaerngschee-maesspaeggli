import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { TranslatePipe } from '@ngx-translate/core';
import { TranslateService } from '@ngx-translate/core';
import { Store } from '@ngrx/store';
import { StartActions } from '../../../../store/start/start.actions';
import { selectStartSendError } from '../../../../store/start/start.feature';
import { InfoBoxComponent } from '../../../../shared/components/info-box/info-box';
import { ControlErrorComponent } from '../../../../shared/components/control-error/control-error';

@Component({
  selector: 'app-maesspaeggli-start',
  standalone: true,
  imports: [
    ReactiveFormsModule,
    MatButtonModule,
    MatCardModule,
    MatFormFieldModule,
    MatIconModule,
    MatInputModule,
    TranslatePipe,
    InfoBoxComponent,
    ControlErrorComponent,
  ],
  templateUrl: './maesspaeggli-start.component.html',
  styleUrl: './maesspaeggli-start.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class MaesspaeggliStartComponent {
  private readonly fb = inject(FormBuilder);
  private readonly store = inject(Store);
  private readonly translate = inject(TranslateService);

  readonly sendError = this.store.selectSignal(selectStartSendError);

  readonly form = this.fb.nonNullable.group({
    email: ['', [Validators.required, Validators.email]],
  });

  submit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.store.dispatch(
      StartActions.submit({
        email: this.form.getRawValue().email,
        language: this.translate.getCurrentLang() ?? 'de',
      }),
    );
  }
}
