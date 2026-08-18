import { TestBed } from '@angular/core/testing';
import { provideMockActions } from '@ngrx/effects/testing';
import { Action } from '@ngrx/store';
import { firstValueFrom, of, Subject, throwError } from 'rxjs';
import { AnmeldungService } from '../../shared/services/anmeldung.service';
import { AnmeldungActions } from './anmeldung.actions';
import { submitAnmeldungEffect } from './anmeldung.effects';

describe('submitAnmeldungEffect', () => {
  let actions$: Subject<Action>;
  let anmeldungService: { requestInformation: jest.Mock };

  beforeEach(() => {
    actions$ = new Subject<Action>();
    anmeldungService = { requestInformation: jest.fn() };

    TestBed.configureTestingModule({
      providers: [
        provideMockActions(() => actions$),
        { provide: AnmeldungService, useValue: anmeldungService },
      ],
    });
  });

  it('maps a successful service response to a success action', async () => {
    anmeldungService.requestInformation.mockReturnValue(of({ sent: true }));
    const effect$ = TestBed.runInInjectionContext(() => submitAnmeldungEffect());
    const result = firstValueFrom(effect$);

    actions$.next(AnmeldungActions.submit({ email: 'person@example.com', language: 'de' }));

    await expect(result).resolves.toEqual(AnmeldungActions.submitSuccess({ sent: true }));
    expect(anmeldungService.requestInformation).toHaveBeenCalledWith('person@example.com', 'de');
  });

  it('maps a service error to a failure action', async () => {
    anmeldungService.requestInformation.mockReturnValue(throwError(() => new Error('SMTP failed')));
    const effect$ = TestBed.runInInjectionContext(() => submitAnmeldungEffect());
    const result = firstValueFrom(effect$);

    actions$.next(AnmeldungActions.submit({ email: 'person@example.com', language: 'de' }));

    await expect(result).resolves.toEqual(AnmeldungActions.submitFailure());
  });
});
