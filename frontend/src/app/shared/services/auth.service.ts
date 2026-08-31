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
    return this.http.post<LoginResponse>(`${environment.apiUrl}/auth/login`, { email, password });
  }

  registrationLogin(token: string): Observable<RegistrationLoginResponse> {
    return this.http.post<RegistrationLoginResponse>(
      `${environment.apiUrl}/auth/registration-login`,
      { token },
    );
  }

  logout(): Observable<void> {
    return this.http.post<void>(`${environment.apiUrl}/auth/logout`, {});
  }

  changePassword(password: string): Observable<{ user: AuthUser }> {
    return this.http.post<{ user: AuthUser }>(
      `${environment.apiUrl}/auth/password-change-authenticated`,
      { password },
    );
  }
}
