import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { ClientOrderResponse, OrderDraft } from '../models/order.model';

@Injectable({ providedIn: 'root' })
export class OrderService {
  private readonly http = inject(HttpClient);

  getCurrent(): Observable<ClientOrderResponse> {
    return this.http.get<ClientOrderResponse>(`${environment.apiUrl}/client/order`);
  }

  saveCurrent(draft: OrderDraft): Observable<ClientOrderResponse> {
    return this.http.put<ClientOrderResponse>(`${environment.apiUrl}/client/order`, draft);
  }
}
