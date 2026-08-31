import { signal } from '@angular/core';
import { TestBed } from '@angular/core/testing';
import { Store } from '@ngrx/store';
import { provideTranslateService } from '@ngx-translate/core';
import { StartActions } from '../../../../store/start/start.actions';
import { MaesspaeggliStartComponent } from './maesspaeggli-start.component';

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
    component.model.set({ email: 'invalid-email' });

    component.submit();

    expect(store.dispatch).not.toHaveBeenCalled();
    expect(component.form.email().touched()).toBe(true);
  });

  it('dispatches the email in the submit action', () => {
    const fixture = TestBed.createComponent(MaesspaeggliStartComponent);
    const component = fixture.componentInstance;
    component.model.set({ email: 'person@example.com' });

    component.submit();

    expect(store.dispatch).toHaveBeenCalledWith(
      StartActions.submit({ email: 'person@example.com', language: 'de' }),
    );
  });

  it('shows the error info when sending fails', () => {
    store.selectSignal.mockReturnValue(signal(true));
    const fixture = TestBed.createComponent(MaesspaeggliStartComponent);

    fixture.detectChanges();

    expect(fixture.nativeElement.querySelector('[role="alert"]')).toBeTruthy();
  });
});
