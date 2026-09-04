import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { ClientOrderResponse, OrderForm } from '../models/order.model';

@Injectable({ providedIn: 'root' })
export class OrderService {
  private readonly http = inject(HttpClient);

  getCurrent(): Observable<ClientOrderResponse> {
    return this.http.get<ClientOrderResponse>(`${environment.apiUrl}/client/order`);
  }

  saveCurrent(form: OrderForm): Observable<ClientOrderResponse> {
    return this.http.put<ClientOrderResponse>(`${environment.apiUrl}/client/order`, form);
  }
}
