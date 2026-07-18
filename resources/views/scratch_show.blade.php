@extends('layouts.app')
@section('content')
<div><h1>Order #{{ $order->id }}</h1>
<div>Total Rp {{ number_format($order->total, 0, ',', '.') }}</div>
</div>@endsection
