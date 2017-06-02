@extends('layout')

@section('content')
<div class="report detailedReport extendedReport">
	<h4 class="centered">Detailed Report</h4>
	<hr>
	<table>
		<tr>
			<th>Date</th>
			<th>Paid By</th>
			<th>Description</th>
			<th>Split Type</th>
			<th>Split</th>
			<th class="align-right">Amount</th>
			<th class="align-right">Net</th>
		</tr>
		@foreach ($transactions as $transaction)
			<tr>
				<td>{{ $transaction->date }}</td>
				<td>{{ $transaction->creator }}</td>
				<td>{{ $transaction->description or '-' }}</td>
				<td>{{ ucfirst($transaction->splitType) }}</td>
				<td>
					@if ($transaction->splitType === 'custom')
						{{ $transaction->userSplit }} / {{ $transaction->splitTotal }}
					@endif
				</td>
				<td class="align-right">{{ $transaction->amount }}</td>
				<td class="align-right">{{ $transaction->net == 0 ? '' : $transaction->net }}</td>
			</tr>
		@endforeach
		<tr class="total">
			<td><strong>{{ $total > 0 ? 'You are owed' : 'You Owe' }}</strong></td>
			<td colspan="5">&nbsp;</td>
			<td class="align-right">{{ money_format("%i", abs($netTotal)) }}</td>
		</tr>
	</table>
	<p>
		You have spent
		<strong>{{ money_format("%i", $total) }}</strong>
		and are accountable for
		<strong>{{ money_format("%i", $total - $netTotal) }}</strong>.
		You {{ $netTotal > 0 ? 'are owed' : 'owe' }}
		<strong>{{ money_format("%i", abs($netTotal)) }}</strong>.
	</p>
</div>
@endsection
