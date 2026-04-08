import { TestBed } from '@angular/core/testing';

import { InsuranceFirmsService } from './insurance-firms.service';

describe('InsuranceFirmsService', () => {
  let service: InsuranceFirmsService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(InsuranceFirmsService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
