import { TestBed } from '@angular/core/testing';
import { MockStore, provideMockStore } from '@ngrx/store/testing';
import { firstValueFrom, Observable } from 'rxjs';
import { NavigationProgressService } from '../../../../shared/services/navigation-progress.service';
import { AdminOverviewActions } from '../../../../store/admin-overview/admin-overview.actions';
import { initialState } from '../../../../store/admin-overview/admin-overview.state';
import { adminOverviewResolver } from './admin-overview.resolver';

describe('adminOverviewResolver', () => {
  let store: MockStore;
  let progress: { start: ReturnType<typeof vi.fn>; stop: ReturnType<typeof vi.fn> };

  beforeEach(() => {
    progress = { start: vi.fn(), stop: vi.fn() };
    TestBed.configureTestingModule({
      providers: [provideMockStore({ initialState: { adminOverview: initialState } }), { provide: NavigationProgressService, useValue: progress }],
    });
    store = TestBed.inject(MockStore);
  });

  it('loads and resolves when the overview reaches an error state', async () => {
    const dispatch = vi.spyOn(store, 'dispatch');
    const result = TestBed.runInInjectionContext(() => adminOverviewResolver({} as never, {} as never)) as Observable<boolean>;
    const resolved = firstValueFrom(result);

    expect(progress.start).toHaveBeenCalledOnce();
    expect(dispatch).toHaveBeenCalledWith(AdminOverviewActions.load());
    store.setState({ adminOverview: { status: 'error', errorCode: 'REQUEST_FAILED' } });

    await expect(resolved).resolves.toBe(true);
    expect(progress.stop).toHaveBeenCalledOnce();
  });

  it('does not reload an already failed overview', async () => {
    store.setState({ adminOverview: { status: 'error', errorCode: 'REQUEST_FAILED' } });
    const dispatch = vi.spyOn(store, 'dispatch');

    const result = TestBed.runInInjectionContext(() => adminOverviewResolver({} as never, {} as never)) as Observable<boolean>;

    await expect(firstValueFrom(result)).resolves.toBe(true);
    expect(dispatch).not.toHaveBeenCalled();
    expect(progress.start).not.toHaveBeenCalled();
  });
});
