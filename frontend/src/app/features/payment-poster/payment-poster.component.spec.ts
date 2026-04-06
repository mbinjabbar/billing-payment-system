import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PaymentPosterComponent } from './payment-poster.component';

describe('PaymentPosterComponent', () => {
  let component: PaymentPosterComponent;
  let fixture: ComponentFixture<PaymentPosterComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [PaymentPosterComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PaymentPosterComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
