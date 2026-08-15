import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { OfferJson } from '../../store/offers/offers.state';

@Injectable({ providedIn: 'root' })
export class OffersService {
  private http = inject(HttpClient);

  getOffers(): Observable<OfferJson[]> {
    return this.http.get<OfferJson[]>('http://localhost:8080/api/offers');
  }
}
