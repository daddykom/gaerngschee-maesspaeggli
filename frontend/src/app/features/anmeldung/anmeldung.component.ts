import { Component } from '@angular/core';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-anmeldung',
  imports: [MatCardModule, MatIconModule],
  templateUrl: './anmeldung.component.html',
  styleUrl: './anmeldung.component.scss',
})
export class AnmeldungComponent {}
