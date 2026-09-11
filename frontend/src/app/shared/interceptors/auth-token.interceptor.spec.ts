import { HttpRequest, HttpResponse } from '@angular/common/http';
import { of } from 'rxjs';
import { authTokenInterceptor } from './auth-token.interceptor';

describe('csrf interceptor', () => {
  const response = new HttpResponse({ status: 200 });

  it('passes a request through without a csrf cookie', () => {
    const request = new HttpRequest('GET', '/protected');
    const next = vi.fn().mockReturnValue(of(response));

    authTokenInterceptor(request, next);

    expect(next).toHaveBeenCalledWith(request);
  });

  it('adds the csrf header when a cookie is available', () => {
    document.cookie = 'XSRF-TOKEN=test-token';
    const request = new HttpRequest('POST', '/protected', null);
    const next = vi.fn().mockReturnValue(of(response));

    authTokenInterceptor(request, next);

    expect(next.mock.calls[0][0].headers.get('X-CSRF-Token')).toBe('test-token');
    document.cookie = 'XSRF-TOKEN=; Max-Age=0';
  });
});
