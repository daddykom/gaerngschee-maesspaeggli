import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { DeliveryOrderResponse } from '../models/delivery.model';

@Injectable({ providedIn: 'root' })
export class DeliveryService {
  private readonly http = inject(HttpClient);

  getOrder(search: { email?: string; token?: string }): Observable<DeliveryOrderResponse> {
    let params = new HttpParams();
    if (search.email) params = params.set('email', search.email);
    if (search.token) params = params.set('token', search.token);
    return this.http.get<DeliveryOrderResponse>(`${environment.apiUrl}/delivery/order`, { params });
  }

  deliver(orderId: string): Observable<{ status: string }> {
    return this.http.post<{ status: string }>(`${environment.apiUrl}/delivery/orders/${orderId}/deliver`, {});
  }

  undo(orderId: string): Observable<{ status: string }> {
    return this.http.post<{ status: string }>(`${environment.apiUrl}/delivery/orders/${orderId}/undo`, {});
  }
}
