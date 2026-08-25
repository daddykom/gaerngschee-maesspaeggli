import { TestBed } from '@angular/core/testing';
import { provideMockActions } from '@ngrx/effects/testing';
import { Action } from '@ngrx/store';
import { firstValueFrom, of, Subject, throwError } from 'rxjs';
import { AnmeldungService } from '../../shared/services/anmeldung.service';
import { StartActions } from './start.actions';
import { showStartSuccessNotificationEffect, submitStartEffect } from './start.effects';

describe('submitStartEffect', () => {
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
    const effect$ = TestBed.runInInjectionContext(() => submitStartEffect());
    const result = firstValueFrom(effect$);

    actions$.next(StartActions.submit({ email: 'person@example.com', language: 'de' }));

    await expect(result).resolves.toEqual(StartActions.submitSuccess({ sent: true }));
    expect(anmeldungService.requestInformation).toHaveBeenCalledWith('person@example.com', 'de');
  });

  it('maps a service error to a failure action', async () => {
    anmeldungService.requestInformation.mockReturnValue(throwError(() => new Error('SMTP failed')));
    const effect$ = TestBed.runInInjectionContext(() => submitStartEffect());
    const result = firstValueFrom(effect$);

    actions$.next(StartActions.submit({ email: 'person@example.com', language: 'de' }));

    await expect(result).resolves.toEqual(StartActions.submitFailure());
  });

  it('shows a success notification after the email was sent', async () => {
    const effect$ = TestBed.runInInjectionContext(() => showStartSuccessNotificationEffect());
    const result = firstValueFrom(effect$);

    actions$.next(StartActions.submitSuccess({ sent: true }));

    await expect(result).resolves.toEqual({
      type: '[Notification] Show',
      variant: 'success',
      titleKey: 'app.anmeldung.emailSentTitle',
      messageKey: 'app.anmeldung.emailSentMessage',
      preserveOnRoutes: ['/start'],
    });
  });
});
