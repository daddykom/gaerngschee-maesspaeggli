import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { provideTranslateService } from '@ngx-translate/core';
import { NotFoundComponent } from './not-found.component';

describe('NotFoundComponent', () => {
  let fixture: ComponentFixture<NotFoundComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [NotFoundComponent],
      providers: [provideRouter([]), provideTranslateService()],
    }).compileComponents();

    fixture = TestBed.createComponent(NotFoundComponent);
    fixture.detectChanges();
  });

  it('renders the not-found heading and both recovery actions', () => {
    expect(fixture.nativeElement.querySelector('h2').textContent).toContain('app.notFound.heading');
    expect(fixture.nativeElement.querySelectorAll('a')).toHaveLength(2);
  });

  it('links to login and start', () => {
    const links = fixture.nativeElement.querySelectorAll('a');

    expect(links[0].getAttribute('href')).toBe('/login');
    expect(links[1].getAttribute('href')).toBe('/start');
  });
});
