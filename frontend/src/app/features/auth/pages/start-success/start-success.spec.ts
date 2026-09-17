import { ComponentFixture, TestBed } from '@angular/core/testing';
import { RouterTestingModule } from '@angular/router/testing';
import { provideTranslateService } from '@ngx-translate/core';
import { StartSuccess } from './start-success';

describe('StartSuccess', () => {
  let component: StartSuccess;
  let fixture: ComponentFixture<StartSuccess>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [RouterTestingModule, StartSuccess],
      providers: [provideTranslateService()],
    }).compileComponents();

    fixture = TestBed.createComponent(StartSuccess);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
