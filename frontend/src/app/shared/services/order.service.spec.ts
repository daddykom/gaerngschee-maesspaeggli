import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { OrderService } from './order.service';

describe('OrderService', () => {
  let service: OrderService;
  let httpTesting: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({ providers: [provideHttpClient(), provideHttpClientTesting()] });
    service = TestBed.inject(OrderService);
    httpTesting = TestBed.inject(HttpTestingController);
  });

  afterEach(() => httpTesting.verify());

  it('loads the current client order', () => {
    const response = { order: null };
    service.getCurrent().subscribe((value) => expect(value).toEqual(response));

    const request = httpTesting.expectOne('http://localhost:8080/client/order');
    expect(request.request.method).toBe('GET');
    request.flush(response);
  });
});
