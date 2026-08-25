import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Observable } from 'rxjs';

export interface FairgateTestResult {
  email: string;
  fairgate: Record<string, unknown>;
}

@Injectable({ providedIn: 'root' })
export class FairgateTestService {
  private readonly http = inject(HttpClient);
  private readonly url = 'http://localhost:8080/admin/fairgate/test';

  test(): Observable<FairgateTestResult> {
    return this.http.get<FairgateTestResult>(this.url);
  }
}
