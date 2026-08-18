import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { TranslatePipe } from '@ngx-translate/core';
import { Store } from '@ngrx/store';
import { AnmeldungActions } from '../../../../store/anmeldung/anmeldung.actions';
import { selectAnmeldungSendError } from '../../../../store/anmeldung/anmeldung.feature';
import { InfoBoxComponent } from '../../../../shared/components/info-box/info-box';

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
  ],
  templateUrl: './maesspaeggli-start.component.html',
  styleUrl: './maesspaeggli-start.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class MaesspaeggliStartComponent {
  private readonly fb = inject(FormBuilder);
  private readonly store = inject(Store);

  readonly sendError = this.store.selectSignal(selectAnmeldungSendError);

  readonly form = this.fb.nonNullable.group({
    email: ['', [Validators.required, Validators.email]],
  });

  submit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.store.dispatch(AnmeldungActions.submit({ email: this.form.getRawValue().email }));
  }
}
