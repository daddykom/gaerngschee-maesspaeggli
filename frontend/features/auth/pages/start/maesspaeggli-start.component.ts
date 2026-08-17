import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';

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
  ],
  templateUrl: './maesspaeggli-start.component.html',
  styleUrl: './maesspaeggli-start.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class MaesspaeggliStartComponent {
  private readonly fb = inject(FormBuilder);

  readonly form = this.fb.nonNullable.group({
    email: ['', [Validators.required, Validators.email]],
  });

  submit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    // Hier euren bestehenden Flow / Store-Dispatch aufrufen.
    // Wichtig: Die UI zeigt nicht an, ob die Adresse bei Fairgate
    // oder im Mässpäggli-System bereits existiert.
    console.log(this.form.getRawValue());
  }
}
