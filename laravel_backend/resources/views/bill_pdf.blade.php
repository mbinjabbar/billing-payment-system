<h2 style="text-align:center;">Medical Bill</h2>

<hr>

<p><strong>Bill #:</strong> {{ $bill->bill_number }}</p>
<p><strong>Date:</strong> {{ $bill->bill_date }}</p>

<hr>

<h4>Charges</h4>
<ul>
    <li>Charges: {{ $bill->charges }}</li>
    <li>Insurance: {{ $bill->insurance_coverage }}</li>
    <li>Discount: {{ $bill->discount_amount }}</li>
    <li>Tax: {{ $bill->tax_amount }}</li>
</ul>

<hr>

<h3>Total: {{ $bill->bill_amount }}</h3>