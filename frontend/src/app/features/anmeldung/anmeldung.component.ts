import { Component } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatCheckbox } from '@angular/material/checkbox';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { Router } from '@angular/router';
import { InfoBoxComponent } from '../../shared/components/info-box/info-box';

@Component({
  selector: 'app-anmeldung',
  imports: [
    MatCardModule,
    MatIconModule,
    MatInputModule,
    MatButtonModule,
    MatCheckbox,
    ReactiveFormsModule,
    InfoBoxComponent,
  ],
  templateUrl: './anmeldung.component.html',
  styleUrl: './anmeldung.component.scss',
})
export class AnmeldungComponent {
  anmeldungForm = new FormGroup({
    email: new FormControl('', [Validators.required, Validators.email]),
    accept: new FormControl('', [Validators.required]),
  });

  constructor(private router: Router) {}

  onSubmit(): void {
    if (this.anmeldungForm.valid) {
      const email = this.anmeldungForm.get('email')?.value;
      this.router.navigate(['/login'], { queryParams: { email } });
    }
  }
}
