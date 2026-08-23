import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { FrontendConfig } from '../models/frontend-config.model';

@Injectable({ providedIn: 'root' })
export class FrontendConfigService {
  private readonly http = inject(HttpClient);
  private readonly baseUrl = 'http://localhost:8080/admin/configuration';

  list(): Observable<FrontendConfig[]> {
    return this.http.get<FrontendConfig[]>(this.baseUrl);
  }

  update(id: string, value: string | string[]): Observable<FrontendConfig> {
    return this.http.patch<FrontendConfig>(`${this.baseUrl}/${id}`, { value });
  }
}
