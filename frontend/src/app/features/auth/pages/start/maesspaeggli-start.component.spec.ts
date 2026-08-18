import { provideTranslateService } from '@ngx-translate/core';
import { Store } from '@ngrx/store';
import { signal } from '@angular/core';
import { TestBed } from '@angular/core/testing';
import { MaesspaeggliStartComponent } from './maesspaeggli-start.component';
import { AnmeldungActions } from '../../../../store/anmeldung/anmeldung.actions';

describe('MaesspaeggliStartComponent', () => {
  const store = {
    dispatch: jest.fn(),
    selectSignal: jest.fn().mockReturnValue(signal(false)),
  };

  beforeEach(async () => {
    jest.clearAllMocks();
    store.selectSignal.mockReturnValue(signal(false));

    await TestBed.configureTestingModule({
      imports: [MaesspaeggliStartComponent],
      providers: [{ provide: Store, useValue: store }, provideTranslateService()],
    }).compileComponents();
  });

  it('does not dispatch for an invalid email', () => {
    const fixture = TestBed.createComponent(MaesspaeggliStartComponent);
    const component = fixture.componentInstance;
    component.form.setValue({ email: 'invalid-email' });

    component.submit();

    expect(store.dispatch).not.toHaveBeenCalled();
    expect(component.form.controls.email.touched).toBe(true);
  });

  it('dispatches the email in the submit action', () => {
    const fixture = TestBed.createComponent(MaesspaeggliStartComponent);
    const component = fixture.componentInstance;
    component.form.setValue({ email: 'person@example.com' });

    component.submit();

    expect(store.dispatch).toHaveBeenCalledWith(
      AnmeldungActions.submit({ email: 'person@example.com' }),
    );
  });

  it('shows the error info when sending fails', () => {
    store.selectSignal.mockReturnValue(signal(true));
    const fixture = TestBed.createComponent(MaesspaeggliStartComponent);

    fixture.detectChanges();

    expect(fixture.nativeElement.querySelector('[role="alert"]')).toBeTruthy();
  });
});
