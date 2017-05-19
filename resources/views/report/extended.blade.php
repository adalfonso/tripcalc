@extends('layout')

@section('content')
<div class="report detailedReport">
	<h4 class="centered form-header">Detailed Report</h4>
	<hr>

	<div class="graph">
		<table class="graph" style="width: 100%;">
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
				<td class="align-right">{{ $total }}</td>
			</tr>
		</table>

		@php
			$total = $transactions->where('isCreator', true)->sum('amount');
			$netTotal = $transactions->sum('net');
		@endphp

		<p>
			You have spent
			<strong>{{ money_format("%i", $total) }}</strong>
			and are accountable for
			<strong>{{ money_format("%i", $total - $netTotal) }}</strong>.
			You {{ $netTotal > 0 ? 'are owed' : 'owe' }}
			<strong>{{ $netTotal }}</strong>.
		</p>
	</div>
</div>
@endsection
