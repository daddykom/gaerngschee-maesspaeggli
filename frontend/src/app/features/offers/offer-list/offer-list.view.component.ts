import { Component, ChangeDetectionStrategy, input, output } from '@angular/core';
import { MatCardModule } from '@angular/material/card';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { Offer } from '../../../shared/models/offer.model';

@Component({
  selector: 'app-offer-list-view',
  standalone: true,
  imports: [MatCardModule, MatProgressSpinnerModule],
  templateUrl: './offer-list.view.component.html',
  styleUrl: './offer-list.view.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class OfferListViewComponent {
  offers = input<Offer[]>([]);
  loading = input<boolean>(false);
  cardClick = output<Offer>();
}
