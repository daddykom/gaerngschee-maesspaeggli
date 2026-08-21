import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { AuthService } from './auth.service';

describe('AuthService', () => {
  let service: AuthService;
  let httpTesting: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });
    service = TestBed.inject(AuthService);
    httpTesting = TestBed.inject(HttpTestingController);
  });

  afterEach(() => httpTesting.verify());

  it('should be created', () => {
    expect(service).toBeTruthy();
  });

  it('posts login credentials to the backend', () => {
    const response = {
      user: { id: 'user-123', email: 'user@example.com', group: 'admin' as const },
      token: 'jwt-token',
      group: 'admin' as const,
    };
    let actualResponse = null;

    service.login('user@example.com', 'secret').subscribe((value) => {
      actualResponse = value;
    });

    const request = httpTesting.expectOne('http://localhost:8080/auth/login');
    expect(request.request.method).toBe('POST');
    expect(request.request.body).toEqual({ email: 'user@example.com', password: 'secret' });
    request.flush(response);

    expect(actualResponse).toEqual(response);
  });
});
