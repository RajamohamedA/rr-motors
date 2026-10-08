@include('expenses.print', [
'expense' => $expense,
'paper' => $paper ?? 'a4',
'pdfMode' => true,
])