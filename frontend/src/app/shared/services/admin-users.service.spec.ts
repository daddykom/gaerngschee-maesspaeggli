import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { AdminUsersService } from './admin-users.service';

describe('AdminUsersService', () => {
  let service: AdminUsersService;
  let httpTesting: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });
    service = TestBed.inject(AdminUsersService);
    httpTesting = TestBed.inject(HttpTestingController);
  });

  afterEach(() => httpTesting.verify());

  it('lists users', () => {
    const response = [{ id: 'user-1', email: 'user@example.com', group: 'user', required_password_reset: false, created_at: null, updated_at: null }];
    service.list().subscribe((value) => expect(value).toEqual(response));

    const request = httpTesting.expectOne('http://localhost:8080/admin/users');
    expect(request.request.method).toBe('GET');
    request.flush(response);
  });

  it('gets a user by id', () => {
    service.get('user-1').subscribe();

    const request = httpTesting.expectOne('http://localhost:8080/admin/users/user-1');
    expect(request.request.method).toBe('GET');
    request.flush({ user: {} });
  });

  it('creates a user', () => {
    service.create('user@example.com', 'user').subscribe();

    const request = httpTesting.expectOne('http://localhost:8080/admin/users');
    expect(request.request.method).toBe('POST');
    expect(request.request.body).toEqual({ email: 'user@example.com', group: 'user' });
    request.flush({ user: {} });
  });

  it('updates a user', () => {
    const changes = { email: 'new@example.com', group: 'admin' as const, required_password_reset: true };
    service.update('user-1', changes).subscribe();

    const request = httpTesting.expectOne('http://localhost:8080/admin/users/user-1');
    expect(request.request.method).toBe('PATCH');
    expect(request.request.body).toEqual(changes);
    request.flush({ user: {} });
  });

  it('deletes a user', () => {
    service.delete('user-1').subscribe();

    const request = httpTesting.expectOne('http://localhost:8080/admin/users/user-1');
    expect(request.request.method).toBe('DELETE');
    request.flush({ deleted: true, userId: 'user-1' });
  });
});
