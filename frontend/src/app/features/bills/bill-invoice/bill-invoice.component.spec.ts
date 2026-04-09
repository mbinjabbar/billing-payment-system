import { ComponentFixture, TestBed } from '@angular/core/testing';

import { BillInvoiceComponent } from './bill-invoice.component';

describe('BillInvoiceComponent', () => {
  let component: BillInvoiceComponent;
  let fixture: ComponentFixture<BillInvoiceComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [BillInvoiceComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(BillInvoiceComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
