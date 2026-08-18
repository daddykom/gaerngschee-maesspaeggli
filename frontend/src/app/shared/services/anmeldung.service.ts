import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Observable } from 'rxjs';

export interface AnmeldungResponse {
  sent: boolean;
}

@Injectable({ providedIn: 'root' })
export class AnmeldungService {
  private readonly http = inject(HttpClient);

  requestInformation(email: string, language: string): Observable<AnmeldungResponse> {
    return this.http.post<AnmeldungResponse>('http://localhost:8080/public/anmeldung', { email, language });
  }
}
