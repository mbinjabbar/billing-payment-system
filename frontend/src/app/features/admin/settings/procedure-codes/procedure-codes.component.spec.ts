import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ProcedureCodesComponent } from './procedure-codes.component';

describe('ProcedureCodesComponent', () => {
  let component: ProcedureCodesComponent;
  let fixture: ComponentFixture<ProcedureCodesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ProcedureCodesComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ProcedureCodesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
