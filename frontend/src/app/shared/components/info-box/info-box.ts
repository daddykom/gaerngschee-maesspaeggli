import { Component, Input } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';

export type InfoBoxVariant = 'info' | 'warning' | 'error' | 'success';

@Component({
  selector: 'app-info-box',
  imports: [
    MatIconModule,
  ],
  templateUrl: './info-box.html',
  styleUrl: './info-box.scss',
})
export class InfoBoxComponent {
  @Input() variant: InfoBoxVariant = 'info';
  @Input() icon?: string;
  @Input() title?: string;

  get defaultIcon(): string {
    switch (this.variant) {
      case 'info': return 'info';
      case 'warning': return 'warning';
      case 'error': return 'error';
      case 'success': return 'check_circle';
    }
  }

  get iconToShow(): string {
    return this.icon ?? this.defaultIcon;
  }
}
