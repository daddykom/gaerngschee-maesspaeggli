import { TestBed } from '@angular/core/testing';
import { provideMockStore, MockStore } from '@ngrx/store/testing';
import { firstValueFrom, Observable } from 'rxjs';
import { NavigationProgressService } from '../../shared/services/navigation-progress.service';
import { OrderActions } from '../../store/order/order.actions';
import { initialState } from '../../store/order/order.state';
import { orderResolver } from './order.resolver';

describe('orderResolver', () => {
  let store: MockStore;
  let progress: { start: jest.Mock; stop: jest.Mock };

  beforeEach(() => {
    progress = { start: jest.fn(), stop: jest.fn() };
    TestBed.configureTestingModule({
      providers: [
        provideMockStore({ initialState: { order: initialState } }),
        { provide: NavigationProgressService, useValue: progress },
      ],
    });
    store = TestBed.inject(MockStore);
  });

  it('loads and waits for a terminal state while showing progress', async () => {
    const dispatch = jest.spyOn(store, 'dispatch');
    const result = TestBed.runInInjectionContext(() => orderResolver({} as never, {} as never)) as Observable<boolean>;
    const resolved = firstValueFrom(result);

    expect(progress.start).toHaveBeenCalledTimes(1);
    expect(dispatch).toHaveBeenCalledWith(OrderActions.orderLoadRequested());

    store.setState({ order: { status: 'loaded', order: null, form: {
      adultsCount: 0,
      childrenCount: 0,
      adults: [],
      children: [],
    } } });

    await expect(resolved).resolves.toBe(true);
    expect(progress.stop).toHaveBeenCalledTimes(1);
  });

  it('does not start progress for an already loaded state', async () => {
    store.setState({ order: { status: 'loaded', order: null, form: {
      adultsCount: 0,
      childrenCount: 0,
      adults: [],
      children: [],
    } } });

    const result = TestBed.runInInjectionContext(() => orderResolver({} as never, {} as never)) as Observable<boolean>;

    await expect(firstValueFrom(result)).resolves.toBe(true);
    expect(progress.start).not.toHaveBeenCalled();
    expect(progress.stop).not.toHaveBeenCalled();
  });
});
