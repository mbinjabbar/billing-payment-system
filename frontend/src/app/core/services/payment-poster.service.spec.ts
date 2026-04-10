import { TestBed } from '@angular/core/testing';

import { PaymentPosterService } from './payment-poster.service';

describe('PaymentPosterService', () => {
  let service: PaymentPosterService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(PaymentPosterService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
