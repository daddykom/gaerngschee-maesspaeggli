import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';

export interface AnmeldungResponse {
  sent: boolean;
}

@Injectable({ providedIn: 'root' })
export class AnmeldungService {
  private readonly http = inject(HttpClient);

  requestInformation(email: string, language: string): Observable<AnmeldungResponse> {
    return this.http.post<AnmeldungResponse>(`${environment.apiUrl}/public/start`, {
      email,
      language,
    });
  }
}
