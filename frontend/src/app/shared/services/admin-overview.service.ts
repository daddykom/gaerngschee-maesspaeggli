import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { AdminOverview } from '../models/admin-overview.model';

@Injectable({ providedIn: 'root' })
export class AdminOverviewService {
  private readonly http = inject(HttpClient);

  get(): Observable<AdminOverview> {
    return this.http.get<AdminOverview>(`${environment.apiUrl}/admin/overview`);
  }
}
