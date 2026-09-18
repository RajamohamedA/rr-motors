@include('customer_ledgers.print', [
'company' => $company,
'store' => $store,
'customer' => $customer,
'entries' => $entries,
'filters' => $filters,
'summary' => $summary,
'pdfMode' => true,
])