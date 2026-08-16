import { Component, inject, OnInit } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatInputModule } from '@angular/material/input';
import { ActivatedRoute } from '@angular/router';

@Component({
  selector: 'app-register',
  imports: [MatCardModule, MatInputModule, MatButtonModule, ReactiveFormsModule],
  templateUrl: './register.html',
  styleUrl: './register.scss',
})
export class Register implements OnInit {
  private route = inject(ActivatedRoute);

  registrierungForm = new FormGroup({
    email: new FormControl('', [Validators.required, Validators.email]),
    password: new FormControl({ value: '', disabled: true }, [Validators.required]),
    passwordConfirm: new FormControl({ value: '', disabled: true }, [Validators.required]),
  });

  ngOnInit(): void {
    const email = this.route.snapshot.queryParamMap.get('email');
    if (email) {
      this.registrierungForm.patchValue({ email });
    }
  }

  onSubmit(): void {
    if (this.registrierungForm.valid) {
      console.log('Registrierung:', this.registrierungForm.value);
    }
  }
}
