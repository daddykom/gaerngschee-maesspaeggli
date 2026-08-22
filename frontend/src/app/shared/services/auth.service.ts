import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { UserGroup } from '../models/frontend-config.model';

export interface AuthUser {
  id: string;
  email: string;
  group: UserGroup;
}

export interface LoginResponse {
  user: AuthUser;
  token: string;
  group: UserGroup;
  requiredPasswordReset: boolean;
}

export interface ApiErrorResponse {
  error: {
    code: string;
    details: Record<string, unknown>;
  };
}

@Injectable({
  providedIn: 'root',
})
export class AuthService {
  private readonly http = inject(HttpClient);

  login(email: string, password: string): Observable<LoginResponse> {
    return this.http.post<LoginResponse>('http://localhost:8080/auth/login', { email, password });
  }

  logout(): Observable<void> {
    return this.http.post<void>('http://localhost:8080/auth/logout', {});
  }

  changePassword(password: string): Observable<{ user: AuthUser }> {
    return this.http.post<{ user: AuthUser }>(
      'http://localhost:8080/auth/password-change-authenticated',
      { password },
    );
  }
}
