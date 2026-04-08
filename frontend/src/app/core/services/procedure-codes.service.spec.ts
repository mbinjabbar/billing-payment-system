import { TestBed } from '@angular/core/testing';

import { ProcedureCodesService } from './procedure-codes.service';

describe('ProcedureCodesService', () => {
  let service: ProcedureCodesService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(ProcedureCodesService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
