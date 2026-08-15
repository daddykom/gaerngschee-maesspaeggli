import { Component, inject, ChangeDetectionStrategy } from '@angular/core';
import { Store } from '@ngrx/store';
import { OfferListViewComponent } from './offer-list.view.component';
import { OffersActions } from '../../../store/offers/offers.actions';
import { selectOffers, selectOffersLoading } from '../../../store/offers/offers.feature';
import { Offer } from '../../../shared/models/offer.model';

@Component({
  standalone: true,
  imports: [OfferListViewComponent],
  templateUrl: './offer-list.container.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class OfferListContainerComponent {
  protected store = inject(Store);

  offers = this.store.selectSignal(selectOffers);
  loading = this.store.selectSignal(selectOffersLoading);

  constructor() {
    this.store.dispatch(OffersActions.loadOffers());
  }

  onCardClick(offer: unknown): void {
    console.log('Selected offer:', (offer as Offer).id);
  }
}
