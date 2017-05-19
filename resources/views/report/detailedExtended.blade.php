@extends('layout')

@section('content')
<div>
	<h4 class="centered form-header">Detailed Report</h4>
	<hr>

	<div class="graph">
		<table>
			<tr>
				<th>Date</th>
				<th v-if="multiUser">Paid By</th>
				<th class="align-right">Amount</th>
				<th v-if="multiUser">Net</th>
			</tr>
			<tr v-for="transaction in transactions">
				<td>{{ transaction.date }}</td>
				<td v-if="multiUser">{{ transaction.creator }}</td>
				<td class="align-right">${{ transaction.amount }}</td>
				<td v-if="multiUser">{{ currency(transaction.net) }}</td>
			</tr>
			<tr class="total" v-if="multiUser">
				<td><strong>{{ balanceVerbiage }}</strong></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>${{ fixedLength(Math.abs(balance)) }}</td>
			</tr>
			<tr class="total">
				<td><strong>Personal total</strong></td>
				<td v-if="multiUser">&nbsp;</td>
				<td v-if="multiUser">&nbsp;</td>
				<td class="align-right">${{ total }}</td>
			</tr>
		</table>
	</div>
</div>
@endsection
