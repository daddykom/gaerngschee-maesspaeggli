import { HttpRequest, HttpResponse } from '@angular/common/http';
import { TestBed } from '@angular/core/testing';
import { provideMockStore, MockStore } from '@ngrx/store/testing';
import { of } from 'rxjs';
import { authTokenInterceptor } from './auth-token.interceptor';
import { selectAuthToken } from '../../store/auth/auth.feature';

describe('authTokenInterceptor', () => {
  let store: MockStore;
  const response = new HttpResponse({ status: 200 });

  beforeEach(() => {
    TestBed.configureTestingModule({ providers: [provideMockStore()] });
    store = TestBed.inject(MockStore);
  });

  it('adds the bearer token when one is available', () => {
    store.overrideSelector(selectAuthToken, 'jwt-token');
    store.refreshState();
    const request = new HttpRequest('GET', '/protected');
    const next = jest.fn().mockReturnValue(of(response));

    TestBed.runInInjectionContext(() => authTokenInterceptor(request, next));

    expect(next).toHaveBeenCalledWith(expect.objectContaining({
      headers: expect.objectContaining({ get: expect.any(Function) }),
    }));
    expect(next.mock.calls[0][0].headers.get('Authorization')).toBe('Bearer jwt-token');
  });

  it('passes the original request through without a token', () => {
    store.overrideSelector(selectAuthToken, null);
    store.refreshState();
    const request = new HttpRequest('GET', '/public');
    const next = jest.fn().mockReturnValue(of(response));

    TestBed.runInInjectionContext(() => authTokenInterceptor(request, next));

    expect(next).toHaveBeenCalledWith(request);
  });
});
