import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
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

export interface RegistrationLoginResponse extends LoginResponse {
  fairgateUserExists: boolean;
  childrenCount: number;
  adultsCount: number;
  salutation: string;
}

export interface SessionStatusResponse {
  expiresAt: string;
  secondsRemaining: number;
}

export interface SessionRefreshResponse extends SessionStatusResponse {
  token: string;
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
    return this.http.post<LoginResponse>(`${environment.apiUrl}/auth/login`, { email, password }, { withCredentials: true });
  }

  registrationLogin(token: string): Observable<RegistrationLoginResponse> {
    return this.http.post<RegistrationLoginResponse>(
      `${environment.apiUrl}/auth/registration-login`,
      { token },
      { withCredentials: true },
    );
  }

  logout(): Observable<void> {
    return this.http.post<void>(`${environment.apiUrl}/auth/logout`, {}, { withCredentials: true });
  }

  sessionStatus(): Observable<SessionStatusResponse> {
    return this.http.get<SessionStatusResponse>(`${environment.apiUrl}/auth/session-status`, { withCredentials: true });
  }

  refreshSession(): Observable<SessionRefreshResponse> {
    return this.http.post<SessionRefreshResponse>(`${environment.apiUrl}/auth/session-refresh`, {}, { withCredentials: true });
  }

  changePassword(password: string): Observable<{ user: AuthUser }> {
    return this.http.post<{ user: AuthUser }>(
      `${environment.apiUrl}/auth/password-change-authenticated`,
      { password },
      { withCredentials: true },
    );
  }
}
