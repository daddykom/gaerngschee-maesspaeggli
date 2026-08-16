import { Component } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatTabsModule } from '@angular/material/tabs';

@Component({
  selector: 'app-anmeldung',
  imports: [
    MatCardModule,
    MatIconModule,
    MatTabsModule,
    MatInputModule,
    MatButtonModule,
    ReactiveFormsModule,
  ],
  templateUrl: './anmeldung.component.html',
  styleUrl: './anmeldung.component.scss',
})
export class AnmeldungComponent {
  anmeldungForm = new FormGroup({
    email: new FormControl('', [Validators.required, Validators.email]),
    password: new FormControl('', [Validators.required]),
  });

  registrierungForm = new FormGroup({
    email: new FormControl('', [Validators.required, Validators.email]),
    password: new FormControl({ value: '', disabled: true }, [Validators.required]),
    passwordConfirm: new FormControl({ value: '', disabled: true }, [Validators.required]),
  });

  onEmailBlurAnmeldung(email: string | null | undefined): void {
    // TODO: Email gegen DB prüfen
    console.log('Anmeldung Email blur:', email);
  }

  onEmailBlurRegistrierung(email: string | null | undefined): void {
    // TODO: Email gegen DB prüfen
    console.log('Registrierung Email blur:', email);
    // Nach erfolgreicher Prüfung: Passwort-Felder aktivieren
    this.registrierungForm.get('password')?.enable();
    this.registrierungForm.get('passwordConfirm')?.enable();
  }
}
