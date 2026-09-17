import { Component } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { RouterLink } from '@angular/router';
import { TranslatePipe } from '@ngx-translate/core';

@Component({
  selector: 'app-start-success',
  imports: [MatButtonModule, RouterLink, TranslatePipe],
  templateUrl: './start-success.html',
  styleUrl: './start-success.scss',
})
export class StartSuccess {}
