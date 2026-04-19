import { ComponentFixture, TestBed } from '@angular/core/testing';

import { InsuranceFirmsComponent } from './insurance-firms.component';

describe('InsuranceFirmsComponent', () => {
  let component: InsuranceFirmsComponent;
  let fixture: ComponentFixture<InsuranceFirmsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [InsuranceFirmsComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(InsuranceFirmsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
