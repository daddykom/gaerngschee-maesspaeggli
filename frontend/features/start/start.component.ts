import { Component, inject, signal } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatCheckbox } from '@angular/material/checkbox';
import { MatInputModule } from '@angular/material/input';
import { Router } from '@angular/router';
import { InfoBoxComponent } from '../../src/app/shared/components/info-box/info-box';

@Component({
  selector: 'app-start',
  imports: [
    MatCardModule,
    MatInputModule,
    MatButtonModule,
    MatCheckbox,
    ReactiveFormsModule,
    InfoBoxComponent,
  ],
  templateUrl: './start.component.html',
  styleUrl: './start.component.scss',
})
export class StartComponent {
  private router = inject(Router);

  anmeldungForm = new FormGroup({
    email: new FormControl('', [Validators.required, Validators.email]),
    accept: new FormControl('', [Validators.required]),
  });

  registerClosed = signal(false);

  onSubmit(): void {
    if (this.anmeldungForm.valid) {
      const email = this.anmeldungForm.get('email')?.value;
      this.router.navigate(['/login'], { queryParams: { email } });
    }
  }
}
