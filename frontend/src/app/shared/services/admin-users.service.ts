import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { UserGroup } from '../models/frontend-config.model';
import { ClientOrder } from '../models/order.model';

export interface AdminUser {
  id: string;
  email: string;
  group: UserGroup;
  required_password_reset: boolean;
  created_at: string | null;
  updated_at: string | null;
}

export interface UserMutationResponse {
  user: AdminUser;
  emailSentTo?: string;
}

export interface ClientDeletionLookup {
  client: Pick<AdminUser, 'id' | 'email'>;
  year: number;
  order: ClientOrder;
  canDelete: boolean;
}

@Injectable({ providedIn: 'root' })
export class AdminUsersService {
  private readonly http = inject(HttpClient);
  private readonly baseUrl = `${environment.apiUrl}/admin/users`;

  list(): Observable<AdminUser[]> {
    return this.http.get<AdminUser[]>(this.baseUrl, { withCredentials: true });
  }

  findClientOrder(email: string): Observable<ClientDeletionLookup> {
    return this.http.get<ClientDeletionLookup>(`${environment.apiUrl}/admin/client-deletion`, {
      params: { email },
      withCredentials: true,
    });
  }

  deleteClientOrder(userId: string): Observable<{ deleted: boolean; userId: string }> {
    return this.http.delete<{ deleted: boolean; userId: string }>(`${environment.apiUrl}/admin/client-deletion/${userId}`, {
      withCredentials: true,
    });
  }

  get(userId: string): Observable<{ user: AdminUser }> {
    return this.http.get<{ user: AdminUser }>(`${this.baseUrl}/${userId}`, { withCredentials: true });
  }

  create(email: string, group: UserGroup): Observable<UserMutationResponse> {
    return this.http.post<UserMutationResponse>(this.baseUrl, { email, group }, { withCredentials: true });
  }

  update(
    userId: string,
    changes: Partial<Pick<AdminUser, 'email' | 'group' | 'required_password_reset'>>,
  ): Observable<UserMutationResponse> {
    return this.http.patch<UserMutationResponse>(`${this.baseUrl}/${userId}`, changes, { withCredentials: true });
  }

  delete(userId: string): Observable<{ deleted: boolean; userId: string }> {
    return this.http.delete<{ deleted: boolean; userId: string }>(`${this.baseUrl}/${userId}`, { withCredentials: true });
  }

  sendPasswordReset(userId: string): Observable<{ emailSentTo: string }> {
    return this.http.post<{ emailSentTo: string }>(`${this.baseUrl}/${userId}/password-reset`, {}, { withCredentials: true });
  }
}
