import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';

export interface FairgateTestResult {
  email: string;
  fairgate: Record<string, unknown>;
}

@Injectable({ providedIn: 'root' })
export class FairgateTestService {
  private readonly http = inject(HttpClient);
  private readonly url = `${environment.apiUrl}/admin/fairgate/test`;

  test(): Observable<FairgateTestResult> {
    return this.http.get<FairgateTestResult>(this.url);
  }
}
